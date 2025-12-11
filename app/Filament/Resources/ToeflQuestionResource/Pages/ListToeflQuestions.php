<?php

namespace App\Filament\Resources\ToeflQuestionResource\Pages;

use App\Filament\Resources\ToeflQuestionResource;
use Filament\Resources\Pages\ListRecords;

class ListToeflQuestions extends ListRecords
{
    protected static string $resource = ToeflQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
