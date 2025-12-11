<?php

namespace App\Filament\Resources\ToeflPackageResource\Pages;

use App\Filament\Resources\ToeflPackageResource;
use Filament\Resources\Pages\ListRecords;

class ListToeflPackages extends ListRecords
{
    protected static string $resource = ToeflPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
