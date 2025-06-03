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
            ->subject('Account Security Update - Lembaga Bahasa')
            ->greeting("Hi {$notifiable->name}")
            ->line('A request was made to update your account password.')
            ->action('Update Password', $this->resetUrl($notifiable))
            ->line("This secure link is valid for the next " . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . " minutes")
            ->line('If you didn\'t initiate this request, please ignore this message.');
    }
 
    protected function resetUrl(mixed $notifiable): string
    {
        return Filament::getResetPasswordUrl($this->token, $notifiable);
    }
 
}
