<?php

namespace App\Filament\Widgets;

use App\Models\Penerjemahan;
use Filament\Actions\Action;
use Filament\Widgets\Widget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class PenerjemahanStatusWidget extends Widget
{

    use HasWidgetShield;
    
    protected static string $view = 'filament.widgets.penerjemahan-status-widget';
    // protected int | string | array $columnSpan = 'full';
    protected int | string | array $columnSpan = 1;
    public ?Penerjemahan $latestPenerjemahan;

    public static function isVisible(): bool
    {
        return auth()->user()->hasRole('Pendaftar');
    }

    public function mount(): void
    {
        $this->latestPenerjemahan = Penerjemahan::where('user_id', auth()->id())
            ->latest()
            ->first();
    }

    // Tombol "Ajukan Penerjemahan"
    protected function getAjukanAction(): Action
    {
        return Action::make('ajukanPenerjemahan')
            ->label('Ajukan Penerjemahan')
            ->url(route('filament.admin.resources.penerjemahan.create'))
            ->color('primary')
            ->icon('heroicon-o-document-plus');
    }

    // Tombol "Lihat Riwayat"
    protected function getRiwayatAction(): Action
    {
        return Action::make('riwayatPenerjemahan')
            ->label('Lihat Riwayat Permohonan')
            ->url(route('filament.admin.resources.penerjemahan.index'))
            ->color('primary')
            ->icon('heroicon-o-document-text');
    }
}