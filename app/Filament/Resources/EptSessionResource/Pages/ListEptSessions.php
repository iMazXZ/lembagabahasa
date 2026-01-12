<?php

namespace App\Filament\Resources\EptSessionResource\Pages;

use App\Filament\Resources\EptSessionResource;
use Filament\Resources\Pages\ListRecords;

class ListEptSessions extends ListRecords
{
    protected static string $resource = EptSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
