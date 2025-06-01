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
use Illuminate\Support\Facades\Storage; // TAMBAHAN: Import Storage

class PenerjemahanResource extends Resource
{
    protected static ?string $model = Penerjemahan::class;

    protected static ?string $navigationIcon = 'heroicon-s-academic-cap';
    
    protected static ?string $navigationLabel = 'Penerjemahan Dokumen Abstrak';

    public static ?string $label = 'Penerjemahan Dokumen Abstrak';

    // PERBAIKAN: Method ini harus static
    public static function getTitle(): string
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
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                ->maxSize(5120)
                ->required($user->hasAnyRole(['Admin', 'pendaftar']))
                ->visible($user->hasAnyRole(['Admin', 'pendaftar']))
                ->helperText('Format: JPG, PNG. Maksimal 5MB'),

            Forms\Components\FileUpload::make('dokumen_asli')
                ->label('Upload Dokumen Asli')
                ->directory('dokumen-asli')
                ->downloadable()
                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->maxSize(10240)
                ->required($user->hasAnyRole(['Admin', 'pendaftar']))
                ->visible($user->hasAnyRole(['Admin', 'pendaftar']))
                ->helperText('Format: PDF, DOC, DOCX. Maksimal 10MB'),

            Forms\Components\FileUpload::make('dokumen_terjemahan')
                ->label('Upload Hasil Terjemahan')
                ->directory('terjemahan')
                ->downloadable()
                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->maxSize(10240)
                ->visible($user->hasRole('Penerjemah'))
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    if ($state) {
                        $set('completion_date', now());
                    }
                })
                ->helperText('Format: PDF, DOC, DOCX. Maksimal 10MB'),

            Forms\Components\DateTimePicker::make('submission_date')
                ->label('Tanggal Pengajuan')
                ->default(now())
                ->disabled()
                ->dehydrated(),

            // FIELD UNTUK ADMIN - HANYA ASSIGN PENERJEMAH (TANPA STATUS)
            Forms\Components\Select::make('translator_id')
                ->label('Pilih Penerjemah')
                ->options(function () {
                    return \App\Models\User::whereHas('roles', function ($query) {
                        $query->where('name', 'Penerjemah');
                    })->pluck('name', 'id');
                })
                ->searchable()
                ->placeholder('Pilih penerjemah...')
                ->visible($user->hasRole('Admin')),

            Forms\Components\DateTimePicker::make('completion_date')
                ->label('Tanggal Selesai')
                ->disabled()
                ->dehydrated()
                ->visible($user->hasRole('Penerjemah')),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('users.name')
                ->label('Nama Pemohon')
                ->searchable()
                ->sortable()
                ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi', 'Penerjemah'])),

            Tables\Columns\TextColumn::make('bukti_pembayaran')
                ->label('Bukti Pembayaran')
                ->formatStateUsing(fn ($state) => $state ? 'Bukti Bayar' : '-')
                ->url(fn ($record) => $record->bukti_pembayaran ? Storage::url($record->bukti_pembayaran) : null, true)
                ->openUrlInNewTab()
                ->icon('heroicon-o-photo')
                ->color('info')
                ->placeholder('-')
                ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi'])),

            Tables\Columns\BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'warning' => 'Menunggu',
                    'info' => 'Diproses', 
                    'success' => 'Selesai',
                ])
                ->sortable(),

            Tables\Columns\TextColumn::make('dokumen_asli')
                ->label('Dokumen Asli')
                ->formatStateUsing(fn ($state) => $state ? 'Lihat Dokumen' : '-')
                ->url(fn ($record) => $record->dokumen_asli ? Storage::url($record->dokumen_asli) : null, true)
                ->openUrlInNewTab()
                ->icon('heroicon-o-document')
                ->color('primary')
                ->placeholder('-')
                ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi', 'Penerjemah'])),

            Tables\Columns\TextColumn::make('submission_date')
                ->label('Pengajuan')
                ->dateTime(fn () => request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone|iPad|iPod/i', request()->header('User-Agent')) ? 'd/m' : 'd/m/Y H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('translator.name')
                ->label('Penerjemah')
                ->placeholder('Belum ditentukan')
                ->sortable()
                ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi', 'Penerjemah'])),

            Tables\Columns\TextColumn::make('completion_date')
                ->label('Selesai')
                ->dateTime(fn () => request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone|iPad|iPod/i', request()->header('User-Agent')) ? 'd/m' : 'd/m/Y H:i')
                ->placeholder('-')
                ->sortable(),
        ])
        
        ->actions([
        // ACTION DOWNLOAD HASIL
        Tables\Actions\Action::make('download_hasil')
            ->label('Download')
            ->icon('heroicon-o-arrow-down-tray')
            ->url(fn ($record) => $record->dokumen_terjemahan ? Storage::url($record->dokumen_terjemahan) : null)
            ->openUrlInNewTab()
            ->visible(fn ($record) => $record->dokumen_terjemahan !== null)
            ->color('success'),
        // ACTION GROUP UNTUK ADMIN - UBAH STATUS
        Tables\Actions\ActionGroup::make([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('set_menunggu')
                ->label('Set Menunggu')
                ->icon('heroicon-o-clock')
                ->color('warning')
                ->action(function ($record) {
                    $record->update(['status' => 'Menunggu']);
                })
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->hasRole('Admin')),
                
            Tables\Actions\Action::make('set_diproses')
                ->label('Set Diproses')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('translator_id')
                        ->label('Pilih Penerjemah')
                        ->options(function () {
                            return \App\Models\User::whereHas('roles', function ($query) {
                                $query->where('name', 'Penerjemah');
                            })->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable()
                        ->placeholder('Pilih penerjemah...'),
                ])
                ->action(function ($record, array $data) {
                    $record->update([
                        'status' => 'Diproses',
                        'translator_id' => $data['translator_id']
                    ]);
                })
                ->visible(fn () => auth()->user()->hasRole('Admin')),
                
            Tables\Actions\Action::make('set_selesai')
                ->label('Set Selesai')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function ($record) {
                    $record->update([
                        'status' => 'Selesai',
                        'completion_date' => now()
                    ]);
                })
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->dokumen_terjemahan !== null && auth()->user()->hasRole('Admin')),
                
        ])
        ->label('Ubah Status')
        ->icon('heroicon-s-cog-6-tooth')
        ->visible(fn () => auth()->user()->hasRole('Admin')),
        
        // ACTION GROUP UNTUK PENERJEMAH - HANYA EDIT
        Tables\Actions\ActionGroup::make([
            Tables\Actions\EditAction::make()
                ->label('Upload Hasil')
                ->icon('heroicon-o-arrow-up-tray'),
        ])
        ->label('Aksi Penerjemah')
        ->icon('heroicon-s-academic-cap')
        ->visible(fn ($record) => auth()->user()->hasRole('Penerjemah') && $record->translator_id === auth()->id()),
        
        // ACTION STANDALONE EDIT UNTUK ROLE LAIN
        Tables\Actions\EditAction::make()
            ->visible(fn () => !auth()->user()->hasAnyRole(['Admin', 'Penerjemah'])),
        
    ])

        // TAMBAHAN: Bulk actions
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ])

        // TAMBAHAN: Filters
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'Menunggu' => 'Menunggu',
                    'Diproses' => 'Diproses', 
                    'Selesai' => 'Selesai',
                ]),
            Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')
                        ->label('Dari Tanggal'),
                    Forms\Components\DatePicker::make('created_until')
                        ->label('Sampai Tanggal'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),
        ])

        ->groups([
            Tables\Grouping\Group::make('status')
                ->label('Status')
                ->collapsible(),
            Tables\Grouping\Group::make('created_at')
                ->label('Tanggal Pendaftaran')
                ->date()
                ->collapsible(),
        ])

        // TAMBAHAN: Default sorting
        ->defaultSort('created_at', 'desc');
    }

    // TAMBAHAN: Query scoping berdasarkan role
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        
        if ($user->hasRole('pendaftar')) {
            return parent::getEloquentQuery()->where('user_id', $user->id);
        }
        
        if ($user->hasRole('Penerjemah')) {
            return parent::getEloquentQuery()->where('translator_id', $user->id);
        }
        
        // Admin bisa lihat semua
        return parent::getEloquentQuery();
    }

    // TAMBAHAN: Navigation badge untuk admin
    public static function getNavigationBadge(): ?string
    {
        if (!auth()->user()->hasRole('Admin')) {
            return null;
        }
        $count = static::getModel()::where('status', 'Menunggu')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pemohon Perlu ditinjau';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
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