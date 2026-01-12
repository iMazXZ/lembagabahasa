<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptAttemptResource\Pages;
use App\Models\EptAttempt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;

class EptAttemptResource extends Resource
{
    protected static ?string $model = EptAttempt::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'EPT';
    protected static ?string $pluralLabel = 'Hasil Ujian';
    protected static ?string $modelLabel = 'Hasil';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Read-only, tidak perlu form untuk edit
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peserta')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('quiz.name')
                    ->label('Paket Soal')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('session.name')
                    ->label('Sesi')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('scaled_listening')
                    ->label('Listening')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('scaled_structure')
                    ->label('Structure')
                    ->badge()
                    ->color('warning'),
                
                Tables\Columns\TextColumn::make('scaled_reading')
                    ->label('Reading')
                    ->badge()
                    ->color('success'),
                
                Tables\Columns\TextColumn::make('total_score')
                    ->label('Total')
                    ->badge()
                    ->color(fn ($state) => $state >= 500 ? 'success' : ($state >= 400 ? 'warning' : 'danger'))
                    ->weight('bold'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->label('Paket Soal')
                    ->relationship('quiz', 'name'),
                
                Tables\Filters\SelectFilter::make('session_id')
                    ->label('Sesi')
                    ->relationship('session', 'name'),
                
                Tables\Filters\TernaryFilter::make('submitted')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Submit')
                    ->falseLabel('Belum Submit')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('submitted_at'),
                        false: fn ($query) => $query->whereNull('submitted_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('started_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Components\Section::make('Informasi Peserta')
                ->schema([
                    Components\TextEntry::make('user.name')
                        ->label('Nama'),
                    Components\TextEntry::make('user.email')
                        ->label('Email'),
                    Components\TextEntry::make('session.name')
                        ->label('Sesi'),
                ])
                ->columns(3),
            
            Components\Section::make('Waktu Ujian')
                ->schema([
                    Components\TextEntry::make('started_at')
                        ->label('Mulai')
                        ->dateTime('d M Y H:i:s'),
                    Components\TextEntry::make('submitted_at')
                        ->label('Selesai')
                        ->dateTime('d M Y H:i:s'),
                ])
                ->columns(2),
            
            Components\Section::make('Skor')
                ->schema([
                    Components\TextEntry::make('score_listening')
                        ->label('Listening (Raw)')
                        ->suffix(' benar'),
                    Components\TextEntry::make('scaled_listening')
                        ->label('Listening (Scaled)')
                        ->badge()
                        ->color('info'),
                    Components\TextEntry::make('score_structure')
                        ->label('Structure (Raw)')
                        ->suffix(' benar'),
                    Components\TextEntry::make('scaled_structure')
                        ->label('Structure (Scaled)')
                        ->badge()
                        ->color('warning'),
                    Components\TextEntry::make('score_reading')
                        ->label('Reading (Raw)')
                        ->suffix(' benar'),
                    Components\TextEntry::make('scaled_reading')
                        ->label('Reading (Scaled)')
                        ->badge()
                        ->color('success'),
                ])
                ->columns(2),
            
            Components\Section::make('Total')
                ->schema([
                    Components\TextEntry::make('total_score')
                        ->label('Total Skor')
                        ->size('lg')
                        ->weight('bold')
                        ->badge()
                        ->color(fn ($state) => $state >= 500 ? 'success' : ($state >= 400 ? 'warning' : 'danger')),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptAttempts::route('/'),
            'view' => Pages\ViewEptAttempt::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Attempt dibuat otomatis saat peserta mulai ujian
    }
}
