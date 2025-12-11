<?php

namespace App\Filament\Resources\ToeflQuestionResource\Pages;

use App\Filament\Resources\ToeflQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateToeflQuestion extends CreateRecord
{
    protected static string $resource = ToeflQuestionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
