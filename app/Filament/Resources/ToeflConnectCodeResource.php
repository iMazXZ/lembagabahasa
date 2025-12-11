<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ToeflConnectCodeResource\Pages;
use App\Models\ToeflConnectCode;
use App\Models\ToeflExam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ToeflConnectCodeResource extends Resource
{
    protected static ?string $model = ToeflConnectCode::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'TOEFL Online';
    protected static ?string $navigationLabel = 'Connect Code';
    protected static ?string $modelLabel = 'Connect Code';
    protected static ?string $pluralModelLabel = 'Connect Code';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Kode')->schema([
                Forms\Components\Select::make('exam_id')
                    ->label('Ujian')
                    ->options(ToeflExam::where('is_active', true)->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Forms\Components\TextInput::make('code_plain')
                    ->label('Kode Akses')
                    ->required()
                    ->maxLength(64)
                    ->helperText('Kode yang akan dimasukkan peserta')
                    ->default(fn () => strtoupper(Str::random(8)))
                    ->dehydrated(false)
                    ->afterStateHydrated(function ($component, $state, $record) {
                        // Tidak bisa unhash, jadi kosongkan saat edit
                        if ($record) {
                            $component->state($record->code_hint ?? '********');
                        }
                    }),
                Forms\Components\TextInput::make('code_hint')
                    ->label('Nama Grup')
                    ->placeholder('Grup 001')
                    ->helperText('Label untuk identifikasi grup'),
            ])->columns(3),

            Forms\Components\Section::make('Waktu Aktif')->schema([
                Forms\Components\DateTimePicker::make('starts_at')
                    ->label('Mulai')
                    ->required(),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->label('Berakhir')
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Pengaturan')->schema([
                Forms\Components\TextInput::make('max_uses')
                    ->label('Batas Pemakaian')
                    ->numeric()
                    ->placeholder('Kosongkan = unlimited')
                    ->helperText('Jumlah maksimal peserta yang bisa pakai kode ini'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(2),

            Forms\Components\Hidden::make('created_by')
                ->default(fn () => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('exam.name')
                    ->label('Ujian')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('code_hint')
                    ->label('Grup')
                    ->searchable(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Berakhir')
                    ->dateTime('d M Y, H:i'),
                Tables\Columns\TextColumn::make('attempts_count')
                    ->label('Dipakai')
                    ->counts('attempts'),
                Tables\Columns\TextColumn::make('max_uses')
                    ->label('Maks')
                    ->default('∞'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('exam_id')
                    ->label('Ujian')
                    ->options(ToeflExam::pluck('name', 'id')),
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
            'index' => Pages\ListToeflConnectCodes::route('/'),
            'create' => Pages\CreateToeflConnectCode::route('/create'),
            'edit' => Pages\EditToeflConnectCode::route('/{record}/edit'),
        ];
    }
}
