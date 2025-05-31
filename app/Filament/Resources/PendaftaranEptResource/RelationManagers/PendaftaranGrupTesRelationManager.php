<?php

namespace App\Filament\Resources\PendaftaranEptResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PendaftaranGrupTesRelationManager extends RelationManager
{
    protected static string $relationship = 'pendaftaranGrupTes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('masterGrupTes.group_number')->label('Grup'),
                Tables\Columns\TextColumn::make('masterGrupTes.tanggal_tes')->label('Tanggal Tes')->dateTime('d M Y H:i'),
                Tables\Columns\TextColumn::make('masterGrupTes.ruangan_tes')->label('Ruangan'),
            ]);
    }
}