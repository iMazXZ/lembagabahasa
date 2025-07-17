<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Pages\Actions\Action;
use App\Filament\Widgets\PendaftarEptChart;
use App\Filament\Widgets\PendaftarProdiChart;
use App\Filament\Widgets\NilaiRataRataChart;
use Filament\Forms\Components\DatePicker;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Carbon\Carbon; // <-- Tambahkan ini

class Laporan extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.laporan';

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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('dashboard')
                ->label('Kembali ke Dasbor')
                ->url(route('filament.admin.pages.2'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),

            Action::make('exportPdfPeriode')
                ->label('Cetak Laporan (Custom)')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
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
                ->action(function (array $data) {
                    $url = route('laporan.export.pdf', [
                        'mulai' => $data['tanggal_mulai'],
                        'selesai' => $data['tanggal_selesai'],
                    ]);
                    // Mengarahkan ke tab baru
                    return redirect()->to($url);
                }),

            // TOMBOL BARU DITAMBAHKAN DI SINI
            Action::make('exportPdf3Bulan')
                ->label('Cetak Laporan (3 Bulan Terakhir)')
                ->icon('heroicon-o-document-chart-bar')
                ->color('warning')
                ->action(function () {
                    $tanggalSelesai = Carbon::now()->endOfMonth();
                    // Mengambil 2 bulan sebelumnya + bulan ini = 3 bulan
                    $tanggalMulai = Carbon::now()->subMonths(2)->startOfMonth(); 

                    $url = route('laporan.export.pdf', [
                        'mulai' => $tanggalMulai->toDateString(),
                        'selesai' => $tanggalSelesai->toDateString(),
                    ]);
                    // Mengarahkan ke tab baru
                    return redirect()->to($url);
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