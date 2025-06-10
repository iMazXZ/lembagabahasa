<?php

namespace App\Filament\Widgets;

use App\Models\DataNilaiTes;
use Filament\Widgets\ChartWidget;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class NilaiRataRataChart extends ChartWidget
{

    use HasWidgetShield;

    protected static ?string $heading = 'Grafik Rata-rata Skor EPT (12 Bulan Terakhir)';
    
    protected static ?string $maxHeight = '300px';

    // Atur widget ini untuk mengambil setengah dari lebar grid
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = DataNilaiTes::query()
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, AVG(total_score) as average_score')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('average_score', 'month')
            ->all();

        // Siapkan array untuk 12 bulan terakhir
        $labels = [];
        $values = [];
        $currentMonth = now()->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthKey = $currentMonth->format('Y-m');
            $labels[] = $currentMonth->format('M Y');
            // Bulatkan nilai rata-rata ke 2 angka desimal
            $values[] = isset($data[$monthKey]) ? round($data[$monthKey], 2) : 0;
            $currentMonth->subMonth();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nilai Rata-rata',
                    'data' => array_reverse($values),
                    'borderColor' => '#4BC0C0', // Warna hijau
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
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