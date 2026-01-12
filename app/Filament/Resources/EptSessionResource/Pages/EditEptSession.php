<?php

namespace App\Filament\Resources\EptSessionResource\Pages;

use App\Filament\Resources\EptSessionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEptSession extends EditRecord
{
    protected static string $resource = EptSessionResource::class;

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
