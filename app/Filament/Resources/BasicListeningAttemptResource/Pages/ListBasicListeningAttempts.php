<?php

namespace App\Filament\Resources\BasicListeningAttemptResource\Pages;

use App\Filament\Resources\BasicListeningAttemptResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBasicListeningAttempts extends ListRecords
{
    protected static string $resource = BasicListeningAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
