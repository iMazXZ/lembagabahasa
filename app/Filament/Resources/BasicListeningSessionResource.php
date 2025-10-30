<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BasicListeningSessionResource\Pages;
use App\Models\BasicListeningSession;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BasicListeningSessionResource extends Resource
{
    protected static ?string $model = BasicListeningSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-speaker-wave';
    protected static ?string $navigationGroup = 'Basic Listening';
    protected static ?string $pluralLabel = 'Meeting';
    protected static ?string $modelLabel = 'Session';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('number')
                ->label('Nomor (1-5; 6=UAS)')
                ->numeric()->minValue(1)->maxValue(6)->required()->unique(ignoreRecord: true),

            Forms\Components\TextInput::make('title')
                ->label('Judul')->required()->maxLength(255),

            Forms\Components\RichEditor::make('summary')
                ->label('Materi singkat')
                ->helperText('Untuk gambar, tempel URL lengkap (https://...) atau pakai format markdown: ![alt](https://...jpg). YouTube: tempel link, akan otomatis di-embed di halaman.')
                ->toolbarButtons([
                    'bold','italic','underline','strike',
                    'link','bulletList','orderedList',
                    'h2','h3','blockquote','codeBlock',
                    'undo','redo',
                    // jangan pakai attachFiles kalau memang tidak mau upload
                ])
                ->columnSpanFull(),

            Forms\Components\FileUpload::make('audio_url')
                ->label('Audio (opsional)')
                ->directory('bl/audios')
                ->visibility('public')
                ->acceptedFileTypes(['audio/mpeg','audio/mp3','audio/wav','audio/x-m4a'])
                ->helperText('Kosongkan bila tanpa audio.'),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\DateTimePicker::make('opens_at')->label('Buka dari'),
                Forms\Components\DateTimePicker::make('closes_at')->label('Tutup sampai'),
                Forms\Components\TextInput::make('duration_minutes')
                    ->numeric()->minValue(1)->maxValue(180)->default(10)->label('Durasi (menit)'),
            ]),

            Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('#')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->limit(40),
                Tables\Columns\IconColumn::make('audio_url')->label('Audio')
                    ->boolean()->trueIcon('heroicon-o-speaker-wave')->falseIcon('heroicon-o-no-symbol'),
                Tables\Columns\TextColumn::make('opens_at')->dateTime('d M Y H:i')->label('Buka')->sortable(),
                Tables\Columns\TextColumn::make('closes_at')->dateTime('d M Y H:i')->label('Tutup')->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')->label('Durasi'),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Dibuat'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Aktif'),
                Tables\Filters\Filter::make('open_now')->label('Sedang Open')
                    ->query(fn ($q) => $q->where(function($qq){
                        $now = now();
                        $qq->whereNull('opens_at')->orWhere('opens_at','<=',$now);
                    })->where(function($qq){
                        $now = now();
                        $qq->whereNull('closes_at')->orWhere('closes_at','>=',$now);
                    })),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('number');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBasicListeningSessions::route('/'),
            'create' => Pages\CreateBasicListeningSession::route('/create'),
            'edit' => Pages\EditBasicListeningSession::route('/{record}/edit'),
            'view' => Pages\ViewBasicListeningSession::route('/{record}'),
        ];
    }
}
