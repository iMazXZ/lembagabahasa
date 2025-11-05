<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BasicListeningAttemptResource\Pages;
use App\Models\BasicListeningAttempt;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\RepeatableEntry;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;

class BasicListeningAttemptResource extends Resource
{
    protected static ?string $model = BasicListeningAttempt::class;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Basic Listening';
    protected static ?string $pluralLabel     = 'Attempts (Hasil Kuis)';
    protected static ?string $modelLabel      = 'Attempt';

    /** ----------------------------------------------------------------
     * FORM
     * -----------------------------------------------------------------*/
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Attempt')
                ->columns(12)
                ->schema([
                    // Informasi peserta & quiz — tampil (non-dehydrated)
                    Placeholder::make('user_name')
                        ->label('Peserta')
                        ->content(fn (?BasicListeningAttempt $record) => $record?->user?->name ?? '-')
                        ->columnSpan(4)
                        ->extraAttributes(['class' => 'text-gray-900']),

                    Placeholder::make('user_srn')
                        ->label('SRN/NIM')
                        ->content(fn (?BasicListeningAttempt $record) => $record?->user?->srn ?? '-')
                        ->columnSpan(3),

                    Placeholder::make('prody_name')
                        ->label('Prodi')
                        ->content(fn (?BasicListeningAttempt $record) => $record?->user?->prody?->name ?? '-')
                        ->columnSpan(3),

                    Placeholder::make('quiz_title')
                        ->label('Quiz')
                        ->content(fn (?BasicListeningAttempt $record) => $record?->quiz?->title ?? '-')
                        ->columnSpan(2),

                    Grid::make(12)->schema([
                        TextInput::make('score')
                            ->label('Skor (%)')
                            ->numeric()
                            ->step('0.01')
                            ->columnSpan(3),

                        DateTimePicker::make('submitted_at')
                            ->label('Waktu Submit')
                            ->seconds(false)
                            ->native(false)
                            ->columnSpan(5),
                    ])->columnSpan(12),
                ]),

