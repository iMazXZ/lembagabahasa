<?php

namespace App\Filament\Resources\ToeflPackageResource\Pages;

use App\Filament\Resources\ToeflPackageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateToeflPackage extends CreateRecord
{
    protected static string $resource = ToeflPackageResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
