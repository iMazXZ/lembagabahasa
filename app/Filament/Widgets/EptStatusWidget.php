<?php

namespace App\Filament\Widgets;

use App\Models\PendaftaranEpt;
use Filament\Widgets\Widget;
use Filament\Actions\Action;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class EptStatusWidget extends Widget
{

    use HasWidgetShield;
    
    protected static string $view = 'filament.widgets.ept-status-widget';
    // protected int | string | array $columnSpan = 'full';
    protected int | string | array $columnSpan = 1;
    public ?PendaftaranEpt $latestEpt;

    public static function isVisible(): bool
    {
        return auth()->user()->hasRole('Pendaftar');
    }

    public function mount(): void
    {
        $this->latestEpt = PendaftaranEpt::where('user_id', auth()->id())
            ->latest()
            ->first();
    }

    // Mendefinisikan Tombol "Daftar EPT Sekarang"
    protected function getDaftarAction(): Action
    {
        return Action::make('daftarEpt')
            ->label('Daftar EPT Sekarang')
            ->url(route('filament.admin.resources.ept.create'))
            ->color('primary')
            ->icon('heroicon-o-document-plus');
    }

    // Mendefinisikan Tombol "Lihat Riwayat Pendaftaran"
    protected function getRiwayatAction(): Action
    {
        return Action::make('riwayatEpt')
            ->label('Lihat Riwayat Pendaftaran')
            ->url(route('filament.admin.resources.ept.index'))
            ->color('primary')
            ->icon('heroicon-o-document-text');
    }
}