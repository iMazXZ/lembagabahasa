<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptQuizResource\Pages;
use App\Models\EptQuiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EptQuizResource extends Resource
{
    protected static ?string $model = EptQuiz::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'EPT';
    protected static ?string $pluralLabel = 'Paket Soal EPT';
    protected static ?string $modelLabel = 'Paket Soal';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Paket')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Paket Soal')
                        ->placeholder('EPT Januari 2026')
                        ->required()
                        ->maxLength(255),
                    
                    Forms\Components\Textarea::make('description')
                        ->label('Deskripsi')
                        ->placeholder('Deskripsi paket soal...')
                        ->rows(3),
                    
                    Forms\Components\Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])
                ->columns(1),
            
            Forms\Components\Section::make('Durasi per Section (menit)')
                ->schema([
                    Forms\Components\TextInput::make('listening_duration')
                        ->label('Listening')
                        ->numeric()
                        ->default(35)
                        ->suffix('menit')
                        ->required(),
                    
                    Forms\Components\TextInput::make('structure_duration')
                        ->label('Structure')
                        ->numeric()
                        ->default(25)
                        ->suffix('menit')
                        ->required(),
                    
                    Forms\Components\TextInput::make('reading_duration')
                        ->label('Reading')
                        ->numeric()
                        ->default(55)
                        ->suffix('menit')
                        ->required(),
                ])
                ->columns(3),
            
            Forms\Components\Section::make('Jumlah Soal per Section')
                ->schema([
                    Forms\Components\TextInput::make('listening_count')
                        ->label('Listening')
                        ->numeric()
                        ->default(50)
                        ->suffix('soal')
                        ->required(),
                    
                    Forms\Components\TextInput::make('structure_count')
                        ->label('Structure')
                        ->numeric()
                        ->default(40)
                        ->suffix('soal')
                        ->required(),
                    
                    Forms\Components\TextInput::make('reading_count')
                        ->label('Reading')
                        ->numeric()
                        ->default(50)
                        ->suffix('soal')
                        ->required(),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_questions')
                    ->label('Total Soal')
                    ->getStateUsing(fn ($record) => 
                        $record->listening_count + $record->structure_count + $record->reading_count
                    )
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('total_duration')
                    ->label('Total Durasi')
                    ->getStateUsing(fn ($record) => 
                        ($record->listening_duration + $record->structure_duration + $record->reading_duration) . ' menit'
                    ),
                
                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Soal Dibuat')
                    ->counts('questions')
                    ->badge()
                    ->color(fn ($state, $record) => 
                        $state >= ($record->listening_count + $record->structure_count + $record->reading_count) 
                            ? 'success' 
                            : 'warning'
                    ),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // Will add QuestionsRelationManager later
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptQuizzes::route('/'),
            'create' => Pages\CreateEptQuiz::route('/create'),
            'edit' => Pages\EditEptQuiz::route('/{record}/edit'),
            'view' => Pages\ViewEptQuiz::route('/{record}'),
        ];
    }
}
