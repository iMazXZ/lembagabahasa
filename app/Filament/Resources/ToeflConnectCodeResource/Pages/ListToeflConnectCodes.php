<?php

namespace App\Filament\Resources\ToeflConnectCodeResource\Pages;

use App\Filament\Resources\ToeflConnectCodeResource;
use Filament\Resources\Pages\ListRecords;

class ListToeflConnectCodes extends ListRecords
{
    protected static string $resource = ToeflConnectCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
