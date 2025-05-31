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

class PendaftaranEptResource extends Resource
{
    protected static ?string $model = PendaftaranEpt::class;

    protected static ?string $navigationIcon = 'heroicon-s-document-plus';

    protected static ?string $navigationGroup = 'Layanan Lembaga Bahasa';

    public static ?string $label = 'Pendaftaran EPT / TOEFL';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::id()),
                Forms\Components\FileUpload::make('bukti_pembayaran')
                    ->label('Upload Struk Pembayaran')
                    ->image()
                    ->required()
                    ->default(null),
                Forms\Components\Select::make('status_pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Nama Pendaftar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bukti_pembayaran')
                    ->label('Bukti Pembayaran')
                    ->formatStateUsing(fn ($state) => 'Bukti Bayar')
                    ->url(fn ($record) => asset('storage/' . $record->bukti_pembayaran))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-photo')
                    ->color('info'),
                Tables\Columns\BadgeColumn::make('status_pembayaran')
                    ->label('Status')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui', 
                        'rejected' => 'Ditolak - Bukti Tidak Valid',
                        default => 'Tidak Diketahui',
                    })
                    ->colors([
                        'warning' => 'pending',    // Format: 'color' => 'value'
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'secondary' => fn ($state) => empty($state), // untuk null/empty
                    ]),
                ...(
                    auth()->user()->hasRole('pendaftar')
                        ? [
                            Tables\Columns\TextColumn::make('pendaftaranGrupTes.masterGrupTes.group_number')->label('Grup'),
                            Tables\Columns\TextColumn::make('pendaftaranGrupTes.masterGrupTes.tanggal_tes')->label('Jadwal Tes')->dateTime('d M Y H:i'),
                            Tables\Columns\BadgeColumn::make('listening_comprehension')->label('Listening'),
                            Tables\Columns\BadgeColumn::make('structure_written_expr')->label('Structure'),
                            Tables\Columns\BadgeColumn::make('reading_comprehension')->label('Reading'),
                            Tables\Columns\BadgeColumn::make('total_score')->label('Total Skor'),
                            Tables\Columns\BadgeColumn::make('rank')
                                ->label('Status')
                                ->color(fn ($state) => match ($state) {
                                    'Fail' => 'danger',
                                    'Pass' => 'success',
                                    default => null,
                                }),
                        ]
                        : []
                ),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Daftar Pada')
                    ->dateTime()
                    ->sortable()
                    ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi'])),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_pembayaran')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->label('Filter Status Pembayaran'),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn ($record) =>
                        auth()->user()->hasRole('Admin') &&
                        in_array($record->status_pembayaran, ['pending', 'rejected'])
                    )
                    ->action(function ($record) {
                        $record->update(['status_pembayaran' => 'approved']);

                        Notification::make()
                            ->title('Pembayaran disetujui.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn ($record) =>
                        auth()->user()->hasRole('Admin') &&
                        in_array($record->status_pembayaran, ['approved', 'pending'])
                    )
                    ->action(function ($record) {
                        $record->update(['status_pembayaran' => 'rejected']);

                        Notification::make()
                            ->title('Pembayaran ditolak.')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('assignToGroup')
                        ->label('Masukkan ke Grup Tes')
                        ->icon('heroicon-o-user-group')
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

                            Notification::make()
                                ->title('Berhasil menambahkan peserta ke grup tes.')
                                ->success()
                                ->send();
                        })
                        ->form([
                            Forms\Components\Select::make('grup_tes_id')
                                ->label('Pilih Grup Tes')
                                ->options(
                                    \App\Models\MasterGrupTes::all()->mapWithKeys(function ($item) {
                                        return [
                                            $item->id => 'Grup ' . $item->nomor_grup . ' - ' . \Carbon\Carbon::parse($item->tanggal_tes)->translatedFormat('d M Y'),
                                        ];
                                    })
                                )
                                ->required(),
                        ])
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->visible(fn () => auth()->user()->hasRole('Admin'))
                        ->before(function (Collection $records, array $data) {
                            if ($records->count() > 20) {
                                throw new \Exception('Maksimal hanya bisa memilih 20 peserta.');
                            }
                        }),
                    Tables\Actions\BulkAction::make('validasiPembayaran')
                        ->label('Validasi Pembayaran')
                        ->icon('heroicon-o-currency-dollar')
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
                        ->visible(fn () => auth()->user()->hasRole('Admin'))
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
        if (!auth()->user()->hasRole('Admin')) {
            return null;
        }
        $count = static::getModel()::where('status_pembayaran', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pemohon Perlu ditinjau';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendaftaranEpts::route('/'),
            'create' => Pages\CreatePendaftaranEpt::route('/create'),
            'edit' => Pages\EditPendaftaranEpt::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        if (auth()->user()->hasRole('Admin')) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }
}
