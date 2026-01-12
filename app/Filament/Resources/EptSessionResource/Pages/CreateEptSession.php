<?php

namespace App\Filament\Resources\EptSessionResource\Pages;

use App\Filament\Resources\EptSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEptSession extends CreateRecord
{
    protected static string $resource = EptSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
