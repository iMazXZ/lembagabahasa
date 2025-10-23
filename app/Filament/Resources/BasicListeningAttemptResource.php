<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BasicListeningAttemptResource\Pages;
use App\Models\BasicListeningAttempt;
use App\Models\Prody;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
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
            // ⛔️ JANGAN set ->query() di sini agar getEloquentQuery() tetap aktif
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
                    ->state(function ($record) {
                        $firstQuestion = $record->quiz->questions->first();
                        return $firstQuestion ? $firstQuestion->type : 'unknown';
                    })
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'fib_paragraph'   => 'FIB',
                        'multiple_choice' => 'MC',
                        default           => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'fib_paragraph'   => 'warning',
                        'multiple_choice' => 'success',
                        default           => 'gray',
                    }),

                Tables\Columns\TextColumn::make('session.title')
                    ->label('Judul')
                    ->limit(30),

                Tables\Columns\TextColumn::make('score')
                    ->label('Skor')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state === 0    => 'danger',
                        $state >= 80    => 'success',
                        $state >= 60    => 'warning',
                        default         => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state === null ? '–' : $state),

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
                Tables\Filters\SelectFilter::make('session_id')
                    ->relationship('session', 'title')
                    ->label('Session'),

                Tables\Filters\Filter::make('submitted_only')
                    ->label('Sudah submit')
                    ->query(fn ($q) => $q->whereNotNull('submitted_at')),

                // ✅ Satu filter Prodi saja, opsi menyesuaikan role
                Tables\Filters\SelectFilter::make('prody_id')
                    ->label('Prodi')
                    ->options(function () {
                        $user = auth()->user();

                        if ($user?->hasRole('tutor')) {
                            return Prody::query()
                                ->whereIn('id', $user->assignedProdyIds())
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        }

                        return Prody::query()
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('user', fn (Builder $q) => $q->where('prody_id', $data['value']));
                        }
                    }),
            ])
            ->actions([])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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

                TextEntry::make('quiz_type')
                    ->label('Tipe Quiz')
                    ->state(function ($record) {
                        $firstQuestion = $record->quiz->questions->first();
                        return $firstQuestion ? $firstQuestion->type : 'unknown';
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'fib_paragraph'   => 'warning',
                        'multiple_choice' => 'success',
                        default           => 'gray',
                    }),
            ])->columns(3),

            Section::make('Jawaban')->schema([
                TextEntry::make('answers_list')->label('Detail')
                    ->state(function ($record) {
                        $record->loadMissing(['answers', 'quiz.questions']);
                        $rows = [];

                        foreach ($record->quiz->questions as $i => $q) {
                            $ans = $record->answers->firstWhere('question_id', $q->id);

                            if ($q->type === 'fib_paragraph') {
                                $rows[] = self::formatFibAnswer($q, $record->answers, $i + 1);
                            } else {
                                $rows[] = self::formatMcAnswer($q, $ans, $i + 1);
                            }
                        }
                        return implode("\n", $rows);
                    })
                    ->columnSpanFull()
                    ->formatStateUsing(fn ($state) => nl2br(e($state)))
                    ->html(),
            ])->collapsible(),
        ]);
    }

    // === Helper formatter ===
    private static function formatFibAnswer($question, $allAnswers, $number)
    {
        $paragraph  = $question->paragraph_text ?? 'No paragraph';
        $blankCount = is_array($question->fib_placeholders) ? count($question->fib_placeholders) : 0;

        $result = "({$number}) FIB PARAGRAPH - {$blankCount} blanks\n";
        $result .= "Paragraf: {$paragraph}\n\n";

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

        if (!empty($question->fib_answer_key)) {
            $result .= "\nKunci Jawaban:\n";
            foreach ($question->fib_answer_key as $blankIndex => $key) {
                $keyStr = is_array($key) ? implode(' / ', $key) : $key;
                $result .= "Blank {$blankIndex}: {$keyStr}\n";
            }
        }

        return $result . "\n" . str_repeat('-', 50) . "\n";
    }

    private static function formatMcAnswer($question, $answer, $number)
    {
        $chosen = $answer?->answer ?? '-';
        $mark   = $answer?->is_correct ? '✓' : '✗';

        return sprintf(
            "(%02d) [%s] %s\nA. %s\nB. %s\nC. %s\nD. %s\nJawaban: %s | Kunci: %s\n%s\n",
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

    /** Scope utama resource (ADMIN = semua, TUTOR = hanya prodi yang dia ampu). */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['user.prody', 'session', 'quiz.questions', 'connectCode']);

        $user = auth()->user();

        if ($user && $user->hasRole('Admin')) {
            return $query;
        }

        if ($user && $user->hasRole('tutor')) {
            $ids = $user->assignedProdyIds();
            if (empty($ids)) {
                return $query->whereRaw('1=0');
            }

            return $query->whereHas('user', fn (Builder $q) => $q->whereIn('prody_id', $ids));
        }

        return $query->whereRaw('1=0');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBasicListeningAttempts::route('/'),
            'view'  => Pages\ViewBasicListeningAttempt::route('/{record}'),
        ];
    }
}
