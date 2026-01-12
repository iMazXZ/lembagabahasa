<?php

namespace App\Filament\Resources\EptQuestionResource\Pages;

use App\Filament\Resources\EptQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEptQuestion extends EditRecord
{
    protected static string $resource = EptQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
