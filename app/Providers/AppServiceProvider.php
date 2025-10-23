<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use App\Http\Responses\LogoutResponse as CustomLogoutResponse;

use App\Models\PendaftaranEpt;
use App\Observers\PendaftaranEptObserver;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;

// === Tambahan: model & policy yang mau diregistrasi ===
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Carbon\Carbon::setLocale('id');
        PendaftaranEpt::observe(PendaftaranEptObserver::class);

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
    }
}
