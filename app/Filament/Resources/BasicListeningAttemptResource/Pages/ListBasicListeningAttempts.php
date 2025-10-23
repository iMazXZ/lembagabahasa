<?php

namespace App\Filament\Resources\BasicListeningAttemptResource\Pages;

use App\Filament\Resources\BasicListeningAttemptResource;
use Filament\Resources\Pages\ListRecords;

class ListBasicListeningAttempts extends ListRecords
{
    protected static string $resource = BasicListeningAttemptResource::class;

    // Attempt read-only → tidak ada CreateAction
    protected function getHeaderActions(): array
    {
        return [];
    }
}
