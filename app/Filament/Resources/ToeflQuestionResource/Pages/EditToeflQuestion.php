<?php

namespace App\Filament\Resources\ToeflQuestionResource\Pages;

use App\Filament\Resources\ToeflQuestionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditToeflQuestion extends EditRecord
{
    protected static string $resource = ToeflQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
