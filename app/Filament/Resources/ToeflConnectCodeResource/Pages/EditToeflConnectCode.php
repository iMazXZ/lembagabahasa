<?php

namespace App\Filament\Resources\ToeflConnectCodeResource\Pages;

use App\Filament\Resources\ToeflConnectCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditToeflConnectCode extends EditRecord
{
    protected static string $resource = ToeflConnectCodeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Only update hash if a new code is provided
        if (isset($data['code_plain']) && $data['code_plain'] !== '********') {
            $data['code_hash'] = hash('sha256', $data['code_plain']);
        }
        unset($data['code_plain']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
