<?php

namespace App\Filament\Resources\EptQuestionResource\Pages;

use App\Filament\Resources\EptQuestionResource;
use Filament\Resources\Pages\ListRecords;

class ListEptQuestions extends ListRecords
{
    protected static string $resource = EptQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
