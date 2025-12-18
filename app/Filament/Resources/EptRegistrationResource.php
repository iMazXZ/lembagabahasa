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

                // DOWNLOAD BUKTI PEMBAYARAN AS PNG
                Tables\Actions\Action::make('download_bukti')
                    ->label('Download Bukti')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn ($record) => !empty($record->bukti_pembayaran))
                    ->action(function ($record) {
                        // Use 'public' disk since that's where files are stored
                        $publicDisk = Storage::disk('public');
                        
                        if (!$publicDisk->exists($record->bukti_pembayaran)) {
                            Notification::make()
                                ->danger()
                                ->title('File tidak ditemukan')
                                ->send();
                            return;
                        }

                        $filePath = $publicDisk->path($record->bukti_pembayaran);

                        // Use Intervention Image (v3) to convert to PNG
                        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                        $image = $manager->read($filePath);
                        $pngData = $image->toPng();
                        
                        $filename = 'bukti_pembayaran_' . $record->user->srn . '_' . now()->format('Ymd') . '.png';
                        
                        return response()->streamDownload(function () use ($pngData) {
                            echo $pngData;
                        }, $filename, [
                            'Content-Type' => 'image/png',
                        ]);
                    }),
                
                // APPROVE ACTION
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Select::make('grup_1_id')
                            ->label('Grup Tes 1')
                            ->options(\App\Models\EptGroup::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('grup_2_id')
                            ->label('Grup Tes 2')
                            ->options(\App\Models\EptGroup::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('grup_3_id')
                            ->label('Grup Tes 3')
                            ->options(\App\Models\EptGroup::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'approved',
                            'grup_1_id' => $data['grup_1_id'],
                            'grup_2_id' => $data['grup_2_id'],
                            'grup_3_id' => $data['grup_3_id'],
                        ]);

                        // Kirim notifikasi WA persetujuan
                        $user = $record->user;
                        if ($user->whatsapp && $user->whatsapp_verified_at) {
                            try {
                                $dashboardUrl = route('dashboard.ept-registration.index');

                                $message = "*Pendaftaran EPT Diterima* ✅\n\n";
                                $message .= "Yth. *{$user->name}*,\n\n";
                                $message .= "Pembayaran Tes EPT Anda sudah kami verifikasi dan *valid*.\n\n";
                                $message .= "Mohon menunggu penetapan jadwal tes. Ketika kuota peserta sudah terpenuhi, jadwal tes akan segera dikirimkan melalui WhatsApp.\n\n";
                                $message .= "Silakan pantau status pendaftaran Anda di:\n{$dashboardUrl}\n\n";
                                $message .= "_Terima kasih telah mendaftar._";

                                app(WhatsAppService::class)->sendMessage($user->whatsapp, $message);
                            } catch (\Exception $e) {
                                // Log error but don't fail
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Pendaftaran Disetujui')
                            ->body('Peserta berhasil ditambahkan ke grup. Notifikasi WA telah dikirim.')
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
