<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToeflExamResource\Pages;
use App\Models\ToeflExam;
use App\Models\ToeflPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ToeflExamResource extends Resource
{
    protected static ?string $model = ToeflExam::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'TOEFL Online';
    protected static ?string $navigationLabel = 'Jadwal Ujian';
    protected static ?string $modelLabel = 'Jadwal Ujian';
    protected static ?string $pluralModelLabel = 'Jadwal Ujian';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Ujian')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Ujian')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('EPT Gelombang 1 - Januari 2025'),
                Forms\Components\Select::make('package_id')
                    ->label('Paket Soal')
                    ->options(ToeflPackage::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('Tanggal & Waktu')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Ujian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('package.name')
                    ->label('Paket Soal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('attempts_count')
                    ->label('Peserta')
                    ->counts('attempts'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('scheduled_at', 'desc')
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListToeflExams::route('/'),
            'create' => Pages\CreateToeflExam::route('/create'),
            'edit' => Pages\EditToeflExam::route('/{record}/edit'),
        ];
    }
}
