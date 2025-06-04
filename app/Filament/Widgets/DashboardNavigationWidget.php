<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Filament\Widgets\Concerns\InteractsWithPage;

class DashboardNavigationWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-navigation-widget';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['pendaftar']);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('pendaftaran_ept')
                ->label('Pendaftaran EPT')
                ->url(fn (): string => route('filament.admin.resources.ept.index'))
                ->button()
                ->color('primary'),
            Action::make('penerjemahan_dokumen_abstrak')
                ->label('Penerjemahan Dokumen Abstrak')
                ->url(fn (): string => route('filament.admin.resources.penerjemahan.index'))
                ->button()
                ->color('success'),
            Action::make('biodata')
                ->label('Lengkapi Biodata')
                ->url(fn (): string => route('filament.admin.pages.biodata'))
                ->button()
                ->color('gray'),
        ];
    }
}