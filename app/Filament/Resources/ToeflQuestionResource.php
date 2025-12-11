<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToeflQuestionResource\Pages;
use App\Models\ToeflPackage;
use App\Models\ToeflQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ToeflQuestionResource extends Resource
{
    protected static ?string $model = ToeflQuestion::class;
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationGroup = 'TOEFL Online';
    protected static ?string $navigationLabel = 'Bank Soal';
    protected static ?string $modelLabel = 'Soal TOEFL';
    protected static ?string $pluralModelLabel = 'Bank Soal';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Soal')->schema([
                Forms\Components\Select::make('package_id')
                    ->label('Paket Soal')
                    ->options(ToeflPackage::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\Select::make('section')
                    ->label('Section')
                    ->options([
                        'listening' => 'Listening',
                        'structure' => 'Structure',
                        'reading' => 'Reading',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('question_number')
                    ->label('Nomor Soal')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->maxValue(100),
            ])->columns(3),

            Forms\Components\Section::make('Reading Passage')
                ->schema([
                    Forms\Components\Textarea::make('passage')
                        ->label('Teks Bacaan (untuk Reading)')
                        ->helperText('Isi hanya untuk soal Reading yang memerlukan teks bacaan')
                        ->rows(6),
                ])
                ->collapsed()
                ->collapsible(),

            Forms\Components\Section::make('Pertanyaan & Pilihan')->schema([
                Forms\Components\Textarea::make('question')
                    ->label('Pertanyaan')
                    ->required()
                    ->rows(3),
                Forms\Components\TextInput::make('option_a')
                    ->label('Pilihan A')
                    ->required()
                    ->maxLength(500),
                Forms\Components\TextInput::make('option_b')
                    ->label('Pilihan B')
                    ->required()
                    ->maxLength(500),
                Forms\Components\TextInput::make('option_c')
                    ->label('Pilihan C')
                    ->required()
                    ->maxLength(500),
                Forms\Components\TextInput::make('option_d')
                    ->label('Pilihan D')
                    ->required()
                    ->maxLength(500),
                Forms\Components\Select::make('correct_answer')
                    ->label('Jawaban Benar')
                    ->options([
                        'A' => 'A',
                        'B' => 'B',
                        'C' => 'C',
                        'D' => 'D',
                    ])
                    ->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('package.name')
                    ->label('Paket')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('section')
                    ->label('Section')
                    ->colors([
                        'primary' => 'listening',
                        'success' => 'structure',
                        'warning' => 'reading',
                    ]),
                Tables\Columns\TextColumn::make('question_number')
                    ->label('No.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('correct_answer')
                    ->label('Jawaban')
                    ->color('success'),
            ])
            ->defaultSort('package_id')
            ->filters([
                Tables\Filters\SelectFilter::make('package_id')
                    ->label('Paket')
                    ->options(ToeflPackage::pluck('name', 'id')),
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListToeflQuestions::route('/'),
            'create' => Pages\CreateToeflQuestion::route('/create'),
            'edit' => Pages\EditToeflQuestion::route('/{record}/edit'),
        ];
    }
}
