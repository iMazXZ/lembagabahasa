<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptRegistrationResource\Pages;
use App\Models\EptRegistration;
use App\Services\WhatsAppService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class EptRegistrationResource extends Resource
{
    protected static ?string $model = EptRegistration::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'EPT';
    protected static ?string $navigationLabel = 'Pendaftaran EPT';
    protected static ?string $modelLabel = 'Pendaftaran EPT';
    protected static ?string $pluralModelLabel = 'Pendaftaran EPT';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Pendaftar')->schema([
                Forms\Components\Placeholder::make('user_name')
                    ->label('Nama')
                    ->content(fn ($record) => $record->user->name ?? '-'),
                Forms\Components\Placeholder::make('user_srn')
                    ->label('NPM')
                    ->content(fn ($record) => $record->user->srn ?? '-'),
                Forms\Components\Placeholder::make('user_prody')
                    ->label('Program Studi')
                    ->content(fn ($record) => $record->user->prody->name ?? '-'),
            ])->columns(3),

            Forms\Components\Section::make('Bukti Pembayaran')->schema([
                Forms\Components\Placeholder::make('bukti')
                    ->label('')
                    ->content(fn ($record) => view('filament.components.image-preview', [
                        'url' => Storage::url($record->bukti_pembayaran),
                    ])),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.srn')
                    ->label('NPM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.prody.name')
                    ->label('Prodi')
                    ->limit(20),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                // APPROVE ACTION
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\TextInput::make('grup_1')
                            ->label('Nama Grup 1')
                            ->required()
                            ->placeholder('Contoh: Grup 001 Pasca'),
                        Forms\Components\DateTimePicker::make('jadwal_1')
                            ->label('Jadwal Tes 1')
                            ->required(),
                        Forms\Components\TextInput::make('grup_2')
                            ->label('Nama Grup 2')
                            ->required(),
                        Forms\Components\DateTimePicker::make('jadwal_2')
                            ->label('Jadwal Tes 2')
                            ->required(),
                        Forms\Components\TextInput::make('grup_3')
                            ->label('Nama Grup 3')
                            ->required(),
                        Forms\Components\DateTimePicker::make('jadwal_3')
                            ->label('Jadwal Tes 3')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'grup_1' => $data['grup_1'],
                            'jadwal_1' => $data['jadwal_1'],
                            'grup_2' => $data['grup_2'],
                            'jadwal_2' => $data['jadwal_2'],
                            'grup_3' => $data['grup_3'],
                            'jadwal_3' => $data['jadwal_3'],
                        ]);

                        // Send WhatsApp notification
                        $user = $record->user;
                        if ($user->whatsapp && $user->whatsapp_verified_at) {
                            try {
                                $jadwal1 = \Carbon\Carbon::parse($data['jadwal_1'])->translatedFormat('l, d F Y H:i');
                                $jadwal2 = \Carbon\Carbon::parse($data['jadwal_2'])->translatedFormat('l, d F Y H:i');
                                $jadwal3 = \Carbon\Carbon::parse($data['jadwal_3'])->translatedFormat('l, d F Y H:i');

                                $dashboardUrl = route('dashboard.ept-registration.index');

                                $message = "*Pendaftaran EPT Disetujui*\n\n";
                                $message .= "Yth. *{$user->name}*,\n\n";
                                $message .= "Pendaftaran Tes EPT Anda telah *disetujui*. Berikut jadwal tes yang telah ditetapkan:\n\n";
                                $message .= "*Jadwal Tes:*\n";
                                $message .= "1. {$data['grup_1']}\n   {$jadwal1} WIB\n";
                                $message .= "2. {$data['grup_2']}\n   {$jadwal2} WIB\n";
                                $message .= "3. {$data['grup_3']}\n   {$jadwal3} WIB\n\n";
                                $message .= "*Lokasi:* Ruang Stanford\n\n";
                                $message .= "Silakan download dan cetak *Kartu Peserta* melalui link berikut:\n{$dashboardUrl}\n\n";
                                $message .= "_Wajib membawa kartu peserta dan KTP/Kartu Mahasiswa saat ujian._";

                                app(WhatsAppService::class)->sendMessage($user->whatsapp, $message);
                            } catch (\Exception $e) {
                                // Log error but don't fail
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Pendaftaran Disetujui')
                            ->body('Jadwal berhasil disimpan dan notifikasi WA terkirim.')
                            ->send();
                    }),

                // REJECT ACTION
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);

                        // Send WhatsApp notification
                        $user = $record->user;
                        if ($user->whatsapp && $user->whatsapp_verified_at) {
                            try {
                                $dashboardUrl = route('dashboard.ept-registration.index');

                                $message = "*Pendaftaran EPT Ditolak*\n\n";
                                $message .= "Yth. *{$user->name}*,\n\n";
                                $message .= "Mohon maaf, pendaftaran Tes EPT Anda *tidak dapat diproses*.\n\n";
                                $message .= "*Alasan:*\n{$data['rejection_reason']}\n\n";
                                $message .= "Silakan upload ulang bukti pembayaran yang valid melalui link berikut:\n{$dashboardUrl}\n\n";
                                $message .= "_Terima kasih atas pengertiannya._";

                                app(WhatsAppService::class)->sendMessage($user->whatsapp, $message);
                            } catch (\Exception $e) {
                                // Log error but don't fail
                            }
                        }

                        Notification::make()
                            ->warning()
                            ->title('Pendaftaran Ditolak')
                            ->body('Notifikasi penolakan terkirim via WA.')
                            ->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptRegistrations::route('/'),
            'view' => Pages\ViewEptRegistration::route('/{record}'),
        ];
    }
}
