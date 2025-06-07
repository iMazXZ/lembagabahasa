<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EptRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $reason = '')
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pendaftaran EPT - Ditolak')
            ->greeting('Halo, ' . $notifiable->name)
            ->line('Bukti Pembayaran EPT Anda **Ditolak**')
            ->line('Silakan buka halaman Pendaftaran EPT')
            ->salutation(' Kemudian **Upload Bukti Pembayaran EPT Anda kembali.**');
    }
}

