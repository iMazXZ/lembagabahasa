<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BasicListeningAttemptResource\Pages;
use App\Models\BasicListeningAttempt;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use App\Models\Prody;
use Illuminate\Database\Eloquent\Builder;

class BasicListeningAttemptResource extends Resource
{
    protected static ?string $model = BasicListeningAttempt::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Basic Listening';
    protected static ?string $pluralLabel = 'Attempts (Hasil Kuis)';
    protected static ?string $modelLabel = 'Attempt';

    public static function form(Form $form): Form
    {
        return $form->schema([]); // read-only
    }

    public static function table(Table $table): Table
    {
        return $table
            // 🆕 TAMBAH EAGER LOADING DI SINI
            ->query(BasicListeningAttempt::with([
                'user.prody', 
                'session', 
                'quiz.questions', // 🆕 DIBUTUHKAN UNTK quiz_type
                'connectCode'     // 🆕 DIBUTUHKAN UNTK kode_hint
            ]))
            
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peserta')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.srn')
                    ->label('NPM')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.prody.name')
                    ->label('Prodi')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('session.number')
                    ->label('Pert.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quiz_type')
                    ->label('Tipe')
                    ->state(function($record) {
                        $firstQuestion = $record->quiz->questions->first();
                        return $firstQuestion ? $firstQuestion->type : 'unknown';
                    })
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'fib_paragraph' => 'FIB',
                        'multiple_choice' => 'MC', 
                        default => $state
                    })
                    ->color(fn ($state) => match($state) {
                        'fib_paragraph' => 'warning',
                        'multiple_choice' => 'success',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('session.title')
                    ->label('Judul')
                    ->limit(30),

               Tables\Columns\TextColumn::make('score')
                    ->label('Skor')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state === null          => 'gray',    // Belum dinilai
                        $state === 0             => 'danger',  // 🆕 EXPLICIT UNTUK SKOR 0
                        $state >= 80             => 'success',
                        $state >= 60             => 'warning',
                        default                  => 'danger',  // 1-59
                    })
                    ->formatStateUsing(fn ($state) => $state === null ? '–' : $state),

                // 🆕 PERBAIKI KOLOM INI - PAKAI RELASI LANGSUNG
                Tables\Columns\TextColumn::make('connectCode.code_hint')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submit')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('session_id')->relationship('session','title')->label('Session'),
                Tables\Filters\Filter::make('submitted_only')->label('Sudah submit')
                    ->query(fn($q) => $q->whereNotNull('submitted_at')),
                Tables\Filters\SelectFilter::make('prody_id')
                    ->label('Prodi')
                    ->options(fn () => Prody::query()->orderBy('name')->pluck('name','id')->all())
                    ->query(function (Builder $query, array $data) {
                        if (!($data['value'] ?? null)) return $query;
                        return $query->whereHas('user', function (Builder $uq) use ($data) {
                            $uq->where('prody_id', $data['value']); // asumsi FK = prody_id
                        });
                    }),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at','desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Ringkasan')->schema([
                TextEntry::make('user.name')->label('Peserta'),
                TextEntry::make('session.title')->label('Session'),
                TextEntry::make('score')->label('Skor'),
                TextEntry::make('started_at')->dateTime('d M Y H:i')->label('Mulai'),
                TextEntry::make('submitted_at')->dateTime('d M Y H:i')->label('Submit'),
                // 🆕 TAMBAH TIPE QUIZ
                TextEntry::make('quiz_type')
                    ->label('Tipe Quiz')
                    ->state(function($record) {
                        $firstQuestion = $record->quiz->questions->first();
                        return $firstQuestion ? $firstQuestion->type : 'unknown';
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'fib_paragraph' => 'warning',
                        'multiple_choice' => 'success',
                        default => 'gray'
                    }),
            ])->columns(3),

            Section::make('Jawaban')->schema([
                TextEntry::make('answers_list')->label('Detail')
                    ->state(function($record){
                        $record->loadMissing(['answers','quiz.questions']);
                        $rows = [];
                        
                        foreach ($record->quiz->questions as $i=>$q) {
                            $ans = $record->answers->firstWhere('question_id', $q->id);
                            
                            // 🆕 HANDLE BERDASARKAN TIPE - PAKAI STATIC METHOD
                            if ($q->type === 'fib_paragraph') {
                                // FORMAT FIB
                                $rows[] = self::formatFibAnswer($q, $record->answers, $i+1);
                            } else {
                                // FORMAT MULTIPLE CHOICE
                                $rows[] = self::formatMcAnswer($q, $ans, $i+1);
                            }
                        }
                        return implode("\n", $rows);
                    })
                    ->columnSpanFull()
                    ->formatStateUsing(fn($state) => nl2br(e($state)))
                    ->html(),
            ])->collapsible(),
        ]);
    }

    // 🆕 METHOD STATIC: Format FIB Answer
    private static function formatFibAnswer($question, $allAnswers, $number)
    {
        $paragraph = $question->paragraph_text ?? 'No paragraph';
        $blankCount = is_array($question->fib_placeholders) ? count($question->fib_placeholders) : 0;
        
        $result = "({$number}) FIB PARAGRAPH - {$blankCount} blanks\n";
        $result .= "Paragraf: {$paragraph}\n\n";
        
        // Tampilkan jawaban per blank
        $fibAnswers = $allAnswers->where('question_id', $question->id);
        
        if ($fibAnswers->count() > 0) {
            $result .= "Jawaban yang diberikan:\n";
            foreach ($fibAnswers as $fibAns) {
                $result .= "Blank {$fibAns->blank_index}: \"{$fibAns->answer}\" " . 
                        ($fibAns->is_correct ? '✓' : '✗') . "\n";
            }
        } else {
            $result .= "Tidak ada jawaban\n";
        }
        
        // Tampilkan kunci jawaban jika ada
        if (!empty($question->fib_answer_key)) {
            $result .= "\nKunci Jawaban:\n";
            foreach ($question->fib_answer_key as $blankIndex => $key) {
                $keyStr = is_array($key) ? implode(' / ', $key) : $key;
                $result .= "Blank {$blankIndex}: {$keyStr}\n";
            }
        }
        
        return $result . "\n" . str_repeat('-', 50) . "\n";
    }

    // 🆕 METHOD STATIC: Format Multiple Choice Answer  
    private static function formatMcAnswer($question, $answer, $number)
    {
        $chosen = $answer?->answer ?? '-';
        $mark = $answer?->is_correct ? '✓' : '✗';
        
        return sprintf("(%02d) [%s] %s\nA. %s\nB. %s\nC. %s\nD. %s\nJawaban: %s | Kunci: %s\n%s\n",
            $number,
            $mark,
            $question->question ?? '-',
            $question->option_a ?? '-', 
            $question->option_b ?? '-', 
            $question->option_c ?? '-', 
            $question->option_d ?? '-',
            $chosen, 
            $question->correct ?? '-',
            str_repeat('-', 50)
        );
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBasicListeningAttempts::route('/'),
            'view' => Pages\ViewBasicListeningAttempt::route('/{record}'),
        ];
    }
}
