<?php

namespace App\Filament\Resources\EptQuizResource\Pages;

use App\Filament\Resources\EptQuizResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEptQuiz extends EditRecord
{
    protected static string $resource = EptQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
