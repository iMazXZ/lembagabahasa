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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Filament\Tables\Columns\ViewColumn;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;

class PenerjemahanResource extends Resource
{
    protected static ?string $model = Penerjemahan::class;

    protected static ?string $navigationIcon = 'heroicon-s-academic-cap';
    
    protected static ?string $navigationLabel = 'Penerjemahan Dokumen Abstrak';

    public static ?string $slug = 'penerjemahan';

    public static ?string $label = 'Penerjemahan Dokumen Abstrak';

    public static function getTitle(): string
    {
        return 'Penerjemahan Dokumen Abstrak';
    }

    protected static ?string $navigationGroup = 'Layanan Lembaga Bahasa';

    public static function form(Form $form): Form
    {
        $user = auth()->user();

        return $form->schema([
            Forms\Components\Placeholder::make('name')
                ->label('Keterangan Pemohon')
                ->content(function ($record) {
                        $user = $record?->users;
                        $name = $user?->name ?? '-';
                        $prodi = $user?->prody?->name ?? '-';
                        return "{$name} - {$prodi}";
                    }),

            Forms\Components\Placeholder::make('srn')
                ->label('Nomor Pokok Mahasiswa')
                ->content(fn ($record) => $record?->users?->srn ?? '-'),

            Forms\Components\Hidden::make('user_id')
                ->default(fn () => auth()->id()),

            Forms\Components\Hidden::make('status')
                ->default(fn () => 'Menunggu'),

            Forms\Components\FileUpload::make('bukti_pembayaran')
                ->label('Upload Bukti Pembayaran')
                ->directory('bukti-penerjemahan')
                ->downloadable()
                ->image()
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                ->maxSize(2048)
                ->required()
                ->validationMessages([
                    'required' => 'Foto bukti pembayaran wajib diunggah.',
                ])
                ->visible($user->hasAnyRole(['Admin', 'pendaftar']))
                ->reactive()
                ->afterStateUpdated(function ($state, $set, $get) {
                    if ($state && $get('status') === 'Ditolak - Pembayaran Tidak Valid') {
                        $set('status', 'Menunggu');
                    }
                })
                ->helperText('Pastikan file dalam format gambar (JPG/PNG) dan ukuran tidak lebih dari 2MB.'),

            Forms\Components\FileUpload::make('dokumen_asli')
                ->label('Upload Dokumen Asli')
                ->directory('dokumen-asli')
                ->downloadable()
                ->acceptedFileTypes([
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ])
                ->maxSize(10240)
                ->required()
                ->validationMessages([
                    'required' => 'Dokumen Abstrak Asli wajib diunggah.',
                ])
                ->visible($user->hasAnyRole(['Admin', 'pendaftar']))
                ->reactive()
                ->afterStateUpdated(function ($state, $set, $get) {
                    if ($state && $get('status') === 'Ditolak - Dokumen Tidak Valid') {
                        $set('status', 'Menunggu');
                    }
                })
                ->helperText('Format: DOC atau DOCX. Maksimal 10MB'),

            Forms\Components\FileUpload::make('dokumen_terjemahan')
                ->label('Upload Hasil Terjemahan')
                ->directory('hasil-terjemahan')
                ->downloadable()
                ->acceptedFileTypes(['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                ->maxSize(10240)
                ->visible($user->hasAnyRole(['Admin', 'Penerjemah']))
                ->reactive()
                ->afterStateUpdated(function ($state, $set) {
                    if ($state) {
                        $set('completion_date', now());
                    }
                })
                ->helperText('Format: DOC atau DOCX. Maksimal 10MB'),

            Forms\Components\DateTimePicker::make('submission_date')
                ->label('Tanggal Pengajuan')
                ->default(now())
                ->disabled()
                ->dehydrated(),

            Forms\Components\Placeholder::make('status_badge')
                ->label('Status')
                ->content(fn ($get) => $get('status') ?? '-'),

            Forms\Components\Placeholder::make('translator_name')
                ->label('Nama Penerjemah')
                ->content(function ($get, $record) {
                    if ($get('translator_id')) {
                        // Jika sedang edit, ambil dari relasi jika ada
                        if ($record && $record->translator) {
                            return $record->translator->name;
                        }
                        // Jika create, ambil dari User model
                        $translator = \App\Models\User::find($get('translator_id'));
                        return $translator?->name ?? '-';
                    }
                    return '-';
                })
                ->visible(fn ($get) => !empty($get('translator_id'))),

            // FIELD UNTUK ADMIN - ASSIGN PENERJEMAH
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
                ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi', 'Penerjemah', 'Kepala Lembaga'])),

            Tables\Columns\TextColumn::make('bukti_pembayaran')
                ->label('Bukti Pembayaran')
                ->formatStateUsing(fn ($state) => $state ? 'Bukti Bayar' : '-')
                ->url(fn ($record) => $record->bukti_pembayaran ? Storage::url($record->bukti_pembayaran) : null, true)
                ->openUrlInNewTab()
                ->icon('heroicon-o-photo')
                ->color('info')
                ->placeholder('-')
                ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi', 'Kepala Lembaga'])),

            Tables\Columns\BadgeColumn::make('status')
                ->label('Status')
                ->colors([
                    'warning' => fn ($state) => $state === 'Menunggu',
                    'info' => fn ($state) => $state === 'Diproses',
                    'info' => fn ($state) => $state === 'Disetujui',
                    'success' => fn ($state) => $state === 'Selesai', 
                    'danger' => fn ($state) => str_contains($state, 'Tidak Valid'),
                ])
                ->icons([
                    'heroicon-s-clock' => fn ($state) => $state === 'Menunggu',
                    'heroicon-s-cog-6-tooth' => fn ($state) => $state === 'Diproses',
                    'heroicon-s-check' => fn ($state) => $state === 'Disetujui',
                    'heroicon-s-check-circle' => fn ($state) => $state === 'Selesai',
                    'heroicon-s-x-circle' => fn ($state) => str_contains($state, 'Tidak Valid'),
                ])
                ->iconPosition('before')
                ->formatStateUsing(function ($state) {
                    if (str_contains($state, 'Tidak Valid')) {
                        return str_replace(['Ditolak - ', ' Tidak Valid'], ['Ditolak: ', ' Invalid'], $state);
                    }
                    return $state;
                })
                ->sortable(),

            Tables\Columns\TextColumn::make('dokumen_asli')
                ->label('Dokumen Asli')
                ->formatStateUsing(fn ($state) => $state ? 'Lihat Dokumen' : '-')
                ->url(fn ($record) => $record->dokumen_asli ? Storage::url($record->dokumen_asli) : null, true)
                ->openUrlInNewTab()
                ->icon('heroicon-o-document')
                ->color('primary')
                ->placeholder('-')
                ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi', 'Penerjemah', 'Kepala Lembaga'])),

            Tables\Columns\TextColumn::make('submission_date')
                ->label('Pengajuan')
                ->dateTime(fn () => request()->header('User-Agent') && preg_match('/Mobile|Android|iPhone|iPad|iPod/i', request()->header('User-Agent')) ? 'd/m' : 'd/m/Y H:i')
                ->sortable(),

            Tables\Columns\TextColumn::make('translator.name')
                ->label('Penerjemah')
                ->placeholder('Belum ditentukan')
                ->sortable()
                ->visible(fn () => auth()->user()->hasRole(['Admin', 'Staf Administrasi', 'Penerjemah', 'Kepala Lembaga'])),

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
                ->visible(fn ($record) =>
                    $record->dokumen_terjemahan !== null &&
                    (
                        // Tampil ke pendaftar hanya jika status sudah 'Selesai'
                        (auth()->user()->hasRole('pendaftar') && $record->status === 'Selesai')
                        // Tampil ke admin, staf, penerjemah kapan saja dokumen_terjemahan ada
                        || auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi', 'Penerjemah'])
                    )
                )
                ->color('success'),

            // ACTION GROUP UNTUK ADMIN - UBAH STATUS
            Tables\Actions\ActionGroup::make([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('approve_pembayaran')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function ($record) {
                        $record->update(['status' => 'Disetujui']);

                        $record->users->notify(new \App\Notifications\PenerjemahanStatusNotification('Disetujui'));

                        Notification::make()
                            ->title("Pembayaran Disetujui dan Notifikasi Sudah Terkirim ke Email {$record->users->email}")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pembayaran')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui pembayaran ini?')
                    ->visible(fn ($record) =>
                        auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi']) &&
                        $record->status !== 'Disetujui' &&
                        $record->status !== 'Diproses' &&
                        $record->status !== 'Selesai'
                    ),

                Tables\Actions\Action::make('pilih_penerjemah')
                    ->label('Pilih Penerjemah')
                    ->icon('heroicon-o-user-plus')
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

                        $record->users->notify(new \App\Notifications\PenerjemahanStatusNotification('Diproses'));

                        Notification::make()
                            ->title("Penerjemahan Diproses dan Notifikasi Sudah Terkirim ke Email {$record->users->email}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => auth()->user()->hasRole('Admin') && $record->status === 'Disetujui'),


                // ACTION UNTUK MENOLAK PEMBAYARAN
                Tables\Actions\Action::make('tolak_pembayaran')
                    ->label('Reject - Pembayaran')
                    ->icon('heroicon-m-credit-card')
                    ->color('danger')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'Ditolak - Pembayaran Tidak Valid',
                            'translator_id' => null // Clear translator assignment
                        ]);

                        $record->users->notify(new \App\Notifications\PenerjemahanStatusNotification(
                            'Ditolak - Pembayaran Tidak Valid'
                        ));

                        Notification::make()
                            ->title("Ditolak dan Notifikasi Sudah Terkirim ke Email {$record->users->email}")
                            ->danger()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengajuan - Pembayaran Tidak Valid')
                    ->modalDescription('Apakah Anda yakin pembayaran tidak valid dan ingin menolak pengajuan ini?')
                    ->visible(fn ($record) =>
                        auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi']) &&
                        $record->status !== 'Disetujui' &&
                        $record->status !== 'Diproses' &&
                        $record->status !== 'Selesai'
                    ),

                // ACTION UNTUK MENOLAK DOKUMEN
                Tables\Actions\Action::make('tolak_dokumen')
                    ->label('Reject - Dokumen')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'Ditolak - Dokumen Tidak Valid',
                            'translator_id' => null // Clear translator assignment
                        ]);

                        $record->users->notify(new \App\Notifications\PenerjemahanStatusNotification(
                            'Ditolak - Dokumen Tidak Valid'
                        ));

                        Notification::make()
                            ->title("Ditolak dan Notifikasi Sudah Terkirim ke Email {$record->users->email}")
                            ->danger()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Tolak Pengajuan - Dokumen Tidak Valid')
                    ->modalDescription('Apakah Anda yakin dokumen tidak valid dan ingin menolak pengajuan ini?')
                    ->visible(fn ($record) =>
                        auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi']) &&
                        $record->status !== 'Diproses' &&
                        $record->status !== 'Selesai'
                    ),
                    
                Tables\Actions\Action::make('set_selesai')
                    ->label('Set Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'Selesai',
                            'completion_date' => now()
                        ]);

                        $record->users->notify(new \App\Notifications\PenerjemahanStatusNotification(
                            'Selesai'
                        ));

                        Notification::make()
                            ->title("Notifikasi Sudah Terkirim ke Email {$record->users->email}")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->visible(fn ($record) =>
                        $record->dokumen_terjemahan !== null &&
                        auth()->user()->hasRole('Admin') &&
                        $record->status !== 'Selesai' &&
                        $record->status !== 'Ditolak - Dokumen Tidak Valid' &&
                        $record->status !== 'Ditolak - Pembayaran Tidak Valid'
                    ),
                    
            ])
            ->label('Ubah Status')
            ->icon('heroicon-s-cog-6-tooth')
            ->visible(fn () => auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi'])),
            
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
                ->visible(fn () => !auth()->user()->hasAnyRole(['Admin', 'Penerjemah', 'Staf Administrasi'])),
        ])

