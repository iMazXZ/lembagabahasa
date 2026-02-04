<?php

namespace App\Filament\Resources\ManualCertificateResource\Pages;

use App\Filament\Resources\ManualCertificateResource;
use App\Imports\ManualCertificateImport;
use App\Models\CertificateCategory;
use App\Models\ManualCertificate;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Resources\Components\Tab;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListManualCertificates extends ListRecords
{
    protected static string $resource = ManualCertificateResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('Semua')
                ->badge(ManualCertificate::query()->count()),
        ];

        $categories = CertificateCategory::query()
            ->withCount('certificates')
            ->orderBy('name')
            ->get()
            ->filter(fn (CertificateCategory $category): bool => $category->is_active || $category->certificates_count > 0);

        foreach ($categories as $category) {
            $tabs['category_' . $category->id] = Tab::make($category->name)
                ->badge($category->certificates_count)
                ->query(fn (Builder $query): Builder => $query->where('category_id', $category->id));
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadTemplate')
                ->label('Template CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    $content = implode(PHP_EOL, [
                        'NO INDUK;GROUP;NO ABSN;SRN;NAME;PRODI;LIST;SPEAK;READ;WRIT;PHON;VOC;STRU;TTL;AVE;HRF;BLN;TAHUN;SEM;PRED;',
                        '1;1;1;25340022;NAMA MAHASISWA;Pendidikan Bahasa Inggris;77;78;77;80;68;77;70;525;75;B+;12;2025;1;GOOD;',
                        '2;1;2;25340021;NAMA MAHASISWA 2;Pendidikan Agama Islam;73;75;76;80;70;76;69;518;74;B+;12;2025;1;GOOD;',
                    ]) . PHP_EOL;

                    return response()->streamDownload(function () use ($content): void {
                        echo $content;
                    }, 'manual-certificate-template.csv', [
                        'Content-Type' => 'text/csv; charset=UTF-8',
                    ]);
                }),

            Actions\Action::make('previewCsv')
                ->label('Preview CSV')
                ->icon('heroicon-o-eye')
                ->color('info')
                ->slideOver()
                ->modalHeading('Preview CSV Sertifikat Manual')
                ->modalSubmitActionLabel('Tampilkan Preview')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('File CSV')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', '.csv'])
                        ->required()
                        ->disk('local')
                        ->directory('temp-imports'),
                ])
                ->action(function (array $data): void {
                    $filePath = storage_path('app/private/' . $data['file']);

                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->title('File tidak ditemukan')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $previewImport = new ManualCertificateImport(
                            categoryId: 0,
                            semester: null,
                            issuedAt: now()->toDateString(),
                            studyProgram: null,
                        );

                        $preview = $previewImport->preview($filePath);
                        @unlink($filePath);

                        $summaryLines = [
                            "Total baris data: {$preview['total_rows']}",
                            "Siap di-import: {$preview['valid_rows']}",
                            "Akan dilewati: {$preview['skipped_rows']}",
                        ];

                        foreach (array_slice($preview['reason_counts'], 0, 4, true) as $reason => $count) {
                            $summaryLines[] = "- {$reason}: {$count}";
                        }

                        Notification::make()
                            ->title('Preview selesai')
                            ->body(implode(PHP_EOL, $summaryLines))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Preview gagal')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->slideOver()
                ->modalHeading('Import CSV Sertifikat Manual')
                ->modalSubmitActionLabel('Import Sekarang')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('File CSV')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', '.csv'])
                        ->required()
                        ->disk('local')
                        ->directory('temp-imports')
                        ->helperText('Format: NO INDUK, GROUP, NO ABSN, SRN, NAME, PRODI, LIST, SPEAK, READ, WRIT, PHON, VOC, STRU, TTL, AVE, HRF, BLN, TAHUN, SEM, PRED'),

                    Forms\Components\Select::make('category_id')
                        ->label('Kategori Sertifikat')
                        ->options(CertificateCategory::where('is_active', true)->pluck('name', 'id'))
                        ->required(),

                    Forms\Components\DatePicker::make('issued_at')
                        ->label('Tanggal Terbit')
                        ->required()
                        ->default(now()),

                    Forms\Components\TextInput::make('study_program')
                        ->label('Program Studi (Fallback)')
                        ->placeholder('Kosongkan jika tidak diperlukan')
                        ->helperText('Digunakan hanya jika kolom PRODI pada CSV kosong.'),
                ])
                ->action(function (array $data): void {
                    $filePath = storage_path('app/private/' . $data['file']);
                    
                    if (!file_exists($filePath)) {
                        Notification::make()
                            ->title('File tidak ditemukan')
                            ->danger()
                            ->send();
                        return;
                    }

                    try {
                        $import = new ManualCertificateImport(
                            categoryId: $data['category_id'],
                            semester: null,
                            issuedAt: $data['issued_at'],
                            studyProgram: $data['study_program'] ?? null
                        );

                        Excel::import($import, $filePath);
                        $report = $import->getReportSummary();

                        $reportPath = 'import-reports/manual-certificate-import-' . now()->format('Ymd_His') . '.txt';
                        Storage::disk('local')->put($reportPath, $import->toTextReport());

                        // Clean up temp file
                        @unlink($filePath);

                        Notification::make()
                            ->title('Import berhasil!')
                            ->body(implode(PHP_EOL, [
                                "Berhasil: {$report['imported_rows']}",
                                "Dilewati: {$report['skipped_rows']}",
                                "Diproses: {$report['processed_rows']}",
                                "Laporan: storage/app/private/{$reportPath}",
                            ]))
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import gagal')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\CreateAction::make(),
        ];
    }

    protected function configureCreateAction(CreateAction | Tables\Actions\CreateAction $action): void
    {
        parent::configureCreateAction($action);

        $action
            ->slideOver()
            ->url(null)
            ->modalHeading('Buat Sertifikat Manual')
            ->modalSubmitActionLabel('Simpan Sertifikat')
            ->modalWidth('7xl');
    }
}
