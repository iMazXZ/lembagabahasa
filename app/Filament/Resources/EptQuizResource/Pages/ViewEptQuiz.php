<?php

namespace App\Filament\Resources\EptQuizResource\Pages;

use App\Filament\Resources\EptQuizResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class ViewEptQuiz extends ViewRecord
{
    protected static string $resource = EptQuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Components\Section::make('Informasi Paket')
                ->schema([
                    Components\TextEntry::make('name')
                        ->label('Nama Paket'),
                    Components\TextEntry::make('description')
                        ->label('Deskripsi'),
                    Components\IconEntry::make('is_active')
                        ->label('Status')
                        ->boolean(),
                ])
                ->columns(3),
            
            Components\Section::make('Durasi per Section')
                ->schema([
                    Components\TextEntry::make('listening_duration')
                        ->label('Listening')
                        ->suffix(' menit'),
                    Components\TextEntry::make('structure_duration')
                        ->label('Structure')
                        ->suffix(' menit'),
                    Components\TextEntry::make('reading_duration')
                        ->label('Reading')
                        ->suffix(' menit'),
                ])
                ->columns(3),
            
            Components\Section::make('Jumlah Soal')
                ->schema([
                    Components\TextEntry::make('listening_count')
                        ->label('Listening')
                        ->suffix(' soal'),
                    Components\TextEntry::make('structure_count')
                        ->label('Structure')
                        ->suffix(' soal'),
                    Components\TextEntry::make('reading_count')
                        ->label('Reading')
                        ->suffix(' soal'),
                ])
                ->columns(3),
        ]);
    }
}
