<?php

namespace App\Filament\Resources\BasicListeningAttemptResource\Pages;

use App\Filament\Resources\BasicListeningAttemptResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBasicListeningAttempt extends EditRecord
{
    protected static string $resource = BasicListeningAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