        // BULK ACTIONS
        ->bulkActions(
            auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi'])
                ? [
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
                    Tables\Actions\BulkAction::make('validasiPembayaran')
                        ->label('Pilih Status')
                        ->icon('heroicon-s-check-circle')
                        ->color('primary')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Set Status Pembayaran')
                                ->options([
                                    'Menunggu' => 'Menunggu',
                                    'Disetujui' => 'Disetujui',
                                    'Diproses' => 'Diproses',
                                    'Selesai' => 'Selesai',
                                    'Ditolak - Pembayaran Tidak Valid' => 'Ditolak - Pembayaran Tidak Valid',
                                    'Ditolak - Dokumen Tidak Valid' => 'Ditolak - Dokumen Tidak Valid',
                                ])
                                ->required()
                                ->reactive(),

                            Forms\Components\Select::make('translator_id')
                                ->label('Pilih Penerjemah')
                                ->options(function () {
                                    return \App\Models\User::whereHas('roles', function ($query) {
                                        $query->where('name', 'Penerjemah');
                                    })->pluck('name', 'id');
                                })
                                ->searchable()
                                ->placeholder('Pilih penerjemah...')
                                ->visible(fn ($get) => $get('status') === 'Diproses')
                                ->required(fn ($get) => $get('status') === 'Diproses'),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $updateData = [
                                    'status' => $data['status'],
                                ];
                                if ($data['status'] === 'Diproses' && !empty($data['translator_id'])) {
                                    $updateData['translator_id'] = $data['translator_id'];
                                } elseif ($data['status'] !== 'Diproses') {
                                    $updateData['translator_id'] = null;
                                }
                                $record->update($updateData);
                            }

                            Notification::make()
                                ->title('Status pembayaran berhasil diperbarui.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]
                : []
        )

        // FILTERS
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'Menunggu' => 'Menunggu',
                    'Diproses' => 'Diproses', 
                    'Selesai' => 'Selesai',
                    'Ditolak - Pembayaran Tidak Valid' => 'Ditolak - Pembayaran',
                    'Ditolak - Dokumen Tidak Valid' => 'Ditolak - Dokumen',
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

        // DEFAULT SORTING
        ->defaultSort('created_at', 'desc');
    }

    // QUERY SCOPING BERDASARKAN ROLE
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

    // NAVIGATION BADGE UNTUK ADMIN dan STAF ADMINISTRASI
    public static function getNavigationBadge(): ?string
    {
        if (!auth()->user()->hasAnyRole(['Admin', 'Staf Administrasi'])) {
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