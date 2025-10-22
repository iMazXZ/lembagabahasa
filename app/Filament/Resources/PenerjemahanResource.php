<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenerjemahanResource\Pages;
use App\Models\Penerjemahan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use App\Support\ImageTransformer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PenerjemahanResource extends Resource
{
    protected static ?string $model = Penerjemahan::class;

    protected static ?string $navigationIcon  = 'heroicon-s-language';
    protected static ?string $navigationLabel = 'Penerjemahan Dokumen Abstrak';
    protected static ?string $navigationGroup = 'Layanan Lembaga Bahasa';

    public static ?string $slug  = 'penerjemahan';
    public static ?string $label = 'Penerjemahan Dokumen Abstrak';

    public static function getTitle(): string
    {
        return 'Penerjemahan Dokumen Abstrak';
    }

    /* -----------------------------------------------------------
    |  FORM
    |----------------------------------------------------------- */
    public static function form(Form $form): Form
    {
        // Helper lokal untuk kompres → WebP
        $compress = function (TemporaryUploadedFile $file, string $subdir = 'general', int $quality = 82, int $maxWidth = 2000) {
            $tmp = $file->store('tmp');
            $out = ImageTransformer::toWebp(
                inputPath: storage_path('app/' . $tmp),
                targetDisk: 'public',
                targetDir: "penerjemahan/images/{$subdir}",
                quality: $quality,
                maxWidth: $maxWidth
            );
            Storage::delete($tmp);
            return $out['path'];
        };

        return $form->schema([

            // Identitas pemohon (readonly placeholders)
            Forms\Components\Placeholder::make('pemohon_nama')
                ->label('Keterangan Pemohon')
                ->content(function ($record) {
                    $u = $record?->users ?: auth()->user();
                    $name = $u?->name ?? '-';
                    $prodi = $u?->prody?->name ?? '-';
                    return "{$name} — {$prodi}";
                }),

            Forms\Components\Placeholder::make('pemohon_srn')
                ->label('NPM')
                ->content(function ($record) {
                    $u = $record?->users ?: auth()->user();
                    return $u?->srn ?? '-';
                }),

            // Relasi pemohon & status default
            Forms\Components\Hidden::make('user_id')->default(fn () => auth()->id()),
            Forms\Components\Hidden::make('status')->default('Menunggu'),

            FileUpload::make('bukti_pembayaran')
                ->label('Upload Bukti Pembayaran')
                ->image()
                ->disk('public')
                ->visibility('public')
                ->acceptedFileTypes(['image/*'])
                ->maxSize(8192)
                ->downloadable()
                ->helperText('PNG/JPG hingga 8MB. Sistem otomatis mengompres ke WebP.')
                ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, $get) {
                    $nama = Str::slug(auth()->user()?->name ?? 'pemohon', '_');
                    $base = "proof_{$nama}.webp";
                    return ImageTransformer::toWebpFromUploaded(
                        uploaded: $file,
                        targetDisk: 'public',
                        targetDir: 'penerjemahan/images/payments',
                        quality: 85,
                        maxWidth: 1600,
                        maxHeight: null,
                        basename: $base
                    )['path'];
                }),

            // === TEKS SUMBER === (terlihat semua role)
            Forms\Components\Section::make('Abstrak')
                ->schema([
                    Forms\Components\RichEditor::make('source_text')
                        ->label('Masukan Abstrak Yang Ingin Diterjemahkan')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                            'paragraph',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull()
                        ->required()
                        ->disabled(function ($record) {
                            $u = auth()->user();
                            if ($u?->hasRole('pendaftar')) {
                                return filled($record); // pendaftar hanya isi saat create
                            }
                            return $u?->hasAnyRole(['Admin', 'Penerjemah', 'Staf Administrasi', 'Kepala Lembaga']);
                        })
                        ->reactive()
                        ->afterStateUpdated(function ($state, $set) {
                            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $state)));
                            $set('source_word_count', $plain === '' ? 0 : str_word_count(
                                $plain,
                                0,
                                'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ'
                            ));
                        })
                        ->hint(function ($record) {
                            $u = auth()->user();
                            if ($u?->hasRole('pendaftar') && filled($record)) {
                                return 'Teks Abstrak terkunci setelah diajukan.';
                            }
                            if ($u?->hasAnyRole(['Admin', 'Penerjemah'])) {
                                return 'read-only';
                            }
                            return null;
                        }),

                    Forms\Components\Placeholder::make('source_word_count')
                        ->label('Jumlah Kata Yang Dimasukan')
                        ->content(fn (Get $get) => $get('source_word_count') ?? 0),
                ])->collapsible(),

            // === HASIL TERJEMAHAN === (disembunyikan dari pendaftar)
            Forms\Components\Section::make('Hasil Terjemahan')
                ->schema([
                    Forms\Components\RichEditor::make('translated_text')
                        ->label('Teks Terjemahan')
                        ->toolbarButtons([
                            'bold',
                            'italic',
                            'underline',
                            'bulletList',
                            'orderedList',
                            'h2',
                            'h3',
                            'paragraph',
                            'undo',
                            'redo',
                        ])
                        ->columnSpanFull()
                        ->reactive()
                        ->disabled(function () {
                            $u = auth()->user();
                            return ! $u?->hasAnyRole(['Admin', 'Penerjemah']);
                        })
                        ->afterStateUpdated(function ($state, $set) {
                            $plain = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $state)));
                            $count = $plain === '' ? 0 : str_word_count(
                                $plain,
                                0,
                                'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿ'
                            );
                            $set('translated_word_count', $count);
                            if ($count > 0) {
                                $set('completion_date', now());
                            }
                        })
                        ->hint(function () {
                            $u = auth()->user();
                            return $u?->hasAnyRole(['Admin', 'Penerjemah'])
                                ? 'Isi/ubah hasil terjemahan di sini.'
                                : null;
                        }),

                    Forms\Components\Placeholder::make('translated_word_count')
                        ->label('Jumlah Kata (Terjemahan)')
                        ->content(fn (Get $get) => $get('translated_word_count') ?? 0),
                ])
                ->collapsible()
                ->visible(fn () => ! auth()->user()?->hasRole('pendaftar')),

            // Tanggal
            Forms\Components\DateTimePicker::make('submission_date')
                ->label('Tanggal Pengajuan')
                ->default(now())
                ->disabled()
                ->dehydrated(),

            Forms\Components\DateTimePicker::make('completion_date')
                ->label('Tanggal Selesai')
                ->disabled()
                ->dehydrated(),

            // Info Status & Penerjemah
            Forms\Components\Placeholder::make('status_badge')
                ->label('Status')
                ->content(fn (Get $get) => $get('status') ?? '-'),

            Forms\Components\Placeholder::make('translator_name')
                ->label('Nama Penerjemah')
                ->content(function (Get $get, $record) {
                    if ($get('translator_id')) {
                        if ($record?->translator) return $record->translator->name;
                        $tr = User::find($get('translator_id'));
                        return $tr?->name ?? '-';
                    }
                    return '-';
                })
                ->visible(fn (Get $get) => filled($get('translator_id'))),

            Forms\Components\Select::make('translator_id')
                ->label('Pilih Penerjemah')
                ->options(fn () => User::whereHas('roles', fn ($q) => $q->where('name', 'Penerjemah'))->pluck('name', 'id'))
                ->searchable()
                ->placeholder('Pilih penerjemah...')
                ->visible(fn () => auth()->user()?->hasRole('Admin')),
        ])->columns(2);
    }

    /* -----------------------------------------------------------
    |  TABLE
    |----------------------------------------------------------- */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('users.name')
                    ->label('Nama Pemohon')
                    ->searchable()
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi', 'Penerjemah', 'Kepala Lembaga'])),

                Tables\Columns\TextColumn::make('bukti_pembayaran')
                    ->label('Bukti')
                    ->formatStateUsing(fn ($state) => $state ? 'Lihat' : '-')
                    ->url(fn ($record) => $record->bukti_pembayaran ? Storage::url($record->bukti_pembayaran) : null)
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->placeholder('-')
                    ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi', 'Kepala Lembaga'])),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => fn (string $state) => $state === 'Menunggu',
                        'info'    => fn (string $state) => in_array($state, ['Diproses', 'Disetujui'], true),
                        'success' => fn (string $state) => $state === 'Selesai',
                        'danger'  => fn (string $state) => str_contains($state, 'Tidak Valid'),
                    ])
                    ->icons([
                        'heroicon-s-clock'        => fn (string $state) => $state === 'Menunggu',
                        'heroicon-s-cog-6-tooth'  => fn (string $state) => $state === 'Diproses',
                        'heroicon-s-check'        => fn (string $state) => $state === 'Disetujui',
                        'heroicon-s-check-circle' => fn (string $state) => $state === 'Selesai',
                        'heroicon-s-x-circle'     => fn (string $state) => str_contains($state, 'Tidak Valid'),
                    ])
                    ->iconPosition('before')
                    ->formatStateUsing(function (string $state) {
                        return str_contains($state, 'Tidak Valid')
                            ? str_replace(['Ditolak - ', ' Tidak Valid'], ['Ditolak: ', ' Invalid'], $state)
                            : $state;
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('submission_date')
                    ->label('Pengajuan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('translator.name')
                    ->label('Penerjemah')
                    ->placeholder('—')
                    ->sortable()
                    ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi', 'Penerjemah', 'Kepala Lembaga'])),

                Tables\Columns\TextColumn::make('completion_date')
                    ->label('Selesai')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Menunggu' => 'Menunggu',
                        'Disetujui' => 'Disetujui',
                        'Diproses'  => 'Diproses',
                        'Selesai'   => 'Selesai',
                        'Ditolak - Pembayaran Tidak Valid' => 'Ditolak - Pembayaran',
                        'Ditolak - Dokumen Tidak Valid'    => 'Ditolak - Dokumen',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                            ->when($data['created_until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                    }),
            ])
            ->actions([
                // ===== Unduh PDF (Admin/Staf/Kepala/Penerjemah melihat; pendaftar tidak di sini) =====
                Tables\Actions\Action::make('download_pdf')
                    ->label('Unduh PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->visible(function (Penerjemahan $record) {
                        $status = strtolower((string) $record->status);
                        $okStatus = in_array($status, ['selesai', 'disetujui', 'completed', 'approved'], true);
                        $hasOutput = filled($record->translated_text) || filled($record->final_file_path);
                        return $okStatus && $hasOutput;
                    })
                    ->url(fn (Penerjemahan $record) => route('penerjemahan.pdf', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('approve_pembayaran')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Setujui Pembayaran')
                        ->modalDescription('Yakin menyetujui pembayaran ini?')
                        ->visible(fn ($record) =>
                            auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi']) &&
                            !in_array($record->status, ['Disetujui', 'Diproses', 'Selesai'], true)
                        )
                        ->action(function ($record) {
                            $record->update(['status' => 'Disetujui']);
                            $record->users?->notify(new \App\Notifications\PenerjemahanStatusNotification('Disetujui'));
                            Notification::make()->title("Pembayaran disetujui & notifikasi terkirim ke {$record->users?->email}")->success()->send();
                        }),

                    Tables\Actions\Action::make('pilih_penerjemah')
                        ->label('Pilih Penerjemah')
                        ->icon('heroicon-o-user-plus')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('translator_id')
                                ->label('Pilih Penerjemah')
                                ->options(fn () => User::whereHas('roles', fn ($q) => $q->where('name', 'Penerjemah'))->pluck('name', 'id'))
                                ->required()
                                ->searchable(),
                        ])
                        ->visible(fn ($record) => auth()->user()?->hasRole('Admin') && $record->status === 'Disetujui')
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status'        => 'Diproses',
                                'translator_id' => $data['translator_id'],
                            ]);
                            $record->users?->notify(new \App\Notifications\PenerjemahanStatusNotification('Diproses'));
                            Notification::make()->title("Penerjemahan diproses & notifikasi terkirim ke {$record->users?->email}")->success()->send();
                        }),

                    Tables\Actions\Action::make('tolak_pembayaran')
                        ->label('Reject - Pembayaran')
                        ->icon('heroicon-m-credit-card')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Pengajuan (Pembayaran Tidak Valid)')
                        ->modalDescription('Yakin menolak karena pembayaran tidak valid?')
                        ->visible(fn ($record) =>
                            auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi']) &&
                            !in_array($record->status, ['Disetujui', 'Diproses', 'Selesai'], true)
                        )
                        ->action(function ($record) {
                            $record->update([
                                'status'        => 'Ditolak - Pembayaran Tidak Valid',
                                'translator_id' => null,
                            ]);
                            $record->users?->notify(new \App\Notifications\PenerjemahanStatusNotification('Ditolak - Pembayaran Tidak Valid'));
                            Notification::make()->title("Ditolak & notifikasi terkirim ke {$record->users?->email}")->danger()->send();
                        }),

                    Tables\Actions\Action::make('tolak_dokumen')
                        ->label('Reject - Dokumen')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Tolak Pengajuan (Dokumen Tidak Valid)')
                        ->modalDescription('Yakin menolak karena dokumen tidak valid?')
                        ->visible(fn ($record) =>
                            auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi']) &&
                            !in_array($record->status, ['Diproses', 'Selesai'], true)
                        )
                        ->action(function ($record) {
                            $record->update([
                                'status'        => 'Ditolak - Dokumen Tidak Valid',
                                'translator_id' => null,
                            ]);
                            $record->users?->notify(new \App\Notifications\PenerjemahanStatusNotification('Ditolak - Dokumen Tidak Valid'));
                            Notification::make()->title("Ditolak & notifikasi terkirim ke {$record->users?->email}")->danger()->send();
                        }),

                    Tables\Actions\Action::make('set_selesai')
                        ->label('Set Selesai')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record) =>
                            auth()->user()?->hasRole('Admin') &&
                            $record->status !== 'Selesai'
                        )
                        ->action(function ($record) {
                            $record->update([
                                'status'          => 'Selesai',
                                'completion_date' => now(),
                            ]);
                            $record->users?->notify(new \App\Notifications\PenerjemahanStatusNotification('Selesai'));
                            Notification::make()->title("Notifikasi terkirim ke {$record->users?->email}")->success()->send();
                        }),

                    // Link publik PDF (berdasar verification code)
                    Tables\Actions\Action::make('copy_public_pdf')
                        ->label('Salin Link Publik')
                        ->icon('heroicon-o-link')
                        ->visible(function (Penerjemahan $record) {
                            $status = strtolower((string) $record->status);
                            $okStatus = in_array($status, ['selesai', 'disetujui', 'completed', 'approved'], true);
                            $hasOutput = filled($record->translated_text) || filled($record->final_file_path);
                            return filled($record->verification_code) && $okStatus && $hasOutput;
                        })
                        ->action(function (Penerjemahan $record) {
                            $url = route('verification.penerjemahan.pdf', $record->verification_code);
                            Notification::make()->title('Link Publik PDF')->body($url)->success()->send();
                        }),
                ])
                ->label('Ubah Status')
                ->icon('heroicon-s-cog-6-tooth')
                ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi'])),

                // Aksi khusus Penerjemah: hanya Edit untuk mengisi terjemahan
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()->label('Isi Terjemahan')->icon('heroicon-o-pencil-square'),
                ])
                ->label('Aksi Penerjemah')
                ->icon('heroicon-s-academic-cap')
                ->visible(fn ($record) => auth()->user()?->hasRole('Penerjemah') && $record->translator_id === auth()->id()),

                // Aksi Edit standar untuk role lain (mis. pendaftar tidak melihat action admin)
                Tables\Actions\EditAction::make()
                    ->visible(fn () => ! auth()->user()?->hasAnyRole(['Admin', 'Penerjemah', 'Staf Administrasi'])),
            ])
            ->bulkActions(
                auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi'])
                    ? [
                        Tables\Actions\BulkActionGroup::make([
                            Tables\Actions\DeleteBulkAction::make(),
                        ]),
                    ]
                    : []
            );
    }

    /* -----------------------------------------------------------
    |  QUERY SCOPE (berdasarkan role)
    |----------------------------------------------------------- */
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if ($user->hasRole('pendaftar')) {
            return parent::getEloquentQuery()->where('user_id', $user->id);
        }

        if ($user->hasRole('Penerjemah')) {
            return parent::getEloquentQuery()->where('translator_id', $user->id);
        }

        return parent::getEloquentQuery();
    }

    /* -----------------------------------------------------------
    |  NAV BADGE (Admin/Staf)
    |----------------------------------------------------------- */
    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi'])) {
            return null;
        }
        $count = static::getModel()::where('status', 'Menunggu')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pemohon perlu ditinjau';
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPenerjemahans::route('/'),
            'create' => Pages\CreatePenerjemahan::route('/create'),
            'edit'   => Pages\EditPenerjemahan::route('/{record}/edit'),
        ];
    }
}