            Section::make('Jawaban (FIB)')
                ->description('Edit jawaban peserta dan tandai benar/salah. Skor dihitung ulang saat simpan.')
                ->collapsible()
                ->schema([
                    Repeater::make('answers')
                        ->relationship('answers')
                        ->reorderable(false)
                        ->grid(1)
                        ->schema([
                            Hidden::make('id'),
                            Hidden::make('question_id'), // ← pastikan ikut terdehidrasi agar kita bisa baca kunci

                            Grid::make(12)->schema([
                                TextInput::make('blank_index')
                                    ->label('Blank #')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->formatStateUsing(fn ($state) => is_numeric($state) ? ((int)$state + 1) : ($state ?? '—'))
                                    ->columnSpan(2),

                                TextInput::make('answer')
                                    ->label('Jawaban Peserta')
                                    ->columnSpan(5),

                                // === Kunci jawaban (readonly) ===
                                Placeholder::make('correct_key')
                                    ->label('Kunci')
                                    ->content(function (Get $get) {
                                        $questionId = $get('question_id');
                                        $blankIndex = $get('blank_index');

                                        if (!$questionId) {
                                            return '—';
                                        }

                                        /** @var \App\Models\BasicListeningQuestion|null $q */
                                        $q = \App\Models\BasicListeningQuestion::find($questionId);
                                        if (!$q) {
                                            return '—';
                                        }

                                        $type = $q->type ?? 'unknown';

                                        // MC: tampilkan huruf kunci (A/B/C/D) atau teksnya jika mau
                                        if ($type === 'multiple_choice') {
                                            // tampilkan huruf kuncinya
                                            return $q->correct ?? '—';
                                            // kalau mau teks opsi:
                                            // $map = ['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d];
                                            // return $q->correct ? ($map[$q->correct] ?? $q->correct) : '—';
                                        }

                                        // FIB: ambil dari fib_answer_key (array)
                                        if ($type === 'fib_paragraph') {
                                            $keys = is_array($q->fib_answer_key ?? null) ? $q->fib_answer_key : [];
                                            if ($keys === []) {
                                                return '—';
                                            }

                                            // Normalisasi index: kunci bisa 1-based; blank_index di DB biasanya 0-based
                                            $isOneBased = isset($keys[1]) && !isset($keys[0]);
                                            $displayIdx = $isOneBased ? ((int)$blankIndex + 1) : ((int)$blankIndex);

                                            $keyRaw = $keys[$displayIdx] ?? null;
                                            if (is_array($keyRaw)) {
                                                return implode(' / ', $keyRaw);
                                            }
                                            return $keyRaw ?? '—';
                                        }

                                        return '—';
                                    })
                                    ->columnSpan(3),

                                Select::make('is_correct')
                                    ->label('Status')
                                    ->options([
                                        '1' => '✅ Benar',
                                        '0' => '❌ Salah',
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->dehydrateStateUsing(
                                        fn ($state) => in_array(strtolower((string)$state), ['1','true','on','yes','y'], true)
                                    )
                                    ->columnSpan(2),
                            ]),
                        ])
                        ->itemLabel(fn (array $state): ?string =>
                            isset($state['blank_index'])
                                ? 'Blank #' . ((int)$state['blank_index'] + 1)
                                : null
                        ),
                ]),
        ]);
    }

    /** ----------------------------------------------------------------
     * INFOLIST (VIEW PAGE)
     * -----------------------------------------------------------------*/
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // ====== RINGKASAN UMUM ======
            InfoSection::make('Ringkasan Attempt')
                ->columns(4)
                ->schema([
                    TextEntry::make('user.name')->label('Peserta'),
                    TextEntry::make('user.srn')->label('SRN/NIM'),
                    TextEntry::make('user.prody.name')->label('Prodi'),
                    TextEntry::make('user.nomor_grup_bl')->label('No. Grup BL')
                        ->placeholder('—'),

                    TextEntry::make('session.number')->label('Pertemuan'),
                    TextEntry::make('session.title')->label('Judul Sesi'),
                    TextEntry::make('quiz.title')->label('Judul Quiz'),

                    TextEntry::make('started_at')->label('Mulai')
                        ->dateTime('d M Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('submitted_at')->label('Submit')
                        ->dateTime('d M Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('updated_at')->label('Diubah')
                        ->dateTime('d M Y H:i'),

                    TextEntry::make('score')->label('Skor (%)')
                        ->formatStateUsing(fn (mixed $state) => $state === null ? '—' : $state),

                    // Ringkasan jawaban
                    TextEntry::make('metrics.total_questions')
                        ->label('Total Soal')
                        ->state(fn (TextEntry $c) => $c->getRecord()?->quiz?->questions?->count() ?? 0),

                    TextEntry::make('metrics.total_answers')
                        ->label('Total Jawaban')
                        ->state(fn (TextEntry $c) => $c->getRecord()?->answers?->count() ?? 0),

                    TextEntry::make('metrics.correct_answers')
                        ->label('Benar')
                        ->state(fn (TextEntry $c) => (int) ($c->getRecord()?->answers?->where('is_correct', true)->count() ?? 0)),

                    TextEntry::make('metrics.wrong_answers')
                        ->label('Salah')
                        ->state(function (TextEntry $c) {
                            $r = $c->getRecord();
                            $total   = (int) ($r?->answers?->count() ?? 0);
                            $correct = (int) ($r?->answers?->where('is_correct', true)->count() ?? 0);
                            $unknown = (int) ($r?->answers?->whereStrict('is_correct', null)->count() ?? 0);
                            return max(0, $total - $correct - $unknown);
                        }),

                    TextEntry::make('metrics.unanswered')
                        ->label('Kosong')
                        ->state(function (TextEntry $c) {
                            $ans = $c->getRecord()?->answers ?? collect();
                            $emptyText = (int) $ans->filter(fn ($a) => ($a->answer === null || $a->answer === ''))->count();
                            $unknown   = (int) $ans->whereStrict('is_correct', null)->count();
                            return max($emptyText, $unknown);
                        }),
                ]),

            // ====== ID SENSITIF — hanya Admin/superuser ======
            InfoSection::make('ID & Metadata Teknis')
                ->columns(5)
                ->visible(fn (): bool => auth()->user()?->hasAnyRole(['Admin','superuser']) ?? false)
                ->schema([
                    TextEntry::make('id')->label('ID Attempt')
                        ->formatStateUsing(fn (mixed $state) => $state ? "#{$state}" : '—')
                        ->copyable(),

                    TextEntry::make('user_id')->label('ID User')->copyable(),
                    TextEntry::make('session_id')->label('ID Session')->copyable(),
                    TextEntry::make('quiz_id')->label('ID Quiz')->copyable(),
                    TextEntry::make('connect_code_id')->label('ID Connect Code')->copyable(),
                ]),

            // ====== Detail jawaban (Blade kustom) ======
            ViewEntry::make('answers_view')
                ->view('filament.attempts.answers-view')
                ->columnSpanFull(),
        ]);
    }

    /** ----------------------------------------------------------------
     * TABLE (INDEX)
     * -----------------------------------------------------------------*/
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peserta')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.prody.name')
                    ->label('Prodi')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('session.number')
                    ->label('Pert.')
                    ->sortable(),

                Tables\Columns\TextColumn::make('quiz_type')
                    ->label('Tipe')
                    ->state(function ($record) {
                        $firstQuestion = $record->quiz?->questions?->first();
                        return $firstQuestion?->type ?? 'unknown';
                    })
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'fib_paragraph'   => 'FIB',
                        'multiple_choice' => 'MC',
                        default           => (string) $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'fib_paragraph'   => 'warning',
                        'multiple_choice' => 'success',
                        default           => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('session.title')
                    ->label('Judul')
                    ->limit(30)
                    ->toggleable(),

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

                Tables\Columns\TextColumn::make('started_at') // ganti ke nama field milikmu jika berbeda
                    ->label('Dimulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Dikumpul')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('prody')
                    ->label('Prodi')
                    ->relationship('user.prody', 'name'),

                Tables\Filters\TernaryFilter::make('submitted')
                    ->label('Sudah Submit?')
                    ->boolean()
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('submitted_at'),
                        false: fn (Builder $q) => $q->whereNull('submitted_at'),
                        blank: fn (Builder $q) => $q
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record): bool =>
                        auth()->user()?->hasAnyRole(['Admin']) ?? false
                    ),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /** ----------------------------------------------------------------
     * RELATIONS
     * -----------------------------------------------------------------*/
    public static function getRelations(): array
    {
        return [
            // Tambahkan RelationManagers bila diperlukan
        ];
    }

    /** ----------------------------------------------------------------
     * PAGES
     * -----------------------------------------------------------------*/
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBasicListeningAttempts::route('/'),
            'view'  => Pages\ViewBasicListeningAttempt::route('/{record}'),
            'edit'  => Pages\EditBasicListeningAttempt::route('/{record}/edit'),
        ];
    }
}
