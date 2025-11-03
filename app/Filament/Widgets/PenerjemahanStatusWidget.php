<?php

namespace App\Filament\Widgets;

use App\Models\Penerjemahan;
use Filament\Actions\Action;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Storage;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class PenerjemahanStatusWidget extends Widget
{

    use HasWidgetShield;
    
    protected static string $view = 'filament.widgets.penerjemahan-status-widget';
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
            ->url(route('filament.admin.resources.penerjemahan.index'))
            ->color('primary')
            ->icon('heroicon-s-document-plus');
    }

    // Tombol "Riwayat"
    protected function getRiwayatAction(): Action
    {
        return Action::make('riwayatPenerjemahan')
            ->label('Riwayat')
            ->url(route('filament.admin.resources.penerjemahan.index'))
            ->color('primary')
            ->icon('heroicon-s-document-text');
    }

    // Tombol Download Hasil
    protected function getDownloadHasilAction(): Action
    {
        return Action::make('downloadHasil')
            ->label('Download Hasil')
            ->url(fn () => $this->latestPenerjemahan->dokumen_terjemahan ? Storage::url($this->latestPenerjemahan->dokumen_terjemahan) : null)
            ->openUrlInNewTab()
            ->color('success')
            ->icon('heroicon-s-cloud-arrow-down');
    }
}