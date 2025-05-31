<?php

namespace App\Filament\Resources\DataNilaiTesResource\Pages;

use App\Filament\Resources\DataNilaiTesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataNilaiTes extends EditRecord
{
    protected static string $resource = DataNilaiTesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
