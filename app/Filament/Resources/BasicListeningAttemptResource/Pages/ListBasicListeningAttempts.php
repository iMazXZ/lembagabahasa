<?php

namespace App\Filament\Resources\BasicListeningAttemptResource\Pages;

use App\Filament\Resources\BasicListeningAttemptResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions as Actions;

class ListBasicListeningAttempts extends ListRecords
{
    protected static string $resource = BasicListeningAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('dashboard')
                ->label('Kembali ke Dasbor')
                ->url(route('filament.admin.pages.2'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            Actions\CreateAction::make(),
        ];
    }
}
