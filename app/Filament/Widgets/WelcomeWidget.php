<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\PendaftaranEpt;
use Carbon\Carbon;

class WelcomeWidget extends ChartWidget
{
    protected static ?string $heading = 'Statistik Pendaftar EPT per Bulan';

    protected function getData(): array
    {
        // Ambil jumlah pendaftar per bulan dari Januari sampai Desember
        $months = collect(range(1, 12))->map(function ($month) {
            return Carbon::create()->month($month)->format('F');
        });

        $data = $months->map(function ($monthName, $i) {
            $month = $i + 1;

            return PendaftaranEpt::whereMonth('created_at', $month)->count();
        });

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pendaftar',
                    'data' => $data,
                    'backgroundColor' => '#3b82f6', // biru
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // atau 'line', 'pie', 'doughnut'
    }
}
