<?php

namespace App\Filament\Resources\PendaftaranGrupTesResource\Pages;

use App\Filament\Resources\PendaftaranGrupTesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPendaftaranGrupTes extends ListRecords
{
    protected static string $resource = PendaftaranGrupTesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                -> label('Masukan ke Grup Tes'),
        ];
    }
}
