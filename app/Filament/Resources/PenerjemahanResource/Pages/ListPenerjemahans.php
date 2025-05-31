<?php

namespace App\Filament\Resources\PenerjemahanResource\Pages;

use App\Filament\Resources\PenerjemahanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPenerjemahans extends ListRecords
{
    protected static string $resource = PenerjemahanResource::class;

    protected function getHeaderActions(): array
    {
        // Hanya tampilkan tombol Create jika BUKAN penerjemah
        if (!auth()->user()?->hasRole('penerjemah')) {
            return [
                Actions\CreateAction::make()
                    -> label('Permintaan Baru'),
            ];
        }

        return [];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Filter hanya data yang ditugaskan ke penerjemah ini
        if (auth()->user()->hasRole('Penerjemah')) {
            return $query->where('translator_id', auth()->id());
        }

        return $query;
    }
}