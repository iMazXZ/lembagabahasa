<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendaftaranEptResource\Pages;
use App\Models\PendaftaranEpt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\PendaftaranEptResource\Pages\CreatePendaftaranEpt;
use Filament\Forms\Components\TextInput;


class PendaftaranEptResource extends Resource
{
    protected static ?string $model = PendaftaranEpt::class;

    protected static ?string $navigationIcon = 'heroicon-s-document-plus';

    protected static ?string $navigationGroup = 'Layanan Lembaga Bahasa';

    public static ?string $slug = 'ept';

    public static ?string $label = 'Pendaftaran EPT';
    
    public static function form(Form $form): Form
    {
        $user = auth()->user();

        return $form
            ->schema([
                Forms\Components\Placeholder::make('name')
                    ->label('Keterangan Pemohon')
                    ->content(function () use ($user) {
                        $name = $user?->name ?? '-';
                        $prodi = $user?->prody?->name ?? '-';
                        return "{$name} - {$prodi}";
                    }),

                Forms\Components\Placeholder::make('srn')
                    ->label('Nomor Pokok Mahasiswa')
                    ->content($user?->srn),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                Forms\Components\FileUpload::make('bukti_pembayaran')
                    ->label('Upload Struk Pembayaran')
                    ->helperText('Upload bukti pembayaran EPT. Pastikan file dalam format gambar (JPG/PNG) dan ukuran tidak lebih dari 2MB.')
                    ->directory('bukti-ept')
                    ->downloadable()
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->required()
                    ->columnSpanFull()
                    ->default(null)
                    ->validationMessages([
                            'required' => 'Foto bukti pembayaran wajib diunggah.',
                        ])
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $set('status_pembayaran', 'pending');
                        }
                    }),

                Forms\Components\Hidden::make('status_pembayaran')
                    ->default('pending')
                    ->visible(fn () => !auth()->user()->hasRole(['Admin', 'Staf Administrasi'])),

