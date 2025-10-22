<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Illuminate\Contracts\Support\Htmlable;

use App\Filament\Widgets\StatsWidget;
use App\Filament\Widgets\BiodataStatusWidget;
use App\Filament\Widgets\PengumumanWidget;
use App\Filament\Widgets\EptStatusWidget;
use App\Filament\Widgets\PenerjemahanStatusWidget;
use App\Filament\Widgets\MyEptSubmissionStatus;
use Filament\Widgets\AccountWidget;
use App\Filament\Widgets\QuickLinks;

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
        return [
            AccountWidget::class,
            BiodataStatusWidget::class,
            PengumumanWidget::class,
            StatsWidget::class,
            EptStatusWidget::class,
            PenerjemahanStatusWidget::class,
            MyEptSubmissionStatus::class,
            QuickLinks::class,
        ];
    }
}