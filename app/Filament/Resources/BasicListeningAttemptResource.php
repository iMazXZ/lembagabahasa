<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BasicListeningAttemptResource\Pages;
use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningQuestion;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\Collection;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;

use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Illuminate\Support\Facades\DB;

class BasicListeningAttemptResource extends Resource
{
    protected static ?string $model = BasicListeningAttempt::class;

    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Basic Listening';
    protected static ?string $pluralLabel     = 'Attempts (Hasil Kuis)';
    protected static ?string $modelLabel      = 'Attempt';

    // Cache sederhana untuk menyimpan data soal selama request berlangsung
    protected static array $questionCache = [];

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with([
                'user.prody',
                'session',
                'quiz.questions', 
                'connectCode',
                'answers',
            ]);

        $user = auth()->user();

        if ($user && ($user->hasRole('Admin') || $user->hasRole('superuser'))) {
            return $query;
        }

        if ($user && ($user->hasRole('Tutor') || $user->hasRole('tutor'))) {
            $prodyIds = [];
            if (method_exists($user, 'assignedProdyIds')) {
                $prodyIds = $user->assignedProdyIds();
            } 
            
            if (empty($prodyIds)) {
                return $query->whereRaw('1=0');
            }

            return $query->whereHas('user', fn (Builder $q) => $q->whereIn('prody_id', $prodyIds));
        }

        return $query->whereRaw('1=0');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Attempt')
                ->columns(12)
                ->schema([
                    Placeholder::make('user_name')
                        ->label('Peserta')
                        ->content(fn ($record) => $record?->user?->name . ' (' . ($record?->user?->srn ?? '-') . ')')
                        ->columnSpan(5)
                        ->extraAttributes(['class' => 'text-gray-900 font-bold']),

                    Placeholder::make('prody_name')
                        ->label('Prodi')
                        ->content(fn ($record) => $record?->user?->prody?->name ?? '-')
                        ->columnSpan(3),

                    Placeholder::make('quiz_title')
                        ->label('Paket Soal')
                        ->content(fn ($record) => $record?->quiz?->title ?? '-')
                        ->columnSpan(4),

                    Grid::make(12)->schema([
                        TextInput::make('score')
                            ->label('Skor Akhir')
                            ->helperText('Skor dihitung ulang otomatis saat disimpan.')
                            ->numeric()
                            ->suffix('%')
                            ->columnSpan(3),

                        DateTimePicker::make('submitted_at')
                            ->label('Waktu Submit')
                            ->seconds(false)
                            ->native(false)
                            ->columnSpan(4),
                            
                        DateTimePicker::make('created_at')
                            ->label('Waktu Mulai')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(4),
                    ])->columnSpan(12),
                ]),

