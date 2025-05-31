<?php

namespace App\Filament\Resources\DataNilaiTesResource\Pages;

use App\Filament\Resources\DataNilaiTesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataNilaiTes extends ListRecords
{
    protected static string $resource = DataNilaiTesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                -> label('Masukan Nilai'),
        ];
    }
}
