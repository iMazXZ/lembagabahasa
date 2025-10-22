<?php

namespace App\Filament\Resources\BasicListeningAttemptResource\Pages;

use App\Filament\Resources\BasicListeningAttemptResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBasicListeningAttempt extends ViewRecord
{
    protected static string $resource = BasicListeningAttemptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export Jawaban')
                ->action('exportAnswers')
                ->visible(fn()=>true),
        ];
    }

    public function exportAnswers()
    {
        // di sini kamu bisa implementasi export CSV/JSON jika mau.
        $this->notify('success','Implement export sesuai kebutuhan.');
    }
}
