<?php

namespace App\Filament\Resources\BasicListeningConnectCodeResource\Pages;

use App\Filament\Resources\BasicListeningConnectCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBasicListeningConnectCodes extends ListRecords
{
    protected static string $resource = BasicListeningConnectCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
