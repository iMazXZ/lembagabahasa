<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{TextInput, Select, FileUpload, Toggle, DateTimePicker, Hidden};
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\{Get, Set};
use Illuminate\Support\Str;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Forms\Components\Actions\Action;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    public static ?string $label = 'Posting Informasi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label('Judul')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                    if (blank($get('slug'))) {
                        $set('slug', Str::slug((string) $state));
                    }
                }),

            TextInput::make('slug')
                ->label('Slug')
                ->readOnly()
                ->dehydrated()
                ->unique(ignoreRecord: true)
                ->suffixAction(
                    Action::make('regenerate')
                        ->icon('heroicon-o-arrow-path')
                        ->tooltip('Generate dari Judul')
                        ->action(function (Get $get, Set $set) {
                            $set('slug', Str::slug((string) $get('title')));
                        })
                ),

            Select::make('type')
                ->label('Jenis Post')
                ->options(\App\Models\Post::TYPES)
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('excerpt')
                ->label('Ringkasan')
                ->maxLength(180)
                ->helperText('Opsional. Kosongkan jika ingin otomatis.')
                ->live(),

            FileUpload::make('cover_path')
                ->label('Gambar Sampul')
                ->image()
                ->directory('posts/covers')
                ->imageEditor()
                ->downloadable()
                ->visibility('public')
                ->nullable()
                ->helperText('Kosongkan untuk memakai cover default.'),

            TiptapEditor::make('body')
                ->label('Konten')
                ->required()
                ->columnSpanFull()
                ->maxContentWidth('4xl')
                ->profile('simple')
                ->tools([
                    'heading','bullet-list','ordered-list','bold','italic','underline','link',
                    'table',
                    'media',
                    'code','code-block','blockquote','hr',
                    'undo','redo',
                ]),

            Toggle::make('is_published')
                ->label('Publikasikan?')
                ->default(false),

            DateTimePicker::make('published_at')
                ->label('Waktu Publikasi')
                ->seconds(false)
                ->native(false)
                ->default(now()),

            Hidden::make('author_id')
                ->default(fn () => auth()->id())
                ->dehydrated()
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => \App\Models\Post::TYPES[$state] ?? $state)
                    ->badge(),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),

                Tables\Columns\TextColumn::make('published_at')
                    ->dateTime('d M Y H:i')
                    ->label('Dipublikasi')
                    ->sortable(),

                // ====== Kolom Views ======
                Tables\Columns\TextColumn::make('views')
                    ->label('Views')
                    ->sortable()
                    ->alignRight()
                    ->formatStateUsing(fn ($state) => number_format((int) $state))
                    ->tooltip('Total view per-refresh (dengan rate limit ringan)')
                    ->toggleable(), // bisa disembunyikan via kebab menu
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(\App\Models\Post::TYPES),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Hanya published'),
            ])
            ->defaultSort('published_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
