<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Contracts\Support\Htmlable;

use App\Filament\Widgets\StatsWidget;
use App\Filament\Widgets\PengumumanWidget;
use Filament\Widgets\AccountWidget;
use App\Filament\Widgets\QuickLinks;
use App\Filament\Widgets\StudentBasicListeningWidget;
use App\Filament\Widgets\AdminQueuesWidget;

class DashboardKustom extends Page
{
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    public static ?string $slug = '2';

    public function getTitle(): string | Htmlable
    {
        return '';
    }

    protected static string $view = 'filament.pages.dashboard-kustom';

    protected static ?int $navigationSort = -2;

    public function getHeaderWidgetsColumns(): int
    {
        if(auth()->user()->hasRole('Pendaftar')) {
            return 3;
        }
        return 2;
    }

    protected function getHeaderWidgets(): array
    {
        $widgets = [
            AccountWidget::class,
            PengumumanWidget::class,
            QuickLinks::class,
            StatsWidget::class,
            StudentBasicListeningWidget::class,
        ];

        if (auth()->user()?->hasRole('Admin')) {
            $widgets[] = AdminQueuesWidget::class;
        }

        return $widgets;
    }
}
