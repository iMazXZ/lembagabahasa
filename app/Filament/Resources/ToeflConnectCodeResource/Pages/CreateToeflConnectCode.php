<?php

namespace App\Filament\Resources\ToeflConnectCodeResource\Pages;

use App\Filament\Resources\ToeflConnectCodeResource;
use App\Models\ToeflConnectCode;
use Filament\Resources\Pages\CreateRecord;

class CreateToeflConnectCode extends CreateRecord
{
    protected static string $resource = ToeflConnectCodeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hash the plain code before saving
        if (isset($data['code_plain'])) {
            $data['code_hash'] = hash('sha256', $data['code_plain']);
            $data['code_hint'] = $data['code_hint'] ?? $data['code_plain'];
            unset($data['code_plain']);
        }

        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
