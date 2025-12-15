<?php

namespace App\Filament\Resources\EptGroupResource\Pages;

use App\Filament\Resources\EptGroupResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditEptGroup extends EditRecord
{
    protected static string $resource = EptGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