                Forms\Components\Select::make('status_pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->hidden(fn () => !auth()->user()->hasRole(['Admin', 'Staf Administrasi'])),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Nama Pendaftar')
                    ->searchable()
                    ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi'])),
                Tables\Columns\BadgeColumn::make('pendaftaranGrupTes.masterGrupTes.group_number')
                    ->label('Grup')
                    ->color('success')
                    ->sortable()
                    ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi'])),
                Tables\Columns\TextColumn::make('bukti_pembayaran')
                    ->label('Bukti Pembayaran')
                    ->formatStateUsing(fn ($state) => 'Bukti Bayar')
                    ->url(fn ($record) => asset('storage/' . $record->bukti_pembayaran))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi'])),
                Tables\Columns\BadgeColumn::make('status_pembayaran')
                    ->label('Status')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui', 
                        'rejected' => 'Ditolak',
                        default => 'Tidak Diketahui',
                    })
                    ->tooltip(fn ($record) => match ($record->status_pembayaran) {
                        'pending' => 'Menunggu verifikasi pembayaran',
                        'approved' => 'Pembayaran telah diverifikasi', 
                        'rejected' => 'Pembayaran ditolak, silakan upload ulang',
                        default => 'Status tidak valid',
                    })
                    ->colors([
                        'warning' => 'pending',    // Format: 'color' => 'value'
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'secondary' => fn ($state) => empty($state), // untuk null/empty
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Pada')
                    ->dateTime(fn () => request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone|iPad|iPod/i', request()->header('User-Agent')) ? 'd/m' : 'd/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->label('Filter Status Pembayaran'),
                Tables\Filters\SelectFilter::make('grup_tes_id')
                    ->label('Filter Nomor Grup')
                    ->options(
                        \App\Models\MasterGrupTes::all()->pluck('group_number', 'id')->mapWithKeys(function ($groupNumber, $id) {
                            return [$id => 'Grup ' . $groupNumber];
                        })
                    )
                    ->query(function ($query, $state) {
                        // Hanya jalankan whereHas jika ada value yang dipilih
                        if (!empty($state['value'])) {
                            $query->whereHas('pendaftaranGrupTes', function ($q) use ($state) {
                                $q->where('grup_tes_id', $state['value']);
                            });
                        }
                        // Jika kosong, biarkan query tetap normal tanpa whereHas
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label(fn ($record) => $record->status_pembayaran === 'pending' ? 'Available Soon' : 'Jadwal')
                    ->tooltip(fn ($record) => $record->status_pembayaran === 'pending' ? 'Menunggu Verifikasi Pembayaran' : 'Lihat Jadwal & Nilai Tes')
                    ->url(fn ($record) => PendaftaranEptResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-calendar-days')
                    ->color(fn ($record) => $record->status_pembayaran === 'pending' ? 'gray' : 'success')
                    ->button()
                    ->disabled(fn ($record) => $record->status_pembayaran === 'pending')
                    ->visible(fn ($record) =>
                        ($record->status_pembayaran === 'approved' || $record->status_pembayaran === 'pending') &&
                        auth()->user()->hasRole('pendaftar')
                    ),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->button()
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) =>
                        auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi']) &&
                        in_array($record->status_pembayaran, ['pending', 'rejected'])
                    )
                    ->action(function ($record) {
                        $record->update(['status_pembayaran' => 'approved']);

                        Notification::make()
                            ->title("Pembayaran {$record->users->name} disetujui.")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->button()
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn ($record) =>
                        auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi']) &&
                        in_array($record->status_pembayaran, ['approved', 'pending'])
                    )
                    ->action(function ($record) {
                        $record->update(['status_pembayaran' => 'rejected']);

                        // Kirim notifikasi email
                        $record->users->notify(new \App\Notifications\EptRejectedNotification());

                        Notification::make()
                            ->title("Pembayaran {$record->users->name} ditolak.")
                            ->danger()
                            ->send();
                    }),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->icon('heroicon-m-pencil-square')
                    ->tooltip('Edit'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading('Belum ada data pendaftaran')
            ->emptyStateDescription('Silakan buat pendaftaran EPT pertama.')
            ->emptyStateHeading('Belum ada data pendaftaran')
            ->emptyStateDescription('Silakan buat pendaftaran EPT pertama.')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('assignToGroup')
                        ->label('Masukkan ke Grup Tes')
                        ->color('success')
                        ->icon('heroicon-s-book-open')
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                $sudahMasukGrupIni = \App\Models\PendaftaranGrupTes::where('pendaftaran_ept_id', $record->id)
                                    ->where('grup_tes_id', $data['grup_tes_id'])
                                    ->exists();

                                $jumlahGrup = \App\Models\PendaftaranGrupTes::where('pendaftaran_ept_id', $record->id)->count();

                                if ($sudahMasukGrupIni) {
                                    Notification::make()
                                        ->title("Peserta {$record->users->name} sudah masuk ke grup ini.")
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                if ($jumlahGrup >= 3) {
                                    Notification::make()
                                        ->title("Peserta {$record->users->name} sudah mencapai batas 3 grup.")
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                \App\Models\PendaftaranGrupTes::create([
                                    'pendaftaran_ept_id' => $record->id,
                                    'grup_tes_id' => $data['grup_tes_id'],
                                ]);
                            }

                            $jumlahPesertaSekarang = \App\Models\PendaftaranGrupTes::where('grup_tes_id', $data['grup_tes_id'])->count();
                            $namaGrup = \App\Models\MasterGrupTes::find($data['grup_tes_id']);
                            Notification::make()
                                ->title("Berhasil menambahkan peserta ke grup tes {$namaGrup->group_number}.")
                                ->body("Total peserta di grup ini sekarang: {$jumlahPesertaSekarang}.")
                                ->success()
                                ->send();
                        })
                        ->form([
                            Forms\Components\Select::make('grup_tes_id')
                                ->label('Pilih Grup Tes')
                                ->options(
                                    \App\Models\MasterGrupTes::all()->mapWithKeys(function ($item) {
                                        $jumlahPeserta = \App\Models\PendaftaranGrupTes::where('grup_tes_id', $item->id)->count();
                                        return [
                                            $item->id => 'Grup ' . $item->group_number . ' - ' . \Carbon\Carbon::parse($item->tanggal_tes)->translatedFormat('d M Y') . " ({$jumlahPeserta} Peserta)",
                                        ];
                                    })
                                )
                                ->required(),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi']))
                        ->before(function (Collection $records, array $data) {
                            $jumlahPesertaGrup = \App\Models\PendaftaranGrupTes::where('grup_tes_id', $data['grup_tes_id'])->count();
                            if ($jumlahPesertaGrup + $records->count() > 20) {
                                Notification::make()
                                    ->title('Peserta dalam grup ini sudah mencapai batas maksimal 20 orang.')
                                    ->danger()
                                    ->send();
                                throw new Halt();
                            }
                        }),
                    Tables\Actions\BulkAction::make('validasiPembayaran')
                        ->label('Status Pembayaran')
                        ->icon('heroicon-s-check-circle')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Set Status Pembayaran')
                                ->options([
                                    'pending' => 'Pending',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status_pembayaran' => $data['status'],
                                ]);
                            }

                            Notification::make()
                                ->title('Status pembayaran berhasil diperbarui.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi']))
                        ->deselectRecordsAfterCompletion(),
                ])
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Tanggal Pendaftaran')
                    ->date()
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PendaftaranEptResource\RelationManagers\PendaftaranGrupTesRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        if (!auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi'])) {
            return null;
        }
        $count = static::getModel()::where('status_pembayaran', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pemohon Perlu ditinjau';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendaftaranEpts::route('/'),
            'create' => Pages\CreatePendaftaranEpt::route('/create'),
            'view' => Pages\ViewPendaftaranPage::route('/{record}'),
            'edit' => Pages\EditPendaftaranEpt::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if (auth()->user()->hasRole(['Admin', 'Staf Administrasi'])) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
