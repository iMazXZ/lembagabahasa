<?php

namespace App\Filament\Resources\ToeflExamResource\Pages;

use App\Filament\Resources\ToeflExamResource;
use Filament\Resources\Pages\ListRecords;

class ListToeflExams extends ListRecords
{
    protected static string $resource = ToeflExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
