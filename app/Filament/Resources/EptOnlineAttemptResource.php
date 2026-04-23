<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptOnlineAttemptResource\Pages;
use App\Models\EptOnlineAttempt;
use App\Models\EptOnlineForm;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;

class EptOnlineAttemptResource extends BaseResource
{
    protected static ?string $model = EptOnlineAttempt::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'EPT';
    protected static ?string $navigationLabel = 'Attempt Online';
    protected static ?string $modelLabel = 'Attempt Online';
    protected static ?string $pluralModelLabel = 'Attempt Online';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('form.code')
                    ->label('Paket')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Grup')
                    ->placeholder('-')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        EptOnlineAttempt::STATUS_SUBMITTED => 'success',
                        EptOnlineAttempt::STATUS_EXPIRED => 'danger',
                        EptOnlineAttempt::STATUS_CANCELLED => 'gray',
                        EptOnlineAttempt::STATUS_IN_PROGRESS => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('current_section_type')
                    ->label('Section')
                    ->badge()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Submit')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('form_id')
                    ->options(EptOnlineForm::query()->orderBy('code')->pluck('code', 'id')->all())
                    ->label('Paket Tes'),
                Tables\Filters\SelectFilter::make('status')
                    ->options(EptOnlineAttempt::statusOptions())
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Hapus Terpilih'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptOnlineAttempts::route('/'),
        ];
    }
}
