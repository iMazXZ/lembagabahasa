<?php

namespace App\Providers;

use App\Support\QueueMonitor;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
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
use App\Models\InteractiveClassScore;
use App\Policies\BasicListeningAttemptPolicy;
use App\Policies\BasicListeningConnectCodePolicy;
use App\Policies\InteractiveClassScorePolicy;

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

        // === Registrasi eksplisit policy (opsional jika auto-discovery sudah aktif) ===
        Gate::policy(BasicListeningAttempt::class, BasicListeningAttemptPolicy::class);
        Gate::policy(BasicListeningConnectCode::class, BasicListeningConnectCodePolicy::class);
        Gate::policy(InteractiveClassScore::class, InteractiveClassScorePolicy::class);

        // === Registrasi WhatsApp notification channel ===
        \Illuminate\Support\Facades\Notification::extend('whatsapp', function ($app) {
            return new \App\Channels\WhatsAppChannel();
        });

        Queue::before(function (JobProcessing $event) {
            $payload = $event->job->payload();
            $commandName = QueueMonitor::extractCommandName($payload);

            if (! QueueMonitor::isMonitored($commandName)) {
                return;
            }

            QueueMonitor::touchHeartbeat('processing', $commandName, $event->job->getQueue());
        });

        Queue::after(function (JobProcessed $event) {
            $payload = $event->job->payload();
            $commandName = QueueMonitor::extractCommandName($payload);

            if (! QueueMonitor::isMonitored($commandName)) {
                return;
            }

            QueueMonitor::touchHeartbeat('processed', $commandName, $event->job->getQueue());
        });

        Queue::failing(function (JobFailed $event) {
            $payload = $event->job->payload();
            $commandName = QueueMonitor::extractCommandName($payload);

            if (! QueueMonitor::isMonitored($commandName)) {
                return;
            }

            QueueMonitor::touchHeartbeat('failed', $commandName, $event->job->getQueue());
        });

        // Jalur tunggal outbound WA: 1 pesan setiap 2 menit agar backlog tidak meledak.
        RateLimiter::for('wa-outbound', fn () => Limit::perMinutes(2, 1)->by('wa-outbound'));
    }
}
