<?php

namespace App\Filament\Resources\DataNilaiTesResource\Pages;

use App\Filament\Resources\DataNilaiTesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDataNilaiTes extends CreateRecord
{
    protected static string $resource = DataNilaiTesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
