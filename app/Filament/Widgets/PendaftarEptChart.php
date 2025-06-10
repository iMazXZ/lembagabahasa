<?php

namespace App\Filament\Widgets;

use App\Models\PendaftaranEpt;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class PendaftarEptChart extends ChartWidget
{
    use HasWidgetShield;

    protected static ?string $heading = 'Grafik Tren Pendaftar EPT (12 Bulan Terakhir)';

    protected static ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = PendaftaranEpt::query()
            ->where('status_pembayaran', 'approved')
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('count', 'month')
            ->all();

        // Siapkan array untuk 12 bulan terakhir
        $labels = [];
        $values = [];
        $currentMonth = now()->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthKey = $currentMonth->format('Y-m');
            $labels[] = $currentMonth->format('M Y');
            $values[] = $data[$monthKey] ?? 0;
            $currentMonth->subMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pendaftar',
                    'data' => array_reverse($values),
                    'borderColor' => 'rgb(54, 162, 235)',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                ],
            ],
            'labels' => array_reverse($labels),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}