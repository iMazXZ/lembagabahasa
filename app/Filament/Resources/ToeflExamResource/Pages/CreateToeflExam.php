<?php

namespace App\Filament\Resources\ToeflExamResource\Pages;

use App\Filament\Resources\ToeflExamResource;
use Filament\Resources\Pages\CreateRecord;

class CreateToeflExam extends CreateRecord
{
    protected static string $resource = ToeflExamResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
