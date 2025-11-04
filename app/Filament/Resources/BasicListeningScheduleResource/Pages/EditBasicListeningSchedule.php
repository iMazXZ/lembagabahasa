<?php

namespace App\Filament\Resources\BasicListeningScheduleResource\Pages;

use App\Filament\Resources\BasicListeningScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBasicListeningSchedule extends EditRecord
{
    protected static string $resource = BasicListeningScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
