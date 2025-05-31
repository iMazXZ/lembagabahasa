<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdyResource\Pages;
use App\Filament\Resources\ProdyResource\RelationManagers;
use App\Models\Prody;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdyResource extends Resource
{
    protected static ?string $model = Prody::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    public static ?string $label = 'Daftar Program Studi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
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
        return [
            //
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole(['Admin', 'Staf Pendaftar']));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProdies::route('/'),
            'create' => Pages\CreatePrody::route('/create'),
            'edit' => Pages\EditPrody::route('/{record}/edit'),
        ];
    }
}
