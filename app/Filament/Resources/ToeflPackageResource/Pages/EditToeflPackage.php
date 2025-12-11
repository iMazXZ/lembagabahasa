<?php

namespace App\Filament\Resources\ToeflPackageResource\Pages;

use App\Filament\Resources\ToeflPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditToeflPackage extends EditRecord
{
    protected static string $resource = ToeflPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
