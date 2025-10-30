<?php

namespace App\Filament\Pages;

use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

// === Widget bawaan lama (dipakai kalau ada) ===
use App\Filament\Widgets\PendaftarEptChart;
use App\Filament\Widgets\PendaftarProdiChart;
use App\Filament\Widgets\NilaiRataRataChart;

// === Widget baru (opsional; aman kalau belum ada) ===
// use App\Filament\Widgets\BasicListeningOverview;
// use App\Filament\Widgets\TranslationOverview;

class Laporan extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Pelaporan & Ekspor';
    protected static ?int $navigationSort = 90;

    protected static string $view = 'filament.pages.laporan';

    // ======== HEADER WIDGETS ========

    public function getHeaderWidgetsColumns(): int
    {
        // 2 kolom default; kalau widget > 2, kasih 3 biar lega.
        $count = count($this->getHeaderWidgets());
        return $count >= 3 ? 3 : 2;
    }

    protected function getHeaderWidgets(): array
    {
        $widgets = [];

        // Widget lama yang mungkin masih dipakai
        foreach ([PendaftarEptChart::class, NilaiRataRataChart::class, PendaftarProdiChart::class] as $w) {
            if (class_exists($w)) {
                $widgets[] = $w;
            }
        }

        // Widget opsional (tidak error kalau belum ada)
        foreach ([
            '\App\Filament\Widgets\BasicListeningOverview',
            '\App\Filament\Widgets\TranslationOverview',
            '\App\Filament\Widgets\PostingOverview',
        ] as $maybe) {
            if (class_exists($maybe)) {
                $widgets[] = $maybe;
            }
        }

        return $widgets;
    }

    // ======== HEADER ACTIONS ========

    protected function getHeaderActions(): array
    {
        return [
            // Kembali ke Dashboard (route resmi Filament v3)
            Action::make('dashboard')
                ->label('Kembali ke Dasbor')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => filament()->getPanel()->getUrl()),

            // Ekspor custom range + pilih modul
            Action::make('exportCustom')
                ->label('Cetak Laporan (Custom)')
                ->icon('heroicon-o-calendar-days')
                ->color('primary')
                ->form([
                    Select::make('module')
                        ->label('Modul')
                        ->options([
                            'all'            => 'Semua Modul',
                            'ept'            => 'EPT (pendaftar/nilai)',
                            'basic_listening'=> 'Basic Listening (nilai & attempt)',
                            'penerjemahan'   => 'Penerjemahan (order & progress)',
                        ])
                        ->default('all')
                        ->required()
                        ->native(false),

                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->default(now()->startOfMonth())
                        ->required(),

                    DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->default(now()->endOfMonth())
                        ->required(),

                    Select::make('download')
                        ->label('Mode Unduh')
                        ->helperText('Sesuai preferensi: paksa download PDF (disarankan).')
                        ->options([
                            '1' => 'Paksa download (dl=1)',
                            '0' => 'Buka biasa (inline)',
                        ])
                        ->default('1')
                        ->native(false),
                ])
                ->action(function (array $data) {
                    // Satu endpoint generik: laporan.export.pdf
                    // Controller bisa membaca query: module, mulai, selesai, dl
                    $url = route('laporan.export.pdf', [
                        'module'  => $data['module'] ?? 'all',
                        'mulai'   => Carbon::parse($data['tanggal_mulai'])->toDateString(),
                        'selesai' => Carbon::parse($data['tanggal_selesai'])->toDateString(),
                        'dl'      => (int) ($data['download'] ?? 1),
                    ]);

                    // Catatan: redirect() membuka di tab yang sama.
                    // Jika ingin tab baru, pertimbangkan membuat Action->url(...)->openUrlInNewTab()
                    return redirect()->to($url);
                }),

            // Quick: 3 bulan terakhir (semua modul)
            Action::make('export3months')
                ->label('Cetak Laporan (3 Bulan Terakhir)')
                ->icon('heroicon-o-document-chart-bar')
                ->color('warning')
                ->action(function () {
                    $end   = Carbon::now()->endOfMonth();
                    $start = Carbon::now()->subMonths(2)->startOfMonth();
                    $url = route('laporan.export.pdf', [
                        'module'  => 'all',
                        'mulai'   => $start->toDateString(),
                        'selesai' => $end->toDateString(),
                        'dl'      => 1, // sesuai preferensi: paksa download
                    ]);
                    return redirect()->to($url);
                }),

            // Quick: Semester berjalan (khusus Basic Listening)
            Action::make('exportBlSemesterNow')
                ->label('BL — Cetak Nilai (Semester Berjalan)')
                ->icon('heroicon-o-academic-cap')
                ->color('success')
                ->visible(function () {
                    // Tampilkan tombol ini kalau module BL kemungkinan ada (opsional)
                    return true;
                })
                ->action(function () {
                    // Semester sederhana (Jan–Jun = Genap, Jul–Des = Ganjil)
                    // Kalau kamu pakai InstructionalYear, kamu bisa ganti logika ini dengan query model tsb.
                    $now = Carbon::now();
                    if ($now->month >= 1 && $now->month <= 6) {
                        $start = Carbon::create($now->year, 1, 1)->startOfMonth();
                        $end   = Carbon::create($now->year, 6, 30)->endOfMonth();
                    } else {
                        $start = Carbon::create($now->year, 7, 1)->startOfMonth();
                        $end   = Carbon::create($now->year, 12, 31)->endOfMonth();
                    }

                    $url = route('laporan.export.pdf', [
                        'module'  => 'basic_listening',
                        'mulai'   => $start->toDateString(),
                        'selesai' => $end->toDateString(),
                        'dl'      => 1,
                    ]);

                    return redirect()->to($url);
                }),

            // Quick: Bulan ini (Penerjemahan)
            Action::make('exportTranslationThisMonth')
                ->label('Penerjemahan — Bulan Ini')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->action(function () {
                    $start = Carbon::now()->startOfMonth();
                    $end   = Carbon::now()->endOfMonth();
                    $url = route('laporan.export.pdf', [
                        'module'  => 'penerjemahan',
                        'mulai'   => $start->toDateString(),
                        'selesai' => $end->toDateString(),
                        'dl'      => 1,
                    ]);
                    return redirect()->to($url);
                }),

            // Ekspor keseluruhan (semua modul)
            Action::make('exportAll')
                ->label('Cetak Laporan (Keseluruhan)')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(
                    route('laporan.export.all.pdf', ['dl' => 1]),
                    shouldOpenInNewTab: false
                ),
        ];
    }
}
