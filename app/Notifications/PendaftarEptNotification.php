<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use App\Models\PendaftaranEpt;

class PendaftarEptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public PendaftaranEpt $pendaftaranEpt
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Pendaftar EPT Baru')
            ->body("Pendaftar baru dengan nama {$this->pendaftaranEpt->name} telah mendaftar EPT")
            ->icon('heroicon-o-user-plus')
            ->color('success')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Pendaftar')
                    ->url(route('filament.admin.resources.pendaftaranepts.view', $this->pendaftaranEpt))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
