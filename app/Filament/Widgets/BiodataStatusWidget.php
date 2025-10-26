<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class BiodataStatusWidget extends Widget
{
    use HasWidgetShield;

    protected static string $view = 'filament.widgets.biodata-status-widget';

    /** Lebar 1 kolom (integer) */
    protected int|string|array $columnSpan = 1;

    /** Dibaca di Blade */
    public bool $isBiodataComplete = false;

    /** Widget hanya untuk user role pendaftar (dua varian kapitalisasi). */
    public static function canView(): bool
    {
        $u = auth()->user();
        return $u !== null && $u->hasAnyRole(['pendaftar', 'Pendaftar']);
    }

    /** Pass data ke Blade */
    protected function getViewData(): array
    {
        $u = auth()->user();

        $hasPrody = !empty($u->prody_id) || $u->prody()->exists();
        $srn      = trim((string) ($u->srn  ?? ''));
        $yearStr  = trim((string) ($u->year ?? ''));
        $yearInt  = (int) $u->year;

        $requireManual = $yearInt <= 2024;
        $hasManual     = is_numeric($u->nilaibasiclistening);

        $this->isBiodataComplete = $hasPrody && $srn !== '' && $yearStr !== '' && (
            $requireManual ? $hasManual : true
        );

        return [
            'user'              => $u,
            'isBiodataComplete' => $this->isBiodataComplete,
        ];
    }
}
