<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterGrupTesResource\Pages;
use App\Filament\Resources\MasterGrupTesResource\Pages\InputNilaiGrup;
use App\Models\MasterGrupTes;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterGrupTesResource extends Resource
{
    protected static ?string $model = MasterGrupTes::class;

    protected static ?string $navigationIcon = 'heroicon-s-book-open';

    protected static ?string $navigationGroup = 'Manajemen EPT';
    
    protected static ?int $navigationSort = 1;

    public static ?string $label = 'Kelola Grup Tes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('group_number')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('instructional_year')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\DateTimePicker::make('tanggal_tes')
                    ->required(),
                Forms\Components\TextInput::make('ruangan_tes')
                    ->maxLength(255)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group_number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('instructional_year')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tanggal_tes')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ruangan_tes')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pendaftaran_grup_tes_count')
                    ->label('Jumlah Peserta')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\Action::make('Cetak')
                    ->url(fn ($record) => route('grup.cetak', $record->id))
                    ->openUrlInNewTab()
                    ->icon('heroicon-s-printer')
                    ->color('danger')
                    ->label('Data PDF'),
                Tables\Actions\Action::make('Input Nilai')
                    ->label('Input Nilai')
                    ->icon('heroicon-s-pencil-square')
                    ->url(fn ($record) => MasterGrupTesResource::getUrl('input-nilai-grup', ['record' => $record])),
                Tables\Actions\Action::make('Cetak Nilai')
                    ->url(fn ($record) => route('grup.cetak-nilai', $record->id))
                    ->openUrlInNewTab()
                    ->icon('heroicon-s-printer')
                    ->color('danger')
                    ->label('Nilai PDF'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('pendaftaranGrupTes');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah Grup Tes';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMasterGrupTes::route('/'),
            'create' => Pages\CreateMasterGrupTes::route('/create'),
            'edit' => Pages\EditMasterGrupTes::route('/{record}/edit'),
            'input-nilai-grup' => InputNilaiGrup::route('/{record}/input-nilai'),
        ];
    }
}
