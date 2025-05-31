<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataNilaiTesResource\Pages;
use App\Models\DataNilaiTes;
use App\Models\PendaftaranGrupTes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DataNilaiTesResource extends Resource
{
    protected static ?string $model = DataNilaiTes::class;

    protected static ?string $navigationIcon = 'heroicon-s-clipboard-document-check';
    
    protected static ?string $navigationGroup = 'Manajemen EPT';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('pendaftaran_grup_tes_id')
                ->label('Peserta Tes')
                ->relationship('pendaftaranGrupTes', 'id', function ($query) {
                    $query->with(['pendaftaranEpt.users', 'masterGrupTes']);
                })
                ->getOptionLabelFromRecordUsing(fn ($record) =>
                    $record->pendaftaranEpt->users->name . ' - ' .
                    $record->pendaftaranEpt->users->srn . ' - ' .
                    'Grup ' . $record->masterGrupTes->group_number
                )
                ->required(),

            Forms\Components\TextInput::make('listening_comprehension')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(200)
                ->live()
                ->afterStateUpdated(function (callable $get, callable $set) {
                    self::updateScoreAndRank($get, $set);
                }),

            Forms\Components\TextInput::make('structure_written_expr')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(200)
                ->live()
                ->afterStateUpdated(function (callable $get, callable $set) {
                    self::updateScoreAndRank($get, $set);
                }),

            Forms\Components\TextInput::make('reading_comprehension')
                ->numeric()
                ->required()
                ->minValue(0)
                ->maxValue(200)
                ->live()
                ->afterStateUpdated(function (callable $get, callable $set) {
                    self::updateScoreAndRank($get, $set);
                }),

            Forms\Components\TextInput::make('total_score')
                ->numeric()
                ->disabled()
                ->dehydrated()
                ->afterStateHydrated(function ($component, $state) {
                    $component->state($state);
                })
                ->default(0)
                ->helperText('Akan dihitung otomatis'),

            Forms\Components\TextInput::make('rank')
                ->disabled()
                ->dehydrated()
                ->afterStateHydrated(function ($component, $state) {
                    $component->state($state);
                })
                ->helperText('Akan dihitung otomatis'),

            Forms\Components\Hidden::make('selesai_pada')
                ->default(now()) // Otomatis diisi waktu saat form dibuat
                ->dehydrated(), // Pastikan nilai disimpan ke database
        ]);
    }

    private static function updateScoreAndRank(callable $get, callable $set): void
    {
        $listening = (int) ($get('listening_comprehension') ?? 0);
        $structure = (int) ($get('structure_written_expr') ?? 0);
        $reading = (int) ($get('reading_comprehension') ?? 0);
        
        $totalScore = $listening + $structure + $reading;
        $set('total_score', $totalScore);
        
        // Set rank based on total score
        $rank = $totalScore >= 400 ? 'Pass' : 'Fail';
        $set('rank', $rank);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('pendaftaranGrupTes.pendaftaranEpt.users.name')
                ->label('Nama Peserta')
                ->searchable(),
            Tables\Columns\TextColumn::make('pendaftaranGrupTes.pendaftaranEpt.users.srn')
                ->label('SRN'),
            Tables\Columns\TextColumn::make('pendaftaranGrupTes.masterGrupTes.group_number')
                ->label('Grup')
                ->sortable(),
            Tables\Columns\TextColumn::make('listening_comprehension')->label('Listening'),
            Tables\Columns\TextColumn::make('structure_written_expr')->label('Structure'),
            Tables\Columns\TextColumn::make('reading_comprehension')->label('Reading'),
            Tables\Columns\TextColumn::make('total_score')->label('Total'),
            Tables\Columns\TextColumn::make('rank'),
            Tables\Columns\TextColumn::make('selesai_pada')->dateTime('d M Y H:i'),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListDataNilaiTes::route('/'),
            'create' => Pages\CreateDataNilaiTes::route('/create'),
            'edit' => Pages\EditDataNilaiTes::route('/{record}/edit'),
        ];
    }
}