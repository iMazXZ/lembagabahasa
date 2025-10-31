<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BasicListeningAttemptResource\Pages;
use App\Models\BasicListeningAttempt;
use App\Models\Prody;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
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
                TextEntry::make('user.name')
                    ->label('Peserta')
                    ->formatStateUsing(fn ($record) => $record->user?->name ?? '—'),

                TextEntry::make('user.srn')
                    ->label('NPM')
                    ->formatStateUsing(fn ($record) => $record->user?->srn ?? '—'),

                TextEntry::make('user.prody.name')
                    ->label('Prodi')
                    ->formatStateUsing(fn ($record) => $record->user?->prody?->name ?? '—'),

                TextEntry::make('session.title')
                    ->label('Session')
                    ->formatStateUsing(fn ($record) => $record->session?->title ?? '—'),

                TextEntry::make('quiz_type')
                    ->label('Tipe Quiz')
                    ->state(function ($record) {
                        $first = $record->quiz?->questions?->first();
                        return $first?->type ?? 'unknown';
                    })
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'fib_paragraph'   => 'FIB',
                        'multiple_choice' => 'MC',
                        default           => strtoupper((string) $state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'fib_paragraph'   => 'warning',
                        'multiple_choice' => 'success',
                        default           => 'gray',
                    }),

                TextEntry::make('score')
                    ->label('Skor')
                    ->badge()
                    ->formatStateUsing(fn ($state) => is_numeric($state) ? (string) $state : '–')
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state === 0    => 'danger',
                        $state >= 80    => 'success',
                        $state >= 60    => 'warning',
                        default         => 'danger',
                    }),

                TextEntry::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),

                TextEntry::make('submitted_at')
                    ->label('Submit')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),

                TextEntry::make('summary_stats')
                    ->label('Ringkas')
                    ->state(function ($record) {
                        $qCount  = $record->quiz?->questions?->count() ?? 0;
                        $aCount  = $record->answers?->count() ?? 0;
                        $correct = $record->answers?->where('is_correct', true)?->count() ?? 0;
                        return "Soal: {$qCount} • Jawab: {$aCount} • Benar: {$correct}";
                    })
                    ->placeholder('—')
                    ->helperText('Soal/Jawaban/Benar'),
            ])->columns(3),

            Section::make('Jawaban')->schema([
                ViewEntry::make('answers_view')
                    ->view('filament.attempts.answers-view')
                    ->columnSpanFull(),
            ])->collapsible()->collapsed(),
        ]);
    }

    /** Scope utama resource (ADMIN = semua, TUTOR = hanya prodi yang dia ampu). */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['user.prody', 'session', 'quiz.questions', 'connectCode', 'answers']);

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
