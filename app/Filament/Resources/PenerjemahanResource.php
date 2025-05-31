<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenerjemahanResource\Pages;
use App\Filament\Resources\PenerjemahanResource\RelationManagers;
use App\Models\Penerjemahan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PenerjemahanResource extends Resource
{
    protected static ?string $model = Penerjemahan::class;

    protected static ?string $navigationIcon = 'heroicon-s-academic-cap';
    
    protected static ?string $navigationLabel = 'Penerjemahan Dokumen Abstrak';

    public static ?string $label = 'Penerjemahan Dokumen Abstrak';

    public function getTitle(): string
    {
        return 'Penerjemahan Dokumen Abstrak';
    }

    protected static ?string $navigationGroup = 'Layanan Lembaga Bahasa';

    public static function form(Form $form): Form
    {
        $user = auth()->user();

        return $form->schema([

            Forms\Components\Hidden::make('user_id')
                ->default(fn () => auth()->id()),

            Forms\Components\FileUpload::make('bukti_pembayaran')
                ->label('Upload Bukti Pembayaran')
                ->directory('bukti-pembayaran')
                ->downloadable()
                ->image()
                ->required($user->hasAnyRole(['Admin', 'pendaftar']))
                ->visible($user->hasAnyRole(['Admin', 'pendaftar']))
                ->preserveFilenames(),

            Forms\Components\FileUpload::make('dokumen_asli')
                ->label('Upload Dokumen Asli')
                ->downloadable()
                ->required($user->hasAnyRole(['Admin', 'pendaftar'])) // wajib bagi admin & user biasa
                ->visible($user->hasAnyRole(['Admin', 'pendaftar'])),

            Forms\Components\FileUpload::make('dokumen_terjemahan')
                ->label('Upload Hasil Terjemahan')
                ->directory('terjemahan')
                ->visible($user->hasRole('Penerjemah'))
                ->afterStateUpdated(function ($state, $set) {
                    if ($state) {
                        $set('completion_date', now());
                    }
                }),

            Forms\Components\DateTimePicker::make('submission_date')
                ->default(now())
                ->disabled()
                ->dehydrated(),

            Forms\Components\Select::make('translator_id')
                ->label('Pilih Penerjemah')
                ->options(function () {
                    return \App\Models\User::whereHas('roles', function ($query) {
                        $query->where('name', 'Penerjemah');
                    })->pluck('name', 'id');
                })
                ->visible($user->hasRole('Admin')),

            Forms\Components\Select::make('status')
                ->options([
                    'Menunggu' => 'Menunggu',
                    'Diproses' => 'Diproses',
                    'Selesai' => 'Selesai',
                ])
                ->visible($user->hasRole('Admin')),

            Forms\Components\DateTimePicker::make('completion_date')
                ->disabled()
                ->dehydrated()
                ->visible($user->hasRole('Penerjemah')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('users.name')
                ->label('Nama Pendaftar')
                ->searchable(),
            Tables\Columns\TextColumn::make('bukti_pembayaran')
                ->label('Bukti Pembayaran')
                ->formatStateUsing(fn ($state) => 'Bukti Bayar')
                ->url(fn ($record) => \Storage::url($record->bukti_pembayaran), true)
                ->openUrlInNewTab()
                ->limit(20)
                ->icon('heroicon-o-photo')
                ->color('danger')
                ->placeholder('-'),
            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->sortable(),
            Tables\Columns\TextColumn::make('dokumen_asli')
                ->label('Dokumen Asli')
                ->formatStateUsing(fn ($state) => 'Asli')
                ->url(fn ($record) => \Storage::url($record->dokumen_asli), true)
                ->openUrlInNewTab()
                ->limit(20)
                ->icon('heroicon-o-arrow-down-circle')
                ->color('danger'),
            Tables\Columns\TextColumn::make('submission_date')->dateTime(),
            Tables\Columns\TextColumn::make('translator.name')->label('Penerjemah'),
            Tables\Columns\TextColumn::make('dokumen_terjemahan')
                ->label('Hasil Terjemahan')
                ->formatStateUsing(fn ($state) => 'Terjemahan')
                ->url(fn ($record) => $record->dokumen_terjemahan ? \Storage::url($record->dokumen_terjemahan) : null, true)
                ->openUrlInNewTab()
                ->limit(20)
                ->placeholder('-')
                ->icon('heroicon-o-arrow-down-circle')
                ->color('success'),
            Tables\Columns\TextColumn::make('completion_date')->dateTime(),
        ])
        
        ->actions([
            Tables\Actions\EditAction::make(),
        ])

        ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Tanggal Pendaftaran')
                    ->date()
                    ->collapsible(),
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
            'index' => Pages\ListPenerjemahans::route('/'),
            'create' => Pages\CreatePenerjemahan::route('/create'),
            'edit' => Pages\EditPenerjemahan::route('/{record}/edit'),
        ];
    }
}
