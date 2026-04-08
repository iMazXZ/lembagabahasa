<?php

namespace App\Filament\Resources\EptGroupResource\Pages;

use App\Filament\Resources\EptGroupResource;
use App\Models\EptGroup;
use App\Notifications\EptScheduleAssignedNotification;
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
                ->label('Kirim Notifikasi Semua')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('info')
                ->visible(fn () => $this->record->jadwal !== null)
                ->requiresConfirmation()
                ->modalHeading('Kirim Notifikasi')
                ->modalDescription(fn () => 
                    'Kirim notifikasi jadwal ke semua peserta grup "' . $this->record->name . '" (' . $this->record->allRegistrations()->count() . ' peserta)?'
                )
                ->action(function () {
                    $this->sendBulkWA($this->record);
                }),

            Actions\Action::make('export_bukti_pembayaran')
                ->label('Export Bukti Pembayaran')
                ->icon('heroicon-o-document-arrow-down')
                ->color('warning')
                ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi']))
                ->action(function () {
                    return redirect()->to(route('admin.ept-group-export-bukti.preview', [
                        'group' => $this->record->id,
                    ]));
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
        $emailQueued = 0;
        $waQueued = 0;
        $failed = 0;

        foreach ($registrations as $reg) {
            $user = $reg->user;

            if (! $user) {
                $failed++;
                continue;
            }

            try {
                $tesNum = $reg->testNumberForGroupId((int) $record->id);
                if ($tesNum === null) {
                    $failed++;
                    continue;
                }

                $user->notify(new EptScheduleAssignedNotification(
                    testNumber: $tesNum,
                    groupName: $record->name,
                    scheduledAt: $record->jadwal,
                    location: (string) $record->lokasi,
                    dashboardUrl: route('dashboard.ept-registration.index'),
                ));

                $emailQueued++;

                if ($user->whatsapp && $user->whatsapp_verified_at) {
                    $waQueued++;
                }
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        Notification::make()
            ->success()
            ->title('Notifikasi diproses')
            ->body("Email: {$emailQueued}, WA: {$waQueued}, Gagal: {$failed}")
            ->send();
    }
}
