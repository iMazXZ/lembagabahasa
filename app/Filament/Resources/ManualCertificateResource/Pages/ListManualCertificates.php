<?php

namespace App\Filament\Resources\ManualCertificateResource\Pages;

use App\Filament\Resources\ManualCertificateResource;
use App\Imports\ManualCertificateImport;
use App\Models\CertificateCategory;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListManualCertificates extends ListRecords
{
    protected static string $resource = ManualCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importCsv')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('File CSV')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', '.csv'])
                        ->required()
                        ->disk('local')
                        ->directory('temp-imports')
                        ->helperText('Upload file CSV dengan kolom: Names, SRN, Listening Ave, Speaking Ave, Reading Ave, Writing Ave, Phonetics Ave, Grammar Ave, Vocabulary Ave'),

                    Forms\Components\Select::make('category_id')
                        ->label('Kategori Sertifikat')
                        ->options(CertificateCategory::where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn(Forms\Set $set) => $set('semester', null)),

                    Forms\Components\Select::make('semester')
                        ->label('Semester')
                        ->options(function (Get $get) {
                            $categoryId = $get('category_id');
                            if (!$categoryId) return [];
                            
                            $category = CertificateCategory::find($categoryId);
                            return $category?->getSemesterOptions() ?? [];
                        })
                        ->visible(function (Get $get) {
                            $categoryId = $get('category_id');
                            if (!$categoryId) return false;
                            
                            $category = CertificateCategory::find($categoryId);
                            return !empty($category?->semesters);
                        }),

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
                            semester: $data['semester'] ?? null,
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
}
