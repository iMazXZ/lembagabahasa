<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BasicListeningQuestionResource\Pages;
use App\Models\BasicListeningQuestion;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class BasicListeningQuestionResource extends Resource
{
    protected static ?string $model = BasicListeningQuestion::class;
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $navigationGroup = 'Basic Listening';
    protected static ?string $pluralLabel = 'Buat Soal dalam Paket';
    protected static ?string $navigationParentItem = 'Meeting';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Soal dalam Paket';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Target Paket')
                ->schema([
                    Forms\Components\Select::make('quiz_id')
                        ->relationship('quiz','title')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->label('Pilih Paket Soal'),
                ])->columns(2),

            Forms\Components\Section::make('Tipe Soal')
                ->schema([
                    Forms\Components\Select::make('type')
                        ->label('Tipe Soal')
                        ->default('multiple_choice')
                        ->options([
                            'multiple_choice' => 'Multiple Choice',
                            'short_answer'    => 'Short Answer',
                            'fib_paragraph'   => 'Fill in the Blank (Paragraph)',
                        ])
                        ->native(false)
                        ->required()
                        ->reactive(),
                ])->columns(2),

            // =========================
            // BLOK: Multiple Choice
            // =========================
            Forms\Components\Section::make('Multiple Choice')
                ->visible(fn (Get $get) => ($get('type') ?? 'multiple_choice') !== 'fib_paragraph')
                ->schema([
                    Forms\Components\Textarea::make('question')
                        ->label('Pertanyaan')
                        ->rows(3)
                        ->required(fn (Get $get) => ($get('type') ?? 'multiple_choice') !== 'fib_paragraph')
                        ->dehydrated(fn (Get $get) => ($get('type') ?? 'multiple_choice') !== 'fib_paragraph'),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Textarea::make('option_a')
                            ->label('A')
                            ->rows(2)
                            ->required(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice')
                            ->dehydrated(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice'),

                        Forms\Components\Textarea::make('option_b')
                            ->label('B')
                            ->rows(2)
                            ->required(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice')
                            ->dehydrated(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice'),

                        Forms\Components\Textarea::make('option_c')
                            ->label('C')
                            ->rows(2)
                            ->required(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice')
                            ->dehydrated(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice'),

                        Forms\Components\Textarea::make('option_d')
                            ->label('D')
                            ->rows(2)
                            ->required(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice')
                            ->dehydrated(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice'),
                    ]),

                    Forms\Components\Select::make('correct')
                        ->label('Kunci')
                        ->options(['A'=>'A','B'=>'B','C'=>'C','D'=>'D'])
                        ->native(false)
                        ->required(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice')
                        ->dehydrated(fn (Get $get) => ($get('type') ?? 'multiple_choice') === 'multiple_choice'),

                    Forms\Components\TextInput::make('order')
                        ->label('Urutan')
                        ->numeric()
                        ->default(0),
                ])->columns(2),

            // =========================
            // BLOK: FIB Paragraph
            // =========================
            Forms\Components\Section::make('Fill in the Blank (Paragraph)')
                ->visible(fn (Get $get) => $get('type') === 'fib_paragraph')
                ->schema([
                    Forms\Components\Textarea::make('paragraph_text')
                        ->label('Paragraph (gunakan [[1]], [[2]], [[3]], ... untuk blank)')
                        ->rows(8)
                        ->required(fn (Get $get) => $get('type') === 'fib_paragraph')
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set) {
                            preg_match_all('/\[\[(\d+)\]\]/', $state ?? '', $m);
                            $placeholders = array_values(array_unique($m[1] ?? []));
                            $set('fib_placeholders', $placeholders);
                        })
                        ->helperText('Contoh: "The capital of [[1]] is famous for its [[2]] monuments."'),

                    // Simpan hasil deteksi placeholder agar ikut terdehidrasi
                    Forms\Components\Hidden::make('fib_placeholders'),

                    Forms\Components\KeyValue::make('fib_answer_key')
                        ->label('Answer Key per Blank')
                        ->helperText('Nilai bisa: "apple", ["color","colour"], atau {"regex":"^colou?r$"}')
                        ->required(fn (Get $get) => $get('type') === 'fib_paragraph')
                        ->keyLabel('Index Blank (mis. 1,2,3)')
                        ->valueLabel('Kunci / Daftar / Regex'),

                    Forms\Components\KeyValue::make('fib_weights')
                        ->label('Bobot per Blank (opsional)')
                        ->keyLabel('Index Blank')
                        ->valueLabel('Bobot (angka)'),

                    Forms\Components\KeyValue::make('fib_scoring')
                        ->label('Pengaturan Penilaian')
                        ->default([
                            'mode' => 'exact',          // (disiapkan untuk pengembangan ke depan)
                            'case_sensitive' => false,
                            'allow_trim' => true,
                            'strip_punctuation' => true,
                        ])
                        ->keyLabel('Kunci')
                        ->valueLabel('Nilai')
                        ->helperText('Default: exact match, case-insensitive, trim & hapus tanda baca'),
                ])->columns(2),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('quiz.session.number')
                    ->label('S#')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quiz.title')
                    ->label('Nama Paket Soal')
                    ->limit(20)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'fib_paragraph' => 'FIB Paragraph',
                        'short_answer'  => 'Short Answer',
                        default         => 'Multiple Choice',
                    })
                    ->color(fn ($state) => match ($state) {
                        'fib_paragraph' => 'warning',
                        'short_answer'  => 'info',
                        default         => 'success',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('question')
                    ->label('Pertanyaan')
                    ->limit(50)
                    ->searchable()
                    ->toggleable()
                    ->visible(fn ($record) => ($record?->type ?? 'multiple_choice') !== 'fib_paragraph'),
                
                TextColumn::make('paragraph_text')
                    ->label('Pertanyaan')
                    ->limit(60)
                    ->searchable()
                    ->toggleable()
                    ->visible(fn ($record) => ($record?->type ?? '') === 'fib_paragraph')
                    ->formatStateUsing(function ($state, $record) {
                        $blanks = is_array($record->fib_placeholders) ? count($record->fib_placeholders) : 0;
                        return trim(($state ?? '')) . ($blanks ? "  —  {$blanks} blank" . ($blanks > 1 ? 's' : '') : '');
                    }),

                Tables\Columns\TextColumn::make('correct')
                    ->label('Kunci')
                    ->badge()
                    ->color('primary')
                    ->visible(fn ($record) => ($record?->type ?? 'multiple_choice') === 'multiple_choice'),

                TextColumn::make('fib_summary')
                    ->label('Kunci')
                    ->badge()
                    ->color('warning')
                    ->visible(fn ($record) => ($record?->type ?? '') === 'fib_paragraph')
                    ->getStateUsing(fn ($record) => is_array($record->fib_answer_key) ? (count($record->fib_answer_key) . ' kunci') : '–'),

                Tables\Columns\TextColumn::make('order')
                    ->label('Urut')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => (int)$state > 0 ? $state : '–')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('quiz_id')
                    ->relationship('quiz','title')
                    ->label('Quiz'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe Soal')
                    ->options([
                        'multiple_choice' => 'Multiple Choice',
                        'short_answer'    => 'Short Answer',
                        'fib_paragraph'   => 'FIB Paragraph',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('quiz_id');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBasicListeningQuestions::route('/'),
            'create' => Pages\CreateBasicListeningQuestion::route('/create'),
            'edit'   => Pages\EditBasicListeningQuestion::route('/{record}/edit'),
        ];
    }
}
