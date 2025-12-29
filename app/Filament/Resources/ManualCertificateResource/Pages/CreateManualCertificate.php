<?php

namespace App\Filament\Resources\ManualCertificateResource\Pages;

use App\Filament\Resources\ManualCertificateResource;
use App\Models\CertificateCategory;
use Filament\Resources\Pages\CreateRecord;

class CreateManualCertificate extends CreateRecord
{
    protected static string $resource = ManualCertificateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate certificate number from category
        $category = CertificateCategory::find($data['category_id']);
        
        if ($category) {
            $data['certificate_number'] = $category->generateCertificateNumber($data['semester'] ?? null);
        }

        return $data;
    }
}
