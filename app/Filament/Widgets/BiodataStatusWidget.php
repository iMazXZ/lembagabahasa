<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Widgets\Widget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class BiodataStatusWidget extends Widget
{

    use HasWidgetShield;

    protected static string $view = 'filament.widgets.biodata-status-widget';
    protected int | string | array $columnSpan = '1';
    public bool $isBiodataComplete = false;

    public static function isVisible(): bool
    {
        return auth()->user()->hasRole('Pendaftar');
    }

    public function mount(): void
    {
        $user = auth()->user();
        
        // KOREKSI: Menggunakan logika baru untuk mengecek biodata lengkap
        $isComplete =
            !is_null($user->nilaibasiclistening) &&
            ($user->prody !== null && $user->prody !== '') &&
            ($user->srn !== null && $user->srn !== '') &&
            ($user->year !== null && $user->year !== '');

        $this->isBiodataComplete = $isComplete;
    }

    // Tombol "Lengkapi Biodata"
    protected function getLengkapiAction(): Action
    {
        return Action::make('lengkapiBiodata')
            ->label('Lengkapi')
            ->url(route('filament.admin.pages.biodata'))
            ->color('warning')
            ->icon('heroicon-o-pencil-square');
    }

    // Tombol "Ubah Biodata"
    protected function getUbahAction(): Action
    {
        return Action::make('ubahBiodata')
            ->label('Ubah')
            ->url(route('filament.admin.pages.biodata'))
            ->color('gray')
            ->icon('heroicon-o-pencil-square');
    }
}