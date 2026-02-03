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
                        ->helperText('Format baru: NO INDUK, GROUP, NO ABSN, SRN, NAME, LIST, SPEAK, READ, WRIT, PHON, VOC, STRU, TTL, AVE, HRF, BLN, TAHUN, SEM, PRED'),

                    Forms\Components\Select::make('category_id')
                        ->label('Kategori Sertifikat')
                        ->options(CertificateCategory::where('is_active', true)->pluck('name', 'id'))
                        ->required(),

                    Forms\Components\DatePicker::make('issued_at')
                        ->label('Tanggal Terbit')
                        ->required()
                        ->default(now()),

                    Forms\Components\TextInput::make('study_program')
                        ->label('Program Studi')
                        ->default('ENGLISH EDUCATION')
                        ->helperText('Akan diterapkan ke semua record yang di-import'),
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

                        // Clean up temp file
                        @unlink($filePath);

                        Notification::make()
                            ->title('Import berhasil!')
                            ->body('Data sertifikat berhasil di-import dari CSV.')
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
