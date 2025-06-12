<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SertifikatSiapDiambilNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        // Constructor sekarang bisa dikosongkan
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Mengikuti pola Anda: $notifiable adalah objek User yang dituju
        $users = $notifiable;

        return (new MailMessage)
                    ->subject('Informasi Pengambilan Sertifikat EPT')
                    ->greeting('Halo, ' . $users->name . '!')
                    ->line('Kami memberitahukan bahwa Sertifikat English Proficiency Test (EPT) Anda telah terbit dan siap untuk diambil.')
                    ->line('Anda dapat mengambil sertifikat di kantor Lembaga Bahasa UM Metro pada jam kerja dengan menunjukkan Kartu Tanda Mahasiswa (KTM) atau identitas diri lainnya.')
                    ->salutation('Terima kasih.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Sertifikat EPT Siap Diambil',
            'body' => 'Sertifikat Anda sudah dapat diambil di kantor Lembaga Bahasa.',
            'url' => '#',
        ];
    }
}