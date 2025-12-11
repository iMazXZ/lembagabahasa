<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToeflPackageResource\Pages;
use App\Models\ToeflPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ToeflPackageResource extends Resource
{
    protected static ?string $model = ToeflPackage::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'TOEFL Online';
    protected static ?string $navigationLabel = 'Paket Soal';
    protected static ?string $modelLabel = 'Paket Soal';
    protected static ?string $pluralModelLabel = 'Paket Soal';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Paket')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Paket')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Paket A'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Audio Listening')->schema([
                Forms\Components\FileUpload::make('listening_audio_path')
                    ->label('File Audio Listening')
                    ->helperText('1 file MP3 untuk seluruh 50 soal Listening')
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp3'])
                    ->directory('toefl-audio')
                    ->maxSize(102400) // 100MB
                    ->disk('public'),
            ]),

            Forms\Components\Section::make('Durasi per Section (menit)')->schema([
                Forms\Components\TextInput::make('listening_duration')
                    ->label('Listening')
                    ->numeric()
                    ->default(35)
                    ->suffix('menit'),
                Forms\Components\TextInput::make('structure_duration')
                    ->label('Structure')
                    ->numeric()
                    ->default(25)
                    ->suffix('menit'),
                Forms\Components\TextInput::make('reading_duration')
                    ->label('Reading')
                    ->numeric()
                    ->default(55)
                    ->suffix('menit'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Jumlah Soal')
                    ->counts('questions')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListToeflPackages::route('/'),
            'create' => Pages\CreateToeflPackage::route('/create'),
            'edit' => Pages\EditToeflPackage::route('/{record}/edit'),
        ];
    }
}
