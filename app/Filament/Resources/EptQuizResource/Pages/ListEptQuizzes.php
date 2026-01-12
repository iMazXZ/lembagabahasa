<?php

namespace App\Filament\Resources\EptQuizResource\Pages;

use App\Filament\Resources\EptQuizResource;
use Filament\Resources\Pages\ListRecords;

class ListEptQuizzes extends ListRecords
{
    protected static string $resource = EptQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
