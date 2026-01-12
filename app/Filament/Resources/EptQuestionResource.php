<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptQuestionResource\Pages;
use App\Models\EptQuestion;
use App\Models\EptQuiz;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EptQuestionResource extends Resource
{
    protected static ?string $model = EptQuestion::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationGroup = 'EPT';
    protected static ?string $pluralLabel = 'Soal EPT';
    protected static ?string $modelLabel = 'Soal';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Soal')
                ->schema([
                    Forms\Components\Select::make('quiz_id')
                        ->label('Paket Soal')
                        ->relationship('quiz', 'name')
                        ->required()
                        ->searchable()
                        ->preload(),
                    
                    Forms\Components\Select::make('section')
                        ->label('Section')
                        ->options([
                            'listening' => 'Listening Comprehension',
                            'structure' => 'Structure & Written Expression',
                            'reading' => 'Reading Comprehension',
                        ])
                        ->required()
                        ->reactive(),
                    
                    Forms\Components\TextInput::make('order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(1)
                        ->required(),
                ])
                ->columns(3),
            
            Forms\Components\Section::make('Audio (Listening)')
                ->schema([
                    Forms\Components\FileUpload::make('audio_url')
                        ->label('File Audio')
                        ->disk('public')
                        ->directory('ept/audio')
                        ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav'])
                        ->maxSize(10240),
                ])
                ->visible(fn ($get) => $get('section') === 'listening')
                ->collapsed(),
            
            Forms\Components\Section::make('Passage (Reading)')
                ->schema([
                    Forms\Components\Textarea::make('passage')
                        ->label('Teks Passage')
                        ->rows(6)
                        ->helperText('Untuk soal reading yang berbagi passage, gunakan passage_group yang sama.'),
                    
                    Forms\Components\TextInput::make('passage_group')
                        ->label('Passage Group')
                        ->numeric()
                        ->helperText('Soal dengan group yang sama berbagi passage.'),
                ])
                ->visible(fn ($get) => $get('section') === 'reading')
                ->collapsed(),
            
            Forms\Components\Section::make('Pertanyaan & Pilihan')
                ->schema([
                    Forms\Components\Textarea::make('question')
                        ->label('Pertanyaan')
                        ->rows(3),
                    
                    Forms\Components\TextInput::make('option_a')
                        ->label('Pilihan A')
                        ->required(),
                    
                    Forms\Components\TextInput::make('option_b')
                        ->label('Pilihan B')
                        ->required(),
                    
                    Forms\Components\TextInput::make('option_c')
                        ->label('Pilihan C')
                        ->required(),
                    
                    Forms\Components\TextInput::make('option_d')
                        ->label('Pilihan D')
                        ->required(),
                    
                    Forms\Components\Select::make('correct_answer')
                        ->label('Jawaban Benar')
                        ->options([
                            'A' => 'A',
                            'B' => 'B',
                            'C' => 'C',
                            'D' => 'D',
                        ])
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quiz.name')
                    ->label('Paket')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('section')
                    ->label('Section')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'listening' => 'info',
                        'structure' => 'warning',
                        'reading' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->limit(50)
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('correct_answer')
                    ->label('Jawaban')
                    ->badge()
                    ->color('success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->label('Paket Soal')
                    ->relationship('quiz', 'name'),
                
                Tables\Filters\SelectFilter::make('section')
                    ->label('Section')
                    ->options([
                        'listening' => 'Listening',
                        'structure' => 'Structure',
                        'reading' => 'Reading',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptQuestions::route('/'),
            'create' => Pages\CreateEptQuestion::route('/create'),
            'edit' => Pages\EditEptQuestion::route('/{record}/edit'),
        ];
    }
}
