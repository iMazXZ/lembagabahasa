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

                Tables\Columns\TextColumn::make('session.title')
                    ->label('Judul')
                    ->limit(30),

                Tables\Columns\TextColumn::make('score')
                    ->label('Skor')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state === null          => 'gray',
                        $state >= 80             => 'success',
                        $state >= 60             => 'warning',
                        default                  => 'danger',
                    }),

                // Tampilkan hint kode (bukan plaintext)
                Tables\Columns\TextColumn::make('connectCode.code_hint')
                    ->label('Kode')
                    ->state(fn ($record) => $record->connectCode?->code_hint ?? '—')
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
            ])->columns(2),

            Section::make('Jawaban')->schema([
                TextEntry::make('answers_list')->label('Detail')
                    ->state(function($record){
                        $record->loadMissing(['answers','quiz.questions']);
                        $rows = [];
                        foreach ($record->quiz->questions as $i=>$q) {
                            $ans = $record->answers->firstWhere('question_id',$q->id);
                            $chosen = $ans?->answer ?? '-';
                            $mark = $ans?->is_correct ? '✓' : '✗';
                            $rows[] = sprintf("(%02d) [%s] %s\nA. %s\nB. %s\nC. %s\nD. %s\nJawaban: %s  | Kunci: %s\n",
                                $i+1,
                                $mark,
                                $q->question,
                                $q->option_a,$q->option_b,$q->option_c,$q->option_d,
                                $chosen, $q->correct
                            );
                        }
                        return implode("\n", $rows);
                    })
                    ->columnSpanFull()
                    ->formatStateUsing(fn($state)=> nl2br(e($state)))
                    ->html(),
            ])->collapsible(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBasicListeningAttempts::route('/'),
            'view' => Pages\ViewBasicListeningAttempt::route('/{record}'),
        ];
    }
}
