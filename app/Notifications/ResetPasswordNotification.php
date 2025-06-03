<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notixfication
{
    use Queueable;
 
    public function __construct(private readonly string $token)
    {}
 
    public function via(object $notifiable): array
    {
        return ['mail'];
    }
 
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pembaruan Keamanan Akun - Lembaga Bahasa')
            ->greeting("Halo {$notifiable->name}")
            ->line('Permintaan untuk mengubah kata sandi akun Anda telah diterima.')
            ->action('Ubah Kata Sandi', $this->resetUrl($notifiable))
            ->line("Tautan aman ini berlaku selama " . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . " menit ke depan.")
            ->line('Jika Anda tidak meminta perubahan ini, abaikan pesan ini.');
    }
 
    protected function resetUrl(mixed $notifiable): string
    {
        return Filament::getResetPasswordUrl($this->token, $notifiable);
    }
 
}
