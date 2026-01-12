<?php

namespace App\Filament\Resources\EptQuestionResource\Pages;

use App\Filament\Resources\EptQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEptQuestion extends CreateRecord
{
    protected static string $resource = EptQuestionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
