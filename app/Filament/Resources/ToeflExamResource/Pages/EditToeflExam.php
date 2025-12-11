<?php

namespace App\Filament\Resources\ToeflExamResource\Pages;

use App\Filament\Resources\ToeflExamResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditToeflExam extends EditRecord
{
    protected static string $resource = ToeflExamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
