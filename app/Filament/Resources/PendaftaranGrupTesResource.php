<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendaftaranGrupTesResource\Pages;
use App\Models\PendaftaranGrupTes;
use App\Models\MasterGrupTes;
use App\Models\PendaftaranEpt;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Forms\Get;
use Filament\Forms\Set;

class PendaftaranGrupTesResource extends Resource
{
    protected static ?string $model = PendaftaranGrupTes::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-start-on-rectangle';

    protected static ?string $navigationGroup = 'Manajemen EPT';

    public static ?string $label = 'Assign Grup Tes';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pendaftaran_ept_id')
                    ->label('Pendaftar')
                    ->options(
                        PendaftaranEpt::with('users')
                            ->where('status_pembayaran', 'approved')
                            ->get()
                            ->pluck('users.name', 'id')
                    )
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('grup_tes_id')
                    ->label('Grup Tes')
                    ->options(
                        MasterGrupTes::all()->mapWithKeys(function ($grup) {
                            $count = \App\Models\PendaftaranGrupTes::where('grup_tes_id', $grup->id)->count();
                            return [
                                $grup->id => 'Grup ' . $grup->group_number . ' - ' .
                                    \Carbon\Carbon::parse($grup->tanggal_tes)->translatedFormat('d M Y') .
                                    ' (' . $count . ' Peserta)',
                            ];
                        })
                    )
                    ->searchable()
                    ->required()
                    ->disableOptionWhen(function ($value) {
                        return PendaftaranGrupTes::where('grup_tes_id', $value)->count() >= 20;
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pendaftaranEpt.users.name')->label('Nama Peserta'),
                Tables\Columns\TextColumn::make('pendaftaranEpt.users.srn')->label('NIM'),
                Tables\Columns\TextColumn::make('pendaftaranEpt.users.prody.name')->label('Prodi'),
                Tables\Columns\BadgeColumn::make('masterGrupTes.group_number')->label('Nomor Grup')->color('success')->sortable(),
                Tables\Columns\TextColumn::make('masterGrupTes.tanggal_tes')->label('Tanggal Tes')->date()->sortable(),
                Tables\Columns\TextColumn::make('masterGrupTes.ruangan_tes')->label('Ruangan Tes'),
            ])
            ->defaultSort('updated_at', 'desc')
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
            'index' => Pages\ListPendaftaranGrupTes::route('/'),
            'create' => Pages\CreatePendaftaranGrupTes::route('/create'),
            'edit' => Pages\EditPendaftaranGrupTes::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['masterGrupTes', 'pendaftaranEpt.users']);
    }
}