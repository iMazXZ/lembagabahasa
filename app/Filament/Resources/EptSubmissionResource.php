<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptSubmissionResource\Pages;
use App\Models\EptSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use App\Notifications\EptSubmissionStatusNotification;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Support\Verification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class EptSubmissionResource extends Resource
{
    protected static ?string $model = EptSubmission::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Layanan Lembaga Bahasa';

    public static ?string $slug = 'suratrekomendasi';
    protected static ?string $navigationLabel = 'Pengajuan Surat Rekomendasi';
    protected static ?int $navigationSort = 3;

    public static function shouldRegisterNavigation(): bool
    {
        $u = auth()->user();
        return $u?->hasAnyRole(['Admin', 'Staf Administrasi', 'Kepala Lembaga']) ?? false;
    }

    public static function form(Form $form): Form
    {
        // Admin memverifikasi via tabel/view, jadi form kosong
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.srn')
                    ->label('NPM')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('user.prody.name')
                    ->label('Prodi')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime('d/m/Y H:i')
                    ->since()          // tampilkan “x jam lalu” saat hover
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('catatan_admin')
                    ->label('Catatan Staf')
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->visible(fn ($record) => filled($record?->catatan_admin)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('user.prody_id')
                    ->relationship('user.prody', 'name')
                    ->label('Prodi'),
                Tables\Filters\Filter::make('created_at')
                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Action::make('print')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(fn (EptSubmission $record) => $record->status === 'approved')
                    ->url(fn (EptSubmission $record) => route('ept-submissions.pdf', [$record, 'dl' => 1]))
                    ->openUrlInNewTab(),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (EptSubmission $record): bool =>
                        $record->status === 'pending' && auth()->user()?->hasAnyRole(['Admin','Staf Administrasi'])
                    )
                    ->form(function (EptSubmission $record) {
                        // Saran nomor surat default (sequence per tahun)
                        $year   = now()->year;
                        $count  = EptSubmission::whereYear('approved_at', $year)
                                    ->whereNotNull('surat_nomor')
                                    ->count();
                        $seq    = str_pad((string)($count + 1), 3, '0', STR_PAD_LEFT);
                        $suggest= "{$seq}/II.3.AU/F/KET/LB_UMM/{$year}";

                        return [
                            Forms\Components\TextInput::make('surat_nomor')
                                ->label('Nomor Surat')
                                ->default($record->surat_nomor ?: $suggest)
                                ->required()
                                ->maxLength(100)
                                ->rule('regex:/^\d{3}\/II\.3\.AU\/F\/KET\/LB_UMM\/\d{4}$/')
                                ->helperText('Format: 001/II.3.AU/F/KET/LB_UMM/2025')
                                ->rule(fn () => Rule::unique('ept_submissions', 'surat_nomor')
                                    ->ignore($record->id)
                                    ->where(fn ($q) => $q->whereNotNull('surat_nomor')))
                                ->validationMessages([
                                    'required' => 'Nomor surat wajib diisi.',
                                    'regex'    => 'Format nomor surat tidak sesuai',
                                    'unique'   => 'Nomor surat sudah digunakan.',
                                ]),
                            Forms\Components\Textarea::make('catatan_admin')
                                ->label('Catatan (Opsional)')
                                ->rows(4),
                        ];
                    })
                    ->action(function (EptSubmission $record, array $data) {
                        if ($record->status !== 'pending') {
                            Notification::make()->title('Status sudah tidak pending.')->danger()->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $data) {
                            $locked = EptSubmission::query()->lockForUpdate()->find($record->id);

                            // Generate kode verifikasi jika kosong (pakai App\Support\Verification)
                            $code = $locked->verification_code ?: Verification::generateCode();

                            $locked->update([
                                'status'            => 'approved',
                                'catatan_admin'     => $data['catatan_admin'] ?? $locked->catatan_admin,
                                'approved_at'       => now(),
                                'approved_by'       => auth()->id(),
                                'verification_code' => $code,
                                'verification_url'  => route('verification.show', ['code' => $code]),
                                'surat_nomor'       => $data['surat_nomor'],
                            ]);
                        });

                        // Kirim email setelah commit
                        $fresh   = $record->fresh();
                        $pemohon = $fresh->user; // relasi pemohon

                        if ($pemohon) {
                            $verificationUrl = $fresh->verification_url;
                            // Sertakan link unduh PDF jika memang route-nya ada & aksesnya sesuai
                            $pdfUrl = route('ept-submissions.pdf', $fresh);
                            $pemohon->notify(new EptSubmissionStatusNotification('approved', $verificationUrl, $pdfUrl));
                        }

                        Notification::make()->title('Pengajuan disetujui & email terkirim.')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (EptSubmission $record): bool =>
                        $record->status === 'pending' && auth()->user()?->hasAnyRole(['Admin','Staf Administrasi'])
                    )
                    ->form([
                        Forms\Components\Textarea::make('catatan_admin')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (EptSubmission $record, array $data) {
                        if ($record->status !== 'pending') {
                            Notification::make()->title('Status sudah tidak pending.')->danger()->send();
                            return;
                        }

                        DB::transaction(function () use ($record, $data) {
                            $record->update([
                                'status'        => 'rejected',
                                'catatan_admin' => $data['catatan_admin'],
                                'rejected_at'   => now(),
                                'rejected_by'   => auth()->id(),
                            ]);
                        });

                        // kirim email setelah commit
                        $fresh = $record->fresh('user'); // pastikan relasi diload
                        $pemohon = $fresh?->user;

                        if (! $pemohon) {
                            Log::warning('EPT rejected: user not found for record', ['id' => $record->id]);
                            Notification::make()->title('Ditolak, tapi user tidak ditemukan.')->danger()->send();
                            return;
                        }

                        try {
                            // rejected tidak perlu link
                            $pemohon->notify(new EptSubmissionStatusNotification('rejected', null, null));
                            Notification::make()->title('Pengajuan ditolak & email terkirim.')->success()->send();
                        } catch (\Throwable $e) {
                            Log::error('Gagal kirim email rejected', [
                                'e' => $e->getMessage(),
                                'record_id' => $record->id,
                                'user_id' => $pemohon->id,
                            ]);
                            Notification::make()->title('Ditolak, tapi gagal mengirim email (cek logs).')->danger()->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasAnyRole(['Admin']))
                ]),
            ])
            ->emptyStateHeading('Belum ada pengajuan')
            ->emptyStateDescription('Pengajuan yang dikirim oleh pendaftar akan muncul di sini.');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Components\Section::make('Informasi Pendaftar')
                ->schema([
                    Components\TextEntry::make('user.name')->label('Nama Pendaftar'),
                    Components\TextEntry::make('user.srn')->label('NPM'),
                    Components\TextEntry::make('user.prody.name')->label('Prodi'),
                    Components\TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->formatStateUsing(fn (string $state): string => match ($state) {
                            'pending' => 'Menunggu',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            default => ucfirst($state),
                        })
                        ->color(fn (string $state): string => match ($state) {
                            'pending' => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'gray',
                        }),
                    Components\TextEntry::make('catatan_admin')
                        ->label('Catatan dari Staf')
                        ->visible(fn ($record) => filled($record?->catatan_admin)),
                ])->columns(3),

            Components\Section::make('Data Tes 1')
                ->schema([
                    Components\TextEntry::make('nilai_tes_1')->label('Nilai'),
                    Components\TextEntry::make('tanggal_tes_1')->label('Tanggal')->date(),
                    Components\ImageEntry::make('foto_path_1')
                        ->label('Bukti Foto')
                        ->disk('public')
                        ->height(120)
                        ->square(false)
                        ->url(fn ($state) => \Storage::disk('public')->url($state)) // link ke file asli
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => filled($record?->foto_path_1)),
                ])->columns(3),

            Components\Section::make('Data Tes 2')
                ->schema([
                    Components\TextEntry::make('nilai_tes_2')->label('Nilai'),
                    Components\TextEntry::make('tanggal_tes_2')->label('Tanggal')->date(),
                    Components\ImageEntry::make('foto_path_2')
                        ->label('Bukti Foto')
                        ->disk('public')
                        ->height(120)
                        ->square(false)
                        ->url(fn ($state) => \Storage::disk('public')->url($state))
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => filled($record?->foto_path_2)),
                ])
                ->columns(3)
                ->visible(fn ($record) =>
                    filled($record?->nilai_tes_2) ||
                    filled($record?->foto_path_2) ||
                    filled($record?->tanggal_tes_2)
                ),

            Components\Section::make('Data Tes 3')
                ->schema([
                    Components\TextEntry::make('nilai_tes_3')->label('Nilai'),
                    Components\TextEntry::make('tanggal_tes_3')->label('Tanggal')->date(),
                    Components\ImageEntry::make('foto_path_3')
                        ->label('Bukti Foto')
                        ->disk('public')
                        ->height(120)
                        ->square(false)
                        ->url(fn ($state) => \Storage::disk('public')->url($state))
                        ->openUrlInNewTab()
                        ->visible(fn ($record) => filled($record?->foto_path_3)),
                ])
                ->columns(3)
                ->visible(fn ($record) =>
                    filled($record?->nilai_tes_3) ||
                    filled($record?->foto_path_3) ||
                    filled($record?->tanggal_tes_3)
                ),

        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptSubmissions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // admin tidak membuat data di sini
    }
}