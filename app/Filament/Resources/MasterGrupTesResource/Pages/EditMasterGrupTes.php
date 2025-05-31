<?php

namespace App\Filament\Resources\MasterGrupTesResource\Pages;

use App\Filament\Resources\MasterGrupTesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterGrupTes extends EditRecord
{
    protected static string $resource = MasterGrupTesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
