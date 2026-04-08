<?php

namespace App\Notifications;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Jobs\SendWhatsAppNotification;
use App\Support\WhatsAppOutboundThrottle;

class EptSubmissionStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $status,
        public ?string $verificationUrl = null,
        public ?string $pdfUrl = null,
        public ?string $adminNote = null,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['mail', 'database'];

        if (
            in_array($this->status, ['approved', 'rejected'], true)
            && ! empty($notifiable->whatsapp)
            && $notifiable->whatsapp_verified_at
        ) {
            $channels[] = 'whatsapp';
        }

        return $channels;
    }

    public function viaConnections(): array
    {
        return [
            'database' => 'sync',
        ];
    }

    /**
     * Kirim notifikasi via WhatsApp
     */
    public function toWhatsApp(object $notifiable): bool
    {
        $details = match ($this->status) {
            'approved' => "Pengajuan Anda telah DISETUJUI dan Berhasil Dibuat.",
            'rejected' => "Pengajuan Anda DITOLAK.",
            'pending' => "Pengajuan Anda saat ini MENUNGGU PROSES PENINJAUAN oleh admin.",
            default => '',
        };

        if ($this->status === 'approved' && !empty($this->adminNote)) {
            $details .= "\n\nCatatan Admin:\n" . $this->adminNote;
            $details .= "\n\nSilakan unduh dokumen Surat Rekomendasi, kemudian cetak dan bawa ke Kantor Lembaga Bahasa untuk mendapatkan Cap Basah.";
        } elseif ($this->status === 'approved') {
            $details .= "\n\nSilakan unduh dokumen Surat Rekomendasi, kemudian cetak dan bawa ke Kantor Lembaga Bahasa untuk mendapatkan Cap Basah.";
        }

        if ($this->status === 'rejected') {
            if (!empty($this->adminNote)) {
                $details .= "\n\nAlasan Penolakan:\n" . $this->adminNote;
            }
            $details .= "\n\nSilakan meninjau kembali persyaratan dan memperbaiki dokumen sesuai catatan admin.";
        }
        
        $actionUrl = $this->verificationUrl ?? route('dashboard.ept');

        // Kirim via job dengan rate limit (lihat SendWhatsAppNotification)
        SendWhatsAppNotification::dispatch(
            phone: $notifiable->whatsapp,
            type: 'ept_status',
            status: $this->status,
            userName: $notifiable->name,
            details: $details,
            actionUrl: $actionUrl
        )->delay(WhatsAppOutboundThrottle::nextDelaySeconds());

        // Return true supaya notification dianggap berhasil diantrikan.
        return true;
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

    public function toArray(object $notifiable): array
    {
        $title = match ($this->status) {
            'approved' => 'Surat Rekomendasi Disetujui',
            'rejected' => 'Surat Rekomendasi Ditolak',
            'pending' => 'Surat Rekomendasi Menunggu Tinjauan',
            default => 'Status Surat Rekomendasi',
        };

        $body = match ($this->status) {
            'approved' => 'Pengajuan surat rekomendasi Anda telah disetujui dan siap diproses lebih lanjut.',
            'rejected' => 'Pengajuan surat rekomendasi Anda ditolak. Silakan cek catatan admin.',
            'pending' => 'Pengajuan surat rekomendasi Anda sedang menunggu peninjauan admin.',
            default => $this->details ?? 'Status pengajuan surat rekomendasi diperbarui.',
        };

        if ($this->status === 'rejected' && filled($this->adminNote)) {
            $body .= ' Alasan: ' . $this->adminNote;
        }

        return [
            'type' => 'ept_submission_status',
            'status' => $this->status,
            'title' => $title,
            'body' => $body,
            'url' => $this->verificationUrl ?? route('dashboard.ept'),
            'color' => match ($this->status) {
                'approved' => 'emerald',
                'rejected' => 'rose',
                'pending' => 'amber',
                default => 'slate',
            },
            'icon' => match ($this->status) {
                'approved' => 'fa-solid fa-file-circle-check',
                'rejected' => 'fa-solid fa-file-circle-xmark',
                'pending' => 'fa-solid fa-file-circle-question',
                default => 'fa-solid fa-file-lines',
            },
        ];
    }
}
