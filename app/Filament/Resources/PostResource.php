<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use App\Support\ImageTransformer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{
    TextInput, Select, Toggle, DateTimePicker, Hidden, 
    Section, Group, Textarea, Grid
};
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\{Get, Set};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    public static ?string $label = 'Posting Informasi';

    // Menambahkan badge jumlah post di sidebar navigasi
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // === KOLOM KIRI (KONTEN UTAMA) ===
            Group::make()->schema([
                Section::make('Konten Utama')->schema([
                    TextInput::make('title')
                        ->label('Judul')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('slug')
                        ->label('Slug URL')
                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Str::slug($state) : null)
                        ->dehydrated()
                        ->unique(ignoreRecord: true)
                        ->helperText('Boleh dikosongkan. Slug otomatis dibuat dari judul saat simpan.'),

                    Textarea::make('excerpt')
                        ->label('Ringkasan / Intro')
                        ->rows(3)
                        ->maxLength(180)
                        ->columnSpanFull()
                        ->helperText('Teks singkat yang muncul di daftar berita (Maks. 180 karakter).'),

                    TiptapEditor::make('body')
                        ->label('Isi Berita')
                        ->required()
                        ->columnSpanFull()
                        ->disk('public')
                        ->directory('posts/body')
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, callable $set) {
                            $result = ImageTransformer::toWebpFromUploaded(
                                uploaded:   $file,
                                targetDisk: 'public',
                                targetDir:  'posts/body',
                                quality:    82,
                                maxWidth:   1600,
                                maxHeight:  1600,
                            );

                            $set('width', $result['width'] ?? null);
                            $set('height', $result['height'] ?? null);

                            return Storage::disk('public')->url($result['path']);
                        })
                        ->maxContentWidth('full') // Agar editor lebih luas
                        ->profile('default') // Gunakan profile default agar fitur lengkap
                        ->tools([
                            'heading', 'bullet-list', 'ordered-list', 'bold', 'italic', 
                            'underline', 'link', 'table', 'media', 'code', 'code-block', 
                            'blockquote', 'hr', 'undo', 'redo', 'align-left', 'align-center', 'align-right'
                        ]),
                ]),
            ])->columnSpan(['lg' => 2]), // 2/3 layar di desktop

            // === KOLOM KANAN (METADATA & MEDIA) ===
            Group::make()->schema([
                Section::make('Status & Publikasi')->schema([
                    Select::make('type')
                        ->label('Kategori')
                        ->options(\App\Models\Post::TYPES)
                        ->default(function (): ?string {
                            $type = request()->query('type');

                            if (! is_string($type)) {
                                return null;
                            }

                            return array_key_exists($type, Post::TYPES) ? $type : null;
                        })
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->live(),

                    // Grid untuk Nomor Grup dan Tanggal (2 kolom)
                    Grid::make(2)
                        ->schema([
                            TextInput::make('group_number')
                                ->label('Nomor Grup')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(999)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                    $eventDate = $get('event_date');
                                    if ($state && $eventDate) {
                                        $date = \Carbon\Carbon::parse($eventDate);
                                        $formatted = $date->translatedFormat('l, d F Y');
                                        $set('title', "Jadwal Tes EPT Grup {$state} ({$formatted})");
                                    }
                                }),

                            Forms\Components\DatePicker::make('event_date')
                                ->label('Tanggal Tes')
                                ->native(false)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                    $groupNumber = $get('group_number');
                                    if ($groupNumber && $state) {
                                        $date = \Carbon\Carbon::parse($state);
                                        $formatted = $date->translatedFormat('l, d F Y');
                                        $set('title', "Jadwal Tes EPT Grup {$groupNumber} ({$formatted})");
                                    }
                                }),

                            Forms\Components\TimePicker::make('event_time')
                                ->label('Waktu Tes')
                                ->native(false)
                                ->seconds(false)
                                ->default('08:30'),

                            TextInput::make('event_location')
                                ->label('Lokasi/Ruangan')
                                ->maxLength(255)
                                ->default('Kampus 3, Ruang Standford'),
                        ])
                        ->visible(fn (Get $get) => $get('type') === 'schedule'),

                    // Field reaktif: Pilih Jadwal Terkait (hanya untuk tipe nilai)
                    Select::make('related_post_id')
                        ->label('Jadwal Terkait')
                        ->default(function (): ?int {
                            if (request()->query('type') !== 'scores') {
                                return null;
                            }

                            $relatedId = (int) request()->query('related_post_id');

                            return $relatedId > 0 ? $relatedId : null;
                        })
                        ->options(function (Get $get): array {
                            if ($get('type') !== 'scores') {
                                return [];
                            }

                            $selectedId = (int) ($get('related_post_id') ?? 0);

                            return Post::query()
                                ->where('type', 'schedule')
                                ->where(function (Builder $query) use ($selectedId): void {
                                    $query->whereDoesntHave('relatedScores');

                                    if ($selectedId > 0) {
                                        $query->orWhere('id', $selectedId);
                                    }
                                })
                                ->orderByDesc('created_at')
                                ->orderByDesc('id')
                                ->limit(20)
                                ->pluck('title', 'id')
                                ->all();
                        })
                        ->afterStateHydrated(function (Set $set, Get $get, ?int $state): void {
                            if (! $state || filled($get('title'))) {
                                return;
                            }

                            static::fillScoresTitleFromRelatedPost($set, $state);
                        })
                        ->getSearchResultsUsing(function (Get $get, string $search): array {
                            $selectedId = (int) ($get('related_post_id') ?? 0);

                            return Post::query()
                                ->where('type', 'schedule')
                                ->where(function (Builder $query) use ($selectedId): void {
                                    $query->whereDoesntHave('relatedScores');

                                    if ($selectedId > 0) {
                                        $query->orWhere('id', $selectedId);
                                    }
                                })
                                ->where('title', 'like', '%' . trim($search) . '%')
                                ->orderByDesc('created_at')
                                ->orderByDesc('id')
                                ->limit(20)
                                ->pluck('title', 'id')
                                ->all();
                        })
                        ->getOptionLabelUsing(fn ($value): ?string => Post::query()->whereKey($value)->value('title'))
                        ->searchable()
                        ->optionsLimit(20)
                        ->searchPrompt('Tampilkan 20 jadwal terbaru. Ketik untuk mencari yang lain.')
                        ->searchingMessage('Mencari jadwal...')
                        ->noSearchResultsMessage('Tidak ada jadwal yang cocok.')
                        ->required(fn (Get $get): bool => $get('type') === 'scores')
                        ->unique(
                            table: Post::class,
                            column: 'related_post_id',
                            ignoreRecord: true,
                            modifyRuleUsing: fn ($rule) => $rule->where('type', 'scores'),
                        )
                        ->validationMessages([
                            'unique' => 'Jadwal ini sudah memiliki post nilai.',
                        ])
                        ->visible(fn (Get $get) => $get('type') === 'scores')
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?int $state) {
                            // Auto generate judul dari jadwal terkait
                            if ($state) {
                                static::fillScoresTitleFromRelatedPost($set, $state);
                            }
                        })
                        ->helperText('Judul akan otomatis terisi dari jadwal terkait'),

                    Toggle::make('is_published')
                        ->label('Terbitkan Sekarang')
                        ->onColor('success')
                        ->offColor('danger')
                        ->default(false),

                    DateTimePicker::make('published_at')
                        ->label('Waktu Tayang')
                        ->seconds(false)
                        ->native(false)
                        ->default(now())
                        ->helperText('Dapat dijadwalkan untuk masa depan.'),
                ]),

                // Hidden field tetap ada
                Hidden::make('author_id')
                    ->default(fn () => auth()->id()),
            ])->columnSpan(['lg' => 1]), // 1/3 layar di desktop
        ])->columns(3); // Total grid 3 kolom
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'relatedScores:id,related_post_id,slug,published_at',
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(30)
                    ->wrap()
                    ->weight('bold')
                    // Tampilkan excerpt kecil di bawah judul
                    ->description(fn (Post $record): string => Str::limit($record->excerpt, 40)),

                Tables\Columns\TextColumn::make('type')
                    ->label('Kategori')
                    ->formatStateUsing(fn ($state) => \App\Models\Post::TYPES[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'news'         => 'info',
                        'schedule'     => 'warning',
                        'scores'       => 'success',
                        'service'      => 'primary',
                        'article'      => 'success',
                        'announcement' => 'danger',
                        default        => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('scores_status')
                    ->label('Status Nilai')
                    ->state(function (Post $record): string {
                        if ($record->type !== 'schedule') {
                            return '-';
                        }

                        return static::hasScorePostForSchedule($record) ? 'Sudah Ada' : 'Belum Ada';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Sudah Ada' => 'success',
                        'Belum Ada' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Tayang'),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('d M Y, H:i')
                    ->label('Waktu')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('views')
                    ->label('Dilihat')
                    ->icon('heroicon-m-eye')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->placeholder('Semua')
                    ->trueLabel('Tayang')
                    ->falseLabel('Draft'),

                Tables\Filters\TernaryFilter::make('has_scores')
                    ->label('Status Nilai (Jadwal)')
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Ada')
                    ->falseLabel('Belum Ada')
                    ->queries(
                        true: fn (Builder $query): Builder => $query
                            ->where('type', 'schedule')
                            ->whereHas('relatedScores'),
                        false: fn (Builder $query): Builder => $query
                            ->where('type', 'schedule')
                            ->whereDoesntHave('relatedScores'),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                Tables\Filters\Filter::make('published_range')
                    ->label('Rentang Tayang')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        if ($from && $until) {
                            return "Tayang: {$from} s/d {$until}";
                        }

                        if ($from) {
                            return "Tayang ≥ {$from}";
                        }

                        if ($until) {
                            return "Tayang ≤ {$until}";
                        }

                        return null;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        $from = $data['from'] ?? null;
                        $until = $data['until'] ?? null;

                        if ($from) {
                            $query->whereDate('published_at', '>=', $from);
                        }

                        if ($until) {
                            $query->whereDate('published_at', '<=', $until);
                        }

                        return $query;
                    }),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->persistFiltersInSession()
            ->groups([
                Tables\Grouping\Group::make('type')
                    ->label('Kategori')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (Post $record): string => Post::TYPES[$record->type] ?? ucfirst((string) $record->type))
                    ->collapsible(),

                Tables\Grouping\Group::make('published_at')
                    ->label('Tanggal Tayang')
                    ->date()
                    ->collapsible(),
            ])
            ->defaultGroup('type')
            ->defaultSort('published_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('scores_post')
                        ->label(function (Post $record): string {
                            return static::hasScorePostForSchedule($record) ? 'Lihat Nilai' : 'Input Nilai';
                        })
                        ->icon(function (Post $record): string {
                            return static::hasScorePostForSchedule($record) ? 'heroicon-m-document-text' : 'heroicon-m-plus-circle';
                        })
                        ->color(function (Post $record): string {
                            return static::hasScorePostForSchedule($record) ? 'info' : 'success';
                        })
                        ->visible(fn (Post $record): bool => $record->type === 'schedule')
                        ->url(function (Post $record): string {
                            $scorePost = static::latestScorePostForSchedule($record);

                            if ($scorePost) {
                                return route('front.post.show', ['post' => $scorePost]);
                            }

                            return static::getUrl('create', [
                                'type' => 'scores',
                                'related_post_id' => $record->id,
                            ]);
                        })
                        ->openUrlInNewTab(fn (Post $record): bool => static::hasScorePostForSchedule($record)),
                Tables\Actions\Action::make('view_public')
                        ->label('Lihat Post')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->url(fn (Post $record): string => route('front.post.show', ['post' => $record]))
                        ->openUrlInNewTab(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    protected static function fillScoresTitleFromRelatedPost(Set $set, int $relatedPostId): void
    {
        $related = Post::query()
            ->select(['id', 'title', 'event_date'])
            ->find($relatedPostId);

        if (! $related || ! $related->event_date) {
            return;
        }

        $formattedDate = $related->event_date->translatedFormat('l, d F Y');
        preg_match('/Grup\s*(\d+)/i', $related->title, $matches);
        $groupNum = $matches[1] ?? '';
        $groupLabel = $groupNum !== '' ? " {$groupNum}" : '';

        $set('title', "Nilai EPT Grup{$groupLabel} ({$formattedDate})");
    }

    protected static function latestScorePostForSchedule(Post $schedulePost): ?Post
    {
        if ($schedulePost->type !== 'schedule') {
            return null;
        }

        if ($schedulePost->relationLoaded('relatedScores')) {
            /** @var ?Post $scorePost */
            $scorePost = $schedulePost->relatedScores->first();

            return $scorePost;
        }

        return $schedulePost->relatedScores()
            ->select(['id', 'related_post_id', 'slug', 'published_at'])
            ->first();
    }

    protected static function hasScorePostForSchedule(Post $schedulePost): bool
    {
        return static::latestScorePostForSchedule($schedulePost) instanceof Post;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
