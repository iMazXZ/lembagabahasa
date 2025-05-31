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
                    ->default(null),
                Forms\Components\Select::make('status_pembayaran')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->disabled()
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
                    ->color('success'),
                Tables\Columns\BadgeColumn::make('status_pembayaran')
                    ->label('Status Pembayaran')
                    ->colors([
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak - Bukti Tidak Valid',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Daftar Pada')
                    ->dateTime()
                    ->sortable(),
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
                    ->visible(fn () => auth()->user()->hasRole('Admin'))
                    ->action(fn ($record) => $record->update(['status_pembayaran' => 'approved'])),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn () => auth()->user()->hasRole('Admin'))
                    ->action(fn ($record) => $record->update(['status_pembayaran' => 'rejected'])),
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
            //
        ];
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
