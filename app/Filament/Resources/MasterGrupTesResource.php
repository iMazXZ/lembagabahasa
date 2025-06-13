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
use Illuminate\Support\Carbon;
use Closure;
use Filament\Forms\Get;
use Illuminate\Validation\ValidationException;

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
                    ->label('Nomor Grup Tes')
                    ->numeric()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->rules(['min:1'])
                    ->validationMessages([
                        'unique' => 'Nomor Grup Tes ini Sudah Dibuat. Silakan Buat Nomor Lainnya.',
                    ]),
                Forms\Components\TextInput::make('ruangan_tes')
                    ->label('Ruangan Tes')
                    ->default('Cambridge Room')
                    ->maxLength(255)
                    ->required(),
                Forms\Components\TextInput::make('instructional_year')
                    ->maxLength(255)
                    ->helperText('Contoh: Odd 2024/2025')
                    ->required(),
                Forms\Components\DateTimePicker::make('tanggal_tes')
                    ->label('Jadwal Tes')
                    ->helperText('Pastikan Tanggal Tes tidak bentrok dengan jadwal lainnya.')
                    ->required()
                    ->withoutSeconds()
                    ->native(false)
                    ->minutesStep(10)
                    ->default(Carbon::today()->setTime(13, 20))
                    ->rule(function (Get $get, ?MasterGrupTes $record) {
                        return function (string $attribute, $value, Closure $fail) use ($record) {
                            $exists = MasterGrupTes::where('tanggal_tes', $value)
                                ->when($record, function ($query) use ($record) {
                                    $query->where('id', '!=', $record->id);
                                })
                                ->exists();

                            if ($exists) {
                                $fail('Tanggal dan jam ini sudah dipakai untuk grup tes lain. Silakan pilih waktu lain.');
                            }
                        };
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group_number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tanggal_tes')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('instructional_year')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ruangan_tes')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('pendaftaran_grup_tes_count')
                    ->label('Jumlah Peserta')
                    ->sortable(),
            ])
            ->defaultSort('tanggal_tes', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Input Nilai')
                    ->label('Input Nilai')
                    ->icon('heroicon-s-pencil-square')
                    ->button()
                    ->url(fn ($record) => MasterGrupTesResource::getUrl('input-nilai-grup', ['record' => $record]))
                    ->visible(fn ($record) => $record->pendaftaran_grup_tes_count > 0),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('Cetak')
                        ->url(fn ($record) => route('grup.cetak', $record->id))
                        ->openUrlInNewTab()
                        ->icon('heroicon-s-printer')
                        ->color('danger')
                        ->label('Data Grup Tes PDF'),
                    Tables\Actions\Action::make('Cetak Nilai')
                        ->url(fn ($record) => route('grup.cetak-nilai', $record->id))
                        ->openUrlInNewTab()
                        ->icon('heroicon-s-printer')
                        ->color('danger')
                        ->label('Data Nilai Tes PDF'),
                ])->label('Cetak PDF')
                    ->icon('heroicon-s-printer')
                    ->color('danger')
                    ->button()
                    ->visible(fn ($record) => $record->pendaftaran_grup_tes_count > 0),
                Tables\Actions\EditAction::make()
                    ->label(' '),
            ])
            ->defaultSort('updated_at', 'desc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('instructional_year')
                    ->label('Tahun Ajaran')
                    ->collapsible(),
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
