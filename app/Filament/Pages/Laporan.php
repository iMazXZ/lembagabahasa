<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Pages\Actions\Action;
use App\Filament\Widgets\PendaftarEptChart;
use App\Filament\Widgets\PendaftarProdiChart;
use App\Filament\Widgets\NilaiRataRataChart;
use Filament\Forms\Components\DatePicker;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
// KEMBALIKAN DUA BARIS INI
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

// KEMBALIKAN "implements HasForms"
class Laporan extends Page implements HasForms
{
    use HasPageShield;
    // KEMBALIKAN "use InteractsWithForms"
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.laporan';

    // Method lain biarkan seperti yang sudah benar sebelumnya
    public function getHeaderWidgetsColumns(): int
    {
        return 2;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PendaftarEptChart::class,
            NilaiRataRataChart::class,
            PendaftarProdiChart::class,
        ];
    }

    // app/Filament/Pages/Laporan.php

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboard')
                ->label('Kembali ke Dasbor')
                ->url(route('filament.admin.pages.2'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),

            Action::make('exportPdfPeriode')
                ->label('Cetak Laporan (Per Periode)')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                // Tambahkan atribut ini untuk memaksa link terbuka di tab baru
                ->extraAttributes(['target' => '_blank'])
                ->form([
                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->default(now()->startOfMonth())
                        ->required(),
                    DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->default(now()->endOfMonth())
                        ->required(),
                ])
                // Ubah isi action menjadi redirect
                ->action(function (array $data) {
                    $url = route('laporan.export.pdf', [
                        'mulai' => $data['tanggal_mulai'],
                        'selesai' => $data['tanggal_selesai'],
                    ]);

                    // Lakukan redirect biasa. Atribut target='_blank' akan menanganinya.
                    return redirect($url);
                }),

            Action::make('exportPdfAll')
                ->label('Cetak Laporan (Keseluruhan)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(
                    route('laporan.export.all.pdf'),
                    shouldOpenInNewTab: true
                ),
        ];
    }
}