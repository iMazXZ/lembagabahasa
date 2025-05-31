<?php

namespace App\Filament\Resources\PendaftaranGrupTesResource\Pages;

use App\Filament\Resources\PendaftaranGrupTesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendaftaranGrupTes extends EditRecord
{
    protected static string $resource = PendaftaranGrupTesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