            Section::make('Koreksi Jawaban')
                ->description('Koreksi manual jawaban siswa.')
                ->collapsible()
                ->schema([
                    Repeater::make('answers')
                        ->relationship('answers')
                        ->reorderable(false)
                        ->addable(false)
                        ->deletable(false)
                        ->grid(1)
                        ->schema([
                            Hidden::make('id'),
                            Hidden::make('question_id'),
                            
                            // PERBAIKAN PENTING:
                            // Simpan index murni (0, 1, 2) di Hidden Field agar logika $get('blank_index') akurat
                            Hidden::make('blank_index')
                                ->default(0),

                            Grid::make(12)->schema([
                                // Tampilkan Label menggunakan Placeholder (Visual Saja)
                                Placeholder::make('blank_label')
                                    ->label('#')
                                    ->content(fn (Get $get) => 'Isian #'.((int)$get('blank_index') + 1))
                                    ->extraAttributes(['class' => 'text-gray-500 font-mono text-sm'])
                                    ->columnSpan(2),

                                TextInput::make('answer')
                                    ->label('Jawaban Siswa')
                                    ->columnSpan(5),

                                // Kunci Jawaban (Logic Cerdas)
                                Placeholder::make('correct_key')
                                    ->label('Kunci Jawaban')
                                    ->content(function (Get $get) {
                                        $qId = $get('question_id');
                                        // Ambil index murni dari hidden field (0, 1, 2...)
                                        $idx = (int) $get('blank_index');
                                        
                                        if (!$qId) return '—';

                                        if (!isset(static::$questionCache[$qId])) {
                                            static::$questionCache[$qId] = BasicListeningQuestion::find($qId);
                                        }
                                        $q = static::$questionCache[$qId];

                                        if (!$q) return '?';

                                        if ($q->type === 'fib_paragraph') {
                                            $keys = $q->fib_answer_key ?? [];
                                            
                                            // Deteksi apakah kunci dimulai dari 1 (1-based)
                                            $hasKey1 = array_key_exists(1, $keys) || array_key_exists('1', $keys);
                                            $hasKey0 = array_key_exists(0, $keys) || array_key_exists('0', $keys);
                                            $isOneBased = $hasKey1 && !$hasKey0;

                                            // Jika 1-based, kita geser index DB (+1)
                                            $lookupIndex = $isOneBased ? ($idx + 1) : $idx;
                                            
                                            $key = $keys[$lookupIndex] ?? null;
                                            
                                            if (is_array($key)) return implode(' / ', $key);
                                            if (is_string($key) || is_numeric($key)) return $key;
                                            return '—';
                                        } 
                                        
                                        return $q->correct ?? '—';
                                    })
                                    ->extraAttributes(['class' => 'text-emerald-600 font-mono font-bold'])
                                    ->columnSpan(3),

                                Select::make('is_correct')
                                    ->label('Status')
                                    ->options([
                                        '1' => 'Benar',
                                        '0' => 'Salah',
                                    ])
                                    ->selectablePlaceholder(false)
                                    ->native(false)
                                    ->columnSpan(2),
                            ]),
                        ])
                        ->itemLabel(fn (array $state): ?string =>
                            isset($state['blank_index']) ? 'Isian #' . ((int)$state['blank_index'] + 1) : 'Soal'
                        ),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfoSection::make('Ringkasan Attempt')
                ->columns(4)
                ->schema([
                    TextEntry::make('user.name')->label('Peserta')->weight('bold'),
                    TextEntry::make('user.srn')->label('SRN/NIM')->copyable(),
                    TextEntry::make('user.prody.name')->label('Prodi'),
                    TextEntry::make('user.nomor_grup_bl')->label('Grup BL'),

                    TextEntry::make('session.title')
                        ->label('Sesi')
                        ->prefix(fn($record)=>'Pert. '.$record->session->number.' - '),
                    
                    TextEntry::make('score')
                        ->label('Skor Akhir')
                        ->size(TextEntry\TextEntrySize::Large)
                        ->weight('black')
                        ->color(fn ($state) => $state >= 60 ? 'success' : 'danger')
                        ->formatStateUsing(fn ($state) => $state . '%'),

                    TextEntry::make('submitted_at')->label('Waktu Submit')->dateTime('d M Y, H:i'),
                    
                    TextEntry::make('stats')
                        ->label('Statistik')
                        ->state(function ($record) {
                            $total = $record->answers->count();
                            $correct = $record->answers->where('is_correct', true)->count();
                            return "{$correct} Benar / {$total} Total";
                        }),
                ]),

            ViewEntry::make('answers_view')
                ->view('filament.attempts.answers-view')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peserta')
                    ->description(fn($record) => $record->user?->srn)
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.prody.name')
                    ->label('Prodi')
                    ->sortable()
                    ->limit(20)
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('session.title')
                    ->label('Sesi')
                    ->limit(20)
                    ->tooltip(fn($state)=>$state),

                Tables\Columns\TextColumn::make('score')
                    ->label('Skor')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state < 60     => 'danger',
                        $state < 80     => 'warning',
                        default         => 'success',
                    })
                    ->formatStateUsing(fn ($state) => $state !== null ? $state.'%' : '—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('connectCode.code_hint')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submit')
                    ->dateTime('d M H:i')
                    ->sortable()
                    ->placeholder('Belum'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('prody_id')
                    ->label('Prodi')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user?->hasRole('tutor')) {
                            $ids = method_exists($user, 'assignedProdyIds') ? (array) $user->assignedProdyIds() : [];
                            return \App\Models\Prody::whereIn('id', $ids)->pluck('name','id');
                        }
                        return \App\Models\Prody::pluck('name','id');
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('user', fn($q) => $q->where('prody_id', $data['value']));
                        }
                    }),

                Tables\Filters\TernaryFilter::make('submitted_at')
                    ->label('Status Submit')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Submit')
                    ->falseLabel('Belum/Sedang Mengerjakan')
                    ->queries(
                        true: fn ($q) => $q->whereNotNull('submitted_at'),
                        false: fn ($q) => $q->whereNull('submitted_at'),
                    ),
            ])
            ->actions([
                \Filament\Tables\Actions\ActionGroup::make([
                    
                    Tables\Actions\ViewAction::make(),

                    Tables\Actions\EditAction::make()
                        ->visible(fn () => auth()->user()?->hasAnyRole(['Admin','tutor']))
                        ->authorize(fn () => auth()->user()?->hasAnyRole(['Admin','tutor'])),
                    
                ])
                ->label('Aksi')
                ->icon('heroicon-m-cog-6-tooth')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => 
                            auth()->user()?->hasAnyRole(['Admin', 'superuser']) 
                        )
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            // Opsional: Tambahkan logika pembersihan terkait jika perlu
                            // Misalnya menghapus file log atau data terkait lainnya secara manual
                            // Tapi karena kita pakai cascade delete di DB, $records->each->delete() sudah cukup.
                            
                            $records->each->delete();
                            
                            // Notifikasi sukses
                            \Filament\Notifications\Notification::make()
                                ->title('Data berhasil dihapus')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBasicListeningAttempts::route('/'),
            'edit'  => Pages\EditBasicListeningAttempt::route('/{record}/edit'),
            'view'  => Pages\ViewBasicListeningAttempt::route('/{record}'),
        ];
    }
}