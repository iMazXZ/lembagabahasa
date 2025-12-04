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
    TextInput, Select, FileUpload, Toggle, DateTimePicker, Hidden, 
    Section, Group, Textarea, Grid
};
use Filament\Tables\Columns\{TextColumn, ImageColumn, IconColumn, ToggleColumn};
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\{Get, Set};
use Illuminate\Support\Str;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Forms\Components\Actions\Action;
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
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            // Auto generate slug hanya jika slug kosong atau mode create
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->dehydrated()
                        ->unique(ignoreRecord: true)
                        ->helperText('URL unik untuk postingan ini.')
                        ->suffixAction(
                            Action::make('regenerate')
                                ->icon('heroicon-o-arrow-path')
                                ->tooltip('Generate ulang dari Judul')
                                ->action(function (Get $get, Set $set) {
                                    $set('slug', Str::slug((string) $get('title')));
                                })
                        ),

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
                        ->required()
                        ->searchable()
                        ->native(false),

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

                Section::make('Media')->schema([
                    FileUpload::make('cover_path')
                        ->label('Gambar Sampul')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('posts/covers')
                        ->imageEditor()
                        ->imagePreviewHeight('150') // Preview tidak terlalu besar
                        ->maxSize(4096) // Limit 4MB (akan dikompresi)
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, callable $get) {
                            $old = $get('cover_path');
                            if (is_string($old) && $old !== '' && Storage::disk('public')->exists($old)) {
                                Storage::disk('public')->delete($old);
                            }

                            $result = ImageTransformer::toWebpFromUploaded(
                                uploaded:   $file,
                                targetDisk: 'public',
                                targetDir:  'posts/covers',
                                quality:    82,
                                maxWidth:   1600,
                                maxHeight:  1600,
                            );

                            return $result['path'];
                        })
                        ->deleteUploadedFileUsing(function (string $file) {
                            if (Storage::disk('public')->exists($file)) {
                                Storage::disk('public')->delete($file);
                            }
                        })
                        ->columnSpanFull(),
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
            ->columns([
                // UPDATE: Logika Default Image berdasarkan Tipe
                Tables\Columns\ImageColumn::make('cover_path')
                    ->label('Cover')
                    ->square()
                    ->defaultImageUrl(function (Post $record) {
                        // Pilih gambar berdasarkan type
                        $filename = match ($record->type) {
                            'schedule' => 'schedule.jpg',
                            'scores'   => 'scores.jpg',
                            default    => 'news.jpg',
                        };
                        
                        return url("/images/covers/{$filename}");
                    }),

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
                        'schedule'     => 'warning', // Asumsi jadwal warna kuning/oranye
                        'scores'       => 'success', // Asumsi nilai warna hijau
                        'article'      => 'success',
                        'announcement' => 'danger',
                        default        => 'gray',
                    })
                    ->sortable(),

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
                SelectFilter::make('type')
                    ->label('Kategori')
                    ->options(\App\Models\Post::TYPES),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Status Publikasi'),
            ])
            ->defaultSort('published_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view_public')
                        ->label('Lihat Post')
                        ->icon('heroicon-m-arrow-top-right-on-square')
                        ->url(fn (Post $record): string => route('front.post.show', $record))
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
