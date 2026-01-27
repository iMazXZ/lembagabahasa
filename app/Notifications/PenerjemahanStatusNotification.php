<?php

namespace App\Notifications;

use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Jobs\SendWhatsAppNotification;

class PenerjemahanStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $status,
        public ?string $verificationUrl = null,
    ) {}

    public function via(object $notifiable): array
    {
        // Jika user punya nomor WhatsApp yang terverifikasi, kirim via WA saja
        if (!empty($notifiable->whatsapp) && $notifiable->whatsapp_verified_at) {
            return ['whatsapp'];
        }
        
        // Fallback ke email
        return ['mail'];
    }

    /**
     * Kirim notifikasi via WhatsApp
     */
    public function toWhatsApp(object $notifiable): bool
    {
        $details = match (true) {
            $this->status === 'Diproses' => 'Dokumen Anda sedang dalam proses diterjemahkan oleh Tim Penerjemah.',
            $this->status === 'Selesai' => 'Dokumen sudah siap didownload di Menu Penerjemahan Dokumen Abstrak.',
            str_contains($this->status, 'Ditolak') => 'Silakan upload kembali dokumen yang sesuai.',
            default => '',
        };
        
        $actionUrl = ($this->status === 'Selesai' && $this->verificationUrl) 
            ? $this->verificationUrl 
            : route('dashboard.translation');

        SendWhatsAppNotification::dispatch(
            phone: $notifiable->whatsapp,
            type: 'penerjemahan_status',
            status: $this->status,
            userName: $notifiable->name,
            details: $details,
            actionUrl: $actionUrl
        );

        return true;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Permohonan Penerjemahan [ {$this->status} ]")
            ->greeting("Halo, {$notifiable->name}")
            ->line("Status Penerjemahan Dokumen Abstrak Anda")
            ->line("**{$this->status}**")
            ->when(in_array($this->status, ['Ditolak - Pembayaran Tidak Valid', 'Ditolak - Dokumen Tidak Valid']), function ($message) {
                return $message->line('Silakan buka halaman Penerjemahan Dokumen Abstrak');
            })
            ->when(in_array($this->status, ['Diproses']), function ($message) {
                return $message->line('Dokumen Anda sedang dalam **Proses Diterjemahkan** oleh Tim Penerjemah');
            })
            ->when(in_array($this->status, ['Selesai']), function ($message) {
                return $message->line('Dokumen Sudah Siap **Didownload** di Menu Penerjemahan Dokumen Abstrak');
            })
            ->when(in_array($this->status, ['Ditolak - Pembayaran Tidak Valid', 'Ditolak - Dokumen Tidak Valid']), function ($message) {
                return $message->salutation('Silakan **Upload Kembali Dokumen yang Sesuai.**');
            }, function ($message) {
                return $message->salutation('Regards, Admin.');
            });
    }
}
