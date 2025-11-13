<?php

namespace App\Filament\Pages\Auth;

use Exception;
use App\Notifications\ResetPasswordNotification;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function request(): void
    {
        // Prevent spam
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $e) {
            Notification::make()
                ->title('Terlalu Banyak Percobaan')
                ->body("Silakan coba lagi dalam {$e->secondsUntilAvailable} detik.")
                ->danger()
                ->send();
            return;
        }

        // Ambil email
        $email = $this->form->getState()['email'] ?? null;

        if (! $email) {
            throw ValidationException::withMessages([
                'email' => 'Email wajib diisi.',
            ]);
        }

        // Kirim reset link
        $status = Password::broker(Filament::getAuthPasswordBroker())
            ->sendResetLink(
                ['email' => $email],
                function ($user, string $token) {
                    $notification = new ResetPasswordNotification($token);
                    $notification->url = Filament::getResetPasswordUrl($token, $user);
                    $user->notify($notification);
                }
            );

        if ($status !== Password::RESET_LINK_SENT) {
            Notification::make()
                ->title('Gagal Mengirim Tautan Reset')
                ->body('Email tidak ditemukan atau tidak valid.')
                ->danger()
                ->send();
            return;
        }

        Notification::make()
            ->title('Tautan Reset Terkirim')
            ->body('Silakan cek email Anda (termasuk folder Spam).')
            ->success()
            ->send();

        $this->form->fill();
    }
}
