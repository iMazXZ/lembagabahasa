<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToeflAttemptResource\Pages;
use App\Models\ToeflAttempt;
use App\Models\ToeflExam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ToeflAttemptResource extends Resource
{
    protected static ?string $model = ToeflAttempt::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'TOEFL Online';
    protected static ?string $navigationLabel = 'Hasil Ujian';
    protected static ?string $modelLabel = 'Hasil Ujian';
    protected static ?string $pluralModelLabel = 'Hasil Ujian';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Peserta')->schema([
                Forms\Components\Placeholder::make('user_name')
                    ->label('Nama')
                    ->content(fn ($record) => $record->user->name ?? '-'),
                Forms\Components\Placeholder::make('user_srn')
                    ->label('NPM')
                    ->content(fn ($record) => $record->user->srn ?? '-'),
                Forms\Components\Placeholder::make('exam_name')
                    ->label('Ujian')
                    ->content(fn ($record) => $record->exam->name ?? '-'),
            ])->columns(3),

            Forms\Components\Section::make('Skor')->schema([
                Forms\Components\Placeholder::make('listening_display')
                    ->label('Listening')
                    ->content(fn ($record) => $record->listening_correct . ' benar → ' . ($record->listening_score ?? '-')),
                Forms\Components\Placeholder::make('structure_display')
                    ->label('Structure')
                    ->content(fn ($record) => $record->structure_correct . ' benar → ' . ($record->structure_score ?? '-')),
                Forms\Components\Placeholder::make('reading_display')
                    ->label('Reading')
                    ->content(fn ($record) => $record->reading_correct . ' benar → ' . ($record->reading_score ?? '-')),
                Forms\Components\Placeholder::make('total_display')
                    ->label('Total TOEFL')
                    ->content(fn ($record) => $record->total_score ?? '-'),
            ])->columns(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.srn')
                    ->label('NPM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('exam.name')
                    ->label('Ujian')
                    ->sortable(),
                Tables\Columns\TextColumn::make('listening_score')
                    ->label('L')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('structure_score')
                    ->label('S')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('reading_score')
                    ->label('R')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_score')
                    ->label('Total')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Selesai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('exam_id')
                    ->label('Ujian')
                    ->options(ToeflExam::pluck('name', 'id')),
                Tables\Filters\Filter::make('submitted')
                    ->label('Hanya yang selesai')
                    ->query(fn ($query) => $query->whereNotNull('submitted_at'))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListToeflAttempts::route('/'),
            'view' => Pages\ViewToeflAttempt::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
