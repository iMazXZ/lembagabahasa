<?php

namespace App\Filament\Widgets;

use App\Models\PendaftaranEpt;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class PendaftarProdiChart extends ChartWidget
{

    use HasWidgetShield;

    protected static ?string $heading = 'Sebaran Pendaftar EPT per Program Studi';

    protected static ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = PendaftaranEpt::query()
            ->where('status_pembayaran', 'approved')
            ->join('users', 'pendaftaran_epts.user_id', '=', 'users.id')
            ->join('prodies', 'users.prody_id', '=', 'prodies.id')
            ->select('prodies.name', DB::raw('count(pendaftaran_epts.id) as total'))
            ->groupBy('prodies.name')
            ->pluck('total', 'name')
            ->all();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pendaftar',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40',
                        '#E7E9ED', '#8DDF3C', '#F99BFF', '#A3A8A5'
                    ],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        // Ubah tipe grafik menjadi 'pie'
        return 'pie';
    }
}