<?php

namespace App\Filament\Resources\EptQuizResource\Pages;

use App\Filament\Resources\EptQuizResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEptQuiz extends CreateRecord
{
    protected static string $resource = EptQuizResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
