<?php

namespace App\Providers\Filament;

use Filament\Contracts\Plugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Auth\Register;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;

use App\Filament\Widgets\StatsWidget;
use App\Filament\Pages\Auth\RequestPasswordReset;
use App\Filament\Widgets\PengumumanWidget;
use App\Filament\Pages\DashboardKustom;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->maxContentWidth('full')
            ->id('admin')
            ->path('dashboard')
            ->login()
            ->registration(Register::class)
            ->passwordReset(RequestPasswordReset::class)
            ->emailVerification()
            ->emailVerificationRoutePrefix('verif')
            ->emailVerificationPromptRouteSlug('abort')
            ->emailVerificationRouteSlug('send')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                DashboardKustom::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                
            ])
            ->brandLogo(fn () => view('filament.logo'))
            ->favicon(asset('favicon.ico'))
            ->brandName('Lembaga Bahasa UM Metro')
            ->navigationGroups([
                'Layanan Lembaga Bahasa',
                'Manajemen EPT',
            ])
            // ->navigationItems([
            //     NavigationItem::make('Halaman Utama')
            //         ->url(url('/'), shouldOpenInNewTab: true)
            //         ->icon('heroicon-o-globe-alt')
            //         ->sort(-3),
            // ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::auth.login.form.after',
                fn (): string => Blade::render('<div class="text-center mt-6"><a href="/" class="text-sm text-primary-600 transition">← Kembali ke <strong>Halaman Utama</strong></a></div>'),
            )
            ->renderHook(
                'panels::auth.password-reset.request.form.after',
                fn (): string => Blade::render('<div class="text-sm text-center text-primary-600 dark:text-gray-400 mt-4"><p>Setelah mengirim permintaan, jangan lupa periksa folder <strong>SPAM</strong> di email Anda jika email tidak kunjung masuk.</p></div>'),
            )
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ])
            ->databaseNotifications();
    }

}
