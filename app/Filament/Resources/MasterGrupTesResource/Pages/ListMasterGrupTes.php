<?php

namespace App\Filament\Resources\MasterGrupTesResource\Pages;

use App\Filament\Resources\MasterGrupTesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterGrupTes extends ListRecords
{
    protected static string $resource = MasterGrupTesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                -> label('Buat Grup Tes Baru'),
        ];
    }
}
