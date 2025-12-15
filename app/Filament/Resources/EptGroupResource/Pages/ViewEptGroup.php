<?php

namespace App\Filament\Resources\EptGroupResource\Pages;

use App\Filament\Resources\EptGroupResource;
use App\Models\EptGroup;
use App\Services\WhatsAppService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewEptGroup extends ViewRecord
{
    protected static string $resource = EptGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            // Tetapkan Jadwal
            Actions\Action::make('tetapkan_jadwal')
                ->label('Tetapkan Jadwal')
                ->icon('heroicon-o-calendar')
                ->color('success')
                ->visible(fn () => !$this->record->jadwal)
                ->form([
                    \Filament\Forms\Components\DateTimePicker::make('jadwal')
                        ->label('Waktu Tes')
                        ->required()
                        ->native(false)
                        ->seconds(false)
                        ->default(now()->setTime(8, 30))
                        ->minDate(now()->startOfDay()),
                ])
                ->action(function (array $data) {
                    $this->record->update(['jadwal' => $data['jadwal']]);
                    Notification::make()
                        ->success()
                        ->title('Jadwal berhasil ditetapkan')
                        ->send();
                }),
            
            // Kirim Notif Bulk
            Actions\Action::make('kirim_notif_wa')
                ->label('Kirim Notif WA Semua')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->visible(fn () => $this->record->jadwal !== null)
                ->requiresConfirmation()
                ->modalHeading('Kirim Notifikasi WhatsApp')
                ->modalDescription(fn () => 
                    'Kirim notifikasi jadwal ke semua peserta grup "' . $this->record->name . '" (' . $this->record->allRegistrations()->count() . ' peserta)?'
                )
                ->action(function () {
                    $this->sendBulkWA($this->record);
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Informasi Grup')
                    ->schema([
                        Components\TextEntry::make('name')
                            ->label('Nama Grup'),
                        Components\TextEntry::make('jadwal')
                            ->label('Jadwal Tes')
                            ->dateTime('l, d M Y H:i')
                            ->placeholder('Belum ditetapkan')
                            ->badge()
                            ->color(fn ($state) => $state ? 'success' : 'warning'),
                        Components\TextEntry::make('lokasi')
                            ->label('Lokasi'),
                    ])
                    ->columns(3),
            ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\EptGroupResource\Widgets\PesertaWidget::class,
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function sendBulkWA(EptGroup $record): void
    {
        $registrations = $record->allRegistrations()->with('user')->get();
        $sent = 0;
        $failed = 0;

        foreach ($registrations as $reg) {
            $user = $reg->user;
            if (!$user->whatsapp || !$user->whatsapp_verified_at) {
                $failed++;
                continue;
            }

            try {
                $tesNum = match(true) {
                    $reg->grup_1_id === $record->id => 1,
                    $reg->grup_2_id === $record->id => 2,
                    $reg->grup_3_id === $record->id => 3,
                    default => null,
                };
                
                $jadwal = $record->jadwal->translatedFormat('l, d F Y H:i');
                $dashboardUrl = route('dashboard.ept-registration.index');

                $message = "*Jadwal Tes EPT Ditetapkan*\n\n";
                $message .= "Yth. *{$user->name}*,\n\n";
                $message .= "Jadwal *Tes ke-{$tesNum}* EPT Anda telah ditetapkan:\n\n";
                $message .= "*Grup:* {$record->name}\n";
                $message .= "*Waktu:* {$jadwal} WIB\n";
                $message .= "*Lokasi:* {$record->lokasi}\n\n";
                $message .= "Silakan download Kartu Peserta melalui:\n{$dashboardUrl}\n\n";
                $message .= "_Wajib membawa kartu peserta dan KTP/Kartu Mahasiswa._";

                app(WhatsAppService::class)->sendMessage($user->whatsapp, $message);
                $sent++;
            } catch (\Exception $e) {
                $failed++;
            }
        }

        Notification::make()
            ->success()
            ->title('Notifikasi terkirim')
            ->body("Berhasil: {$sent}, Gagal: {$failed}")
            ->send();
    }
}
