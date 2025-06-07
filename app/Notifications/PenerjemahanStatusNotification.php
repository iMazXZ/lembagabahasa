<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PenerjemahanStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Permohonan Penerjemahan [ {$this->status} ]")
            ->greeting("Halo, {$notifiable->name}")
            ->line("Status Penerjemahan Dokumen Abstrak Anda")
            ->line("**{$this->status}**")
            ->when(in_array($this->status, ['Ditolak - Pembayaran Tidak Valid', 'Ditolak - Dokumen Tidak Valid']), function ($message) {
                return $message->line('Silakan buka halaman Penerjemahan Dokumen Abstrask');
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
