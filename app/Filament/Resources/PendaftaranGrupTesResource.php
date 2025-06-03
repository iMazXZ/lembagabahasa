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
                    ->options(function () {
                        return PendaftaranEpt::with(['users', 'pendaftaranGrupTes.masterGrupTes'])
                            ->where('status_pembayaran', 'approved')
                            ->get()
                            ->filter(function ($pendaftaran) {
                                return $pendaftaran->pendaftaranGrupTes->count() < 3;
                            })
                            ->mapWithKeys(function ($pendaftaran) {
                                $grupInfo = $pendaftaran->pendaftaranGrupTes->map(function ($grupTes) {
                                    return "Grup " . $grupTes->masterGrupTes->group_number;
                                })->join(', ');
                                
                                $grupText = $grupInfo ? " ($grupInfo)" : " (Tidak Masuk Grup Tes)";
                                return [$pendaftaran->id => $pendaftaran->users->name . $grupText];
                            });
                    })
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
                Tables\Columns\TextColumn::make('pendaftaranEpt.users.name')->label('Nama Peserta')->searchable(),
                Tables\Columns\TextColumn::make('pendaftaranEpt.users.srn')->label('NPM')->searchable(),
                Tables\Columns\TextColumn::make('pendaftaranEpt.users.prody.name')->label('Prodi')->searchable(),
                Tables\Columns\BadgeColumn::make('masterGrupTes.group_number')->label('Nomor Grup')->searchable()->color('success')->sortable(),
                Tables\Columns\TextColumn::make('masterGrupTes.tanggal_tes')->label('Tanggal Tes')->date()->sortable(),
                Tables\Columns\TextColumn::make('masterGrupTes.ruangan_tes')->label('Ruangan Tes'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),                
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                ])
                ->icon('heroicon-s-cog-6-tooth'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('grup_tes_id')
                    ->label('Grup Tes')
                    ->options(
                        MasterGrupTes::orderBy('tanggal_tes', 'desc')
                            ->get()
                            ->mapWithKeys(function ($grup) {
                                return [
                                    $grup->id => 'Grup ' . $grup->group_number . ' - ' .
                                        \Carbon\Carbon::parse($grup->tanggal_tes)->translatedFormat('d M Y'),
                                ];
                            })
                    )
                    ->searchable(),
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Tanggal Mendaftarkan Grup Tes')
                    ->date()
                    ->collapsible(),
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