<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EptSubmissionStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $status,
        public ?string $verificationUrl = null,
        public ?string $pdfUrl = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = match ($this->status) {
            'approved' => 'Berhasil Dibuat',
            'rejected' => 'Ditolak',
            'pending'  => 'Menunggu Tinjauan',
            default    => ucfirst($this->status),
        };

        $mail = (new MailMessage)
            ->subject("Surat Rekomendasi EPT — {$statusLabel}")
            ->greeting("Yth. {$notifiable->name},")
            ->line('Melalui email ini kami sampaikan status pengajuan **Surat Rekomendasi EPT** Anda.');

        if ($this->status === 'approved') {
            $mail->line('Pengajuan Anda telah **Disetujui** dan **Berhasil Dibuat**.');
            $mail->line('Silakan **unduh dokumen Surat Rekomendasi**, kemudian cetak. '
                .'Bawa cetakan tersebut ke **Kantor Lembaga Bahasa, Kampus 3 UM Metro, Gedung FIKOM Lantai 2** '
                .'untuk mendapatkan **Cap Basah** dan **Legalisir** *(bila diperlukan)*.');
        }

        if ($this->status === 'rejected') {
            $mail->line('Pengajuan Anda **ditolak**. Silakan meninjau kembali persyaratan dan memperbaiki dokumen sesuai catatan admin.');
        }

        if ($this->status === 'pending') {
            $mail->line('Pengajuan Anda saat ini **menunggu proses peninjauan** oleh admin.');
        }

        if (filled($this->pdfUrl) && $this->status === 'approved') {
            $mail->action('Unduh Surat Rekomendasi (PDF)', $this->pdfUrl);
        }

        if (filled($this->verificationUrl)) {
            $mail->action('Buka Halaman Verifikasi', $this->verificationUrl);
        }

        $mail->salutation("Hormat kami, Admin Lembaga Bahasa UM Metro");

        return $mail;
    }
}