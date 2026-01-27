<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;

use App\Http\Responses\LoginResponse;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningConnectCode;
use App\Policies\BasicListeningAttemptPolicy;
use App\Policies\BasicListeningConnectCodePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponse::class, CustomLogoutResponse::class);

        $this->app->bind(
        LoginResponseContract::class,
        LoginResponse::class,
    );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        FilamentAsset::register([
            Css::make('custom-stylesheet', __DIR__ . '/../../resources/css/custom.css'),
        ]);

        // === Admin override: admin boleh semua ability tanpa cek policy detail ===
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        // === Registrasi eksplisit policy (opsional jika auto-discovery sudah aktif) ===
        Gate::policy(BasicListeningAttempt::class, BasicListeningAttemptPolicy::class);
        Gate::policy(BasicListeningConnectCode::class, BasicListeningConnectCodePolicy::class);

        // === Registrasi WhatsApp notification channel ===
        \Illuminate\Support\Facades\Notification::extend('whatsapp', function ($app) {
            return new \App\Channels\WhatsAppChannel();
        });

        // Rate limit global untuk pengiriman WA agar tidak diblokir.
        RateLimiter::for('wa-notif', fn () => Limit::perMinute(8)->by('wa-notif'));
        RateLimiter::for('wa-otp', fn () => Limit::perMinute(15)->by('wa-otp'));
    }
}
