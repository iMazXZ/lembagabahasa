<?php

namespace App\Filament\Pages\Auth;

use Exception;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\CanResetPassword;
use App\Notifications\ResetPasswordNotification; 
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    public function request(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/password-reset/request-password-reset.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();
 
            return;
        }
 
        $data = $this->form->getState();
        $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
            $data,
            function (CanResetPassword $user, string $token): void {
                if (! method_exists($user, 'notify')) {
                    $userClass = $user::class;
                    throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
                }
 
                $notification = new ResetPasswordNotification($token); 
                $notification->url = Filament::getResetPasswordUrl($token, $user); 
                $user->notify($notification);
            },
        );
 
        if ($status !== Password::RESET_LINK_SENT) {
            Notification::make()
                ->title('Gagal Mengirim Tautan Reset Kata Sandi')
                ->body(__($status))
                ->icon('heroicon-o-exclamation-triangle')
                ->seconds(5)
                ->color('danger')
                ->send();
 
            return;
        }
 
        Notification::make()
            ->title('Tautan Reset Kata Sandi Terkirim')
            ->body(str('Silakan periksa email Anda, termasuk folder **Spam** atau **Kotak Masuk**')->inlineMarkdown()->toHtmlString())
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->persistent()
            ->send();
 
        $this->form->fill();
    }
}
