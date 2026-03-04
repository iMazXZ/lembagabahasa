<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EptRegistrationResource\Pages;
use App\Models\EptRegistration;
use App\Models\SiteSetting;
use App\Support\LegacyBasicListeningScores;
use App\Services\WhatsAppService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EptRegistrationResource extends Resource
{
    protected static ?string $model = EptRegistration::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'EPT';
    protected static ?string $navigationLabel = 'Pendaftaran EPT';
    protected static ?string $modelLabel = 'Pendaftaran EPT';
    protected static ?string $pluralModelLabel = 'Pendaftaran EPT';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->hasAnyRole(['Admin', 'super_admin', 'Staf Administrasi'])) {
            return null;
        }
        $count = static::getModel()::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Data Pendaftar')->schema([
                Forms\Components\Placeholder::make('user_name')
                    ->label('Nama')
                    ->content(fn ($record) => $record->user->name ?? '-'),
                Forms\Components\Placeholder::make('user_srn')
                    ->label('NPM')
                    ->content(fn ($record) => $record->user->srn ?? '-'),
                Forms\Components\Placeholder::make('user_prody')
                    ->label('Program Studi')
                    ->content(fn ($record) => $record->user->prody->name ?? '-'),
                Forms\Components\Placeholder::make('student_status')
                    ->label('Status Peserta')
                    ->content(fn ($record) => $record?->student_status_label ?? '-'),
            ])->columns(3),

            Forms\Components\Section::make('Nilai Pendukung EPT')
                ->description('Ringkasan nilai biodata yang dipakai untuk evaluasi syarat EPT.')
                ->schema([
                    Forms\Components\Placeholder::make('ept_biodata_status')
                        ->label('Status Biodata EPT')
                        ->content(fn (EptRegistration $record): string => SiteSetting::isEptBiodataComplete($record->user)
                            ? 'Lengkap'
                            : 'Belum lengkap'),
                    Forms\Components\Placeholder::make('ept_requirement_scheme')
                        ->label('Skema Syarat Nilai')
                        ->content(fn (EptRegistration $record): string => static::eptRequirementScheme($record)),
                    Forms\Components\Placeholder::make('basic_listening_score')
                        ->label('Nilai Basic Listening')
                        ->content(fn (EptRegistration $record): string => static::basicListeningSummary($record)),

                    Forms\Components\Grid::make(6)
                        ->schema([
                            Forms\Components\Placeholder::make('interactive_class_1')
                                ->label('IC Sem 1')
                                ->content(fn (EptRegistration $record): string => static::scoreText($record->user?->interactive_class_1)),
                            Forms\Components\Placeholder::make('interactive_class_2')
                                ->label('IC Sem 2')
                                ->content(fn (EptRegistration $record): string => static::scoreText($record->user?->interactive_class_2)),
                            Forms\Components\Placeholder::make('interactive_class_3')
                                ->label('IC Sem 3')
                                ->content(fn (EptRegistration $record): string => static::scoreText($record->user?->interactive_class_3)),
                            Forms\Components\Placeholder::make('interactive_class_4')
                                ->label('IC Sem 4')
                                ->content(fn (EptRegistration $record): string => static::scoreText($record->user?->interactive_class_4)),
                            Forms\Components\Placeholder::make('interactive_class_5')
                                ->label('IC Sem 5')
                                ->content(fn (EptRegistration $record): string => static::scoreText($record->user?->interactive_class_5)),
                            Forms\Components\Placeholder::make('interactive_class_6')
                                ->label('IC Sem 6')
                                ->content(fn (EptRegistration $record): string => static::scoreText($record->user?->interactive_class_6)),
                        ])
                        ->columnSpanFull()
                        ->visible(fn (EptRegistration $record): bool => static::isPbiUser($record)),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Placeholder::make('interactive_bahasa_arab_1')
                                ->label('Bahasa Arab 1')
                                ->content(fn (EptRegistration $record): string => static::scoreText($record->user?->interactive_bahasa_arab_1)),
                            Forms\Components\Placeholder::make('interactive_bahasa_arab_2')
                                ->label('Bahasa Arab 2')
                                ->content(fn (EptRegistration $record): string => static::scoreText($record->user?->interactive_bahasa_arab_2)),
                        ])
                        ->columnSpanFull()
                        ->visible(fn (EptRegistration $record): bool => static::isIslamicProgramUser($record)),
                ])
                ->columns(3),

            Forms\Components\Section::make('Bukti Pembayaran')->schema([
                Forms\Components\Placeholder::make('bukti')
                    ->label('')
                    ->content(fn ($record) => view('filament.components.image-preview', [
                        'url' => Storage::url($record->bukti_pembayaran),
                    ])),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.srn')
                    ->label('NPM')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.prody.name')
                    ->label('Prodi')
                    ->limit(20),
                Tables\Columns\BadgeColumn::make('student_status')
                    ->label('Status Peserta')
                    ->color(fn (?string $state): string => match ($state) {
                        EptRegistration::STUDENT_STATUS_REGULAR => 'primary',
                        EptRegistration::STUDENT_STATUS_MAGISTER => 'warning',
                        EptRegistration::STUDENT_STATUS_KONVERSI => 'info',
                        EptRegistration::STUDENT_STATUS_GENERAL => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => filled($state) ? EptRegistration::studentStatusLabel($state) : 'Belum diisi')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('assigned_groups')
                    ->label('Grup Tes')
                    ->getStateUsing(function (EptRegistration $record): string {
                        return collect([
                            $record->grup1?->name,
                            $record->grup2?->name,
                            $record->grup3?->name,
                        ])->filter()->implode(' / ') ?: 'Belum ditetapkan';
                    })
                    ->limit(28)
                    ->tooltip(function (EptRegistration $record): ?string {
                        $groups = collect([
                            $record->grup1?->name,
                            $record->grup2?->name,
                            $record->grup3?->name,
                        ])->filter()->implode(' / ');

                        return $groups !== '' ? $groups : null;
                    }),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Tanggal Disetujui')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable()
                    ->getStateUsing(fn ($record) => $record->status === 'approved' ? $record->updated_at : null)
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
                Tables\Filters\SelectFilter::make('student_status')
                    ->label('Status Peserta')
                    ->options(EptRegistration::studentStatusOptions()),
                Tables\Filters\SelectFilter::make('assigned_group')
                    ->label('Grup Tes')
                    ->options(fn (): array => \App\Models\EptGroup::query()
                        ->select(['id', 'name'])
                        ->latest('id')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(function (Builder $query, array $data): Builder {
                        $groupId = $data['value'] ?? null;

                        if (! filled($groupId)) {
                            return $query;
                        }

                        return $query->where(function (Builder $groupQuery) use ($groupId): void {
                            $groupQuery->where('grup_1_id', $groupId)
                                ->orWhere('grup_2_id', $groupId)
                                ->orWhere('grup_3_id', $groupId);
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\Action::make('download_bukti')
                        ->label('Download Bukti')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('info')
                        ->visible(fn ($record) => !empty($record->bukti_pembayaran))
                        ->action(function ($record) {
                            $publicDisk = Storage::disk('public');

                            if (!$publicDisk->exists($record->bukti_pembayaran)) {
                                Notification::make()
                                    ->danger()
                                    ->title('File tidak ditemukan')
                                    ->send();
                                return;
                            }

                            $filePath = $publicDisk->path($record->bukti_pembayaran);
                            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                            $image = $manager->read($filePath);
                            $pngData = $image->toPng();

                            $filename = 'bukti_pembayaran_' . $record->user->srn . '_' . now()->format('Ymd') . '.png';

                            return response()->streamDownload(function () use ($pngData) {
                                echo $pngData;
                            }, $filename, [
                                'Content-Type' => 'image/png',
                            ]);
                        }),
                    Tables\Actions\Action::make('approve')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->form(function (EptRegistration $record): array {
                            $isGeneral = $record->isGeneralParticipant();
                            $groupOptions = \App\Models\EptGroup::query()
                                ->latest('id')
                                ->pluck('name', 'id')
                                ->all();

                            return [
                                Forms\Components\Placeholder::make('participant_rule')
                                    ->label('Skema Tes')
                                    ->content($isGeneral
                                        ? 'Peserta General hanya dijadwalkan ke 1 grup tes.'
                                        : 'Peserta kampus dijadwalkan ke 3 grup tes yang berbeda.'),
                                Forms\Components\Select::make('grup_1_id')
                                    ->label($isGeneral ? 'Grup Tes' : 'Grup Tes 1')
                                    ->options($groupOptions)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Forms\Components\Select::make('grup_2_id')
                                    ->label('Grup Tes 2')
                                    ->options($groupOptions)
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Grup Tes 1, 2, dan 3 wajib berbeda.')
                                    ->hidden($isGeneral)
                                    ->dehydrated(! $isGeneral)
                                    ->required(! $isGeneral),
                                Forms\Components\Select::make('grup_3_id')
                                    ->label('Grup Tes 3')
                                    ->options($groupOptions)
                                    ->searchable()
                                    ->preload()
                                    ->hidden($isGeneral)
                                    ->dehydrated(! $isGeneral)
                                    ->required(! $isGeneral),
                            ];
                        })
                        ->action(function ($record, array $data) {
                            $groupAssignments = [
                                $data['grup_1_id'] ?? null,
                            ];

                            if (! $record->isGeneralParticipant()) {
                                $groupAssignments[] = $data['grup_2_id'] ?? null;
                                $groupAssignments[] = $data['grup_3_id'] ?? null;
                            }

                            if (! EptRegistration::hasDistinctGroupAssignments($groupAssignments)) {
                                throw ValidationException::withMessages([
                                    'grup_1_id' => 'Grup Tes 1, 2, dan 3 harus berbeda.',
                                    'grup_2_id' => 'Grup Tes 1, 2, dan 3 harus berbeda.',
                                    'grup_3_id' => 'Grup Tes 1, 2, dan 3 harus berbeda.',
                                ]);
                            }

                            $record->update([
                                'status' => 'approved',
                                'grup_1_id' => $data['grup_1_id'],
                                'grup_2_id' => $record->isGeneralParticipant() ? null : ($data['grup_2_id'] ?? null),
                                'grup_3_id' => $record->isGeneralParticipant() ? null : ($data['grup_3_id'] ?? null),
                            ]);

                            $user = $record->user;
                            if ($user->whatsapp && $user->whatsapp_verified_at) {
                                try {
                                    $dashboardUrl = route('dashboard.ept-registration.index');

                                    $message = "*Pendaftaran EPT Diterima* ✅\n\n";
                                    $message .= "Yth. *{$user->name}*,\n\n";
                                    $message .= "Pembayaran Tes EPT Anda sudah kami verifikasi dan *valid*.\n\n";
                                    $message .= "Mohon menunggu penetapan jadwal tes. Ketika kuota peserta sudah terpenuhi, jadwal tes akan segera dikirimkan melalui WhatsApp.\n\n";
                                    $message .= "Silakan pantau status pendaftaran Anda di:\n{$dashboardUrl}\n\n";
                                    $message .= "_Terima kasih telah mendaftar._";

                                    app(WhatsAppService::class)->sendMessage($user->whatsapp, $message);
                                } catch (\Exception $e) {
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title('Pendaftaran Disetujui')
                                ->body('Peserta berhasil ditambahkan ke grup. Notifikasi WA telah dikirim.')
                                ->send();
                        }),
                    Tables\Actions\Action::make('reject')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === 'pending')
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Alasan Penolakan')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'status' => 'rejected',
                                'rejection_reason' => $data['rejection_reason'],
                            ]);

                            $user = $record->user;
                            $waSent = false;
                            $waReason = '';

                            if (!$user->whatsapp) {
                                $waReason = 'Nomor WA belum diisi';
                            } elseif (!$user->whatsapp_verified_at) {
                                $waReason = 'Nomor WA belum diverifikasi';
                            } else {
                                try {
                                    $dashboardUrl = route('dashboard.ept-registration.index');

                                    $message = "*Pendaftaran EPT Ditolak*\n\n";
                                    $message .= "Yth. *{$user->name}*,\n\n";
                                    $message .= "Mohon maaf, pendaftaran Tes EPT Anda *tidak dapat diproses*.\n\n";
                                    $message .= "*Alasan:*\n{$data['rejection_reason']}\n\n";
                                    $message .= "Silakan upload ulang bukti pembayaran yang valid melalui link berikut:\n{$dashboardUrl}\n\n";
                                    $message .= "_Terima kasih atas pengertiannya._";

                                    $result = app(WhatsAppService::class)->sendMessage($user->whatsapp, $message);
                                    $waSent = $result;
                                    if (!$result) {
                                        $waReason = 'Gagal mengirim (API error)';
                                        \Illuminate\Support\Facades\Log::warning('EPT Rejection WA failed', [
                                            'user_id' => $user->id,
                                            'whatsapp' => $user->whatsapp,
                                        ]);
                                    }
                                } catch (\Exception $e) {
                                    $waReason = 'Exception: ' . $e->getMessage();
                                    \Illuminate\Support\Facades\Log::error('EPT Rejection WA exception', [
                                        'user_id' => $user->id,
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }

                            if ($waSent) {
                                Notification::make()
                                    ->warning()
                                    ->title('Pendaftaran Ditolak')
                                    ->body('Notifikasi penolakan terkirim via WA.')
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('Pendaftaran Ditolak')
                                    ->body("WA tidak terkirim: {$waReason}")
                                    ->send();
                            }
                        }),
                ])
                    ->label('')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('preview_export_bukti')
                        ->label('Preview Export Bukti')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->modalHeading('Preview export bukti pembayaran')
                        ->modalDescription('Data terpilih akan dibawa ke layout designer untuk crop, susun, dan atur jumlah foto per halaman.')
                        ->action(function (Collection $records) {
                            $ids = $records->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

                            if (empty($ids)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Tidak ada data dipilih')
                                    ->send();

                                return null;
                            }

                            return redirect()->to(route('admin.ept-registration-export-bukti.preview', [
                                'ids' => $ids,
                            ]));
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'user.prody',
                'grup1:id,name',
                'grup2:id,name',
                'grup3:id,name',
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEptRegistrations::route('/'),
            'view' => Pages\ViewEptRegistration::route('/{record}'),
        ];
    }

    protected static function eptRequirementScheme(EptRegistration $record): string
    {
        $user = $record->user;
        if (! $user) {
            return 'Data user tidak tersedia.';
        }

        $prodyName = $user?->prody?->name ?? '';
        $year = (int) ($user?->year ?? 0);

        if (! $year || $year > 2024) {
            return 'Tidak memakai syarat nilai legacy.';
        }

        if ($prodyName !== '' && str_starts_with($prodyName, 'S2')) {
            return 'Mahasiswa S2 tidak memakai syarat nilai legacy.';
        }

        if ($prodyName === 'Pendidikan Bahasa Inggris') {
            return 'Wajib Interactive Bahasa Inggris semester 1 sampai 6.';
        }

        if (in_array($prodyName, [
            'Komunikasi dan Penyiaran Islam',
            'Pendidikan Agama Islam',
            'Pendidikan Islam Anak Usia Dini',
        ], true)) {
            return 'Wajib Basic Listening dan Interactive Bahasa Arab 1-2.';
        }

        if (in_array(strtolower(trim($prodyName)), ['umum', 'program studi umum'], true)) {
            return 'Program Studi Umum tidak memakai Basic Listening.';
        }

        return 'Wajib Basic Listening.';
    }

    protected static function basicListeningSummary(EptRegistration $record): string
    {
        $user = $record->user;
        if (! $user) {
            return 'Belum tersedia';
        }

        $prodyName = $user?->prody?->name ?? null;
        $year = (int) ($user?->year ?? 0);

        if (! LegacyBasicListeningScores::requiresLegacyScore($year, $prodyName)) {
            return 'Tidak diwajibkan';
        }

        return static::scoreText(LegacyBasicListeningScores::effectiveScoreForUser($user));
    }

    protected static function isPbiUser(EptRegistration $record): bool
    {
        $user = $record->user;

        return (int) ($user?->year ?? 0) > 0
            && (int) ($user?->year ?? 0) <= 2024
            && ($user?->prody?->name ?? null) === 'Pendidikan Bahasa Inggris';
    }

    protected static function isIslamicProgramUser(EptRegistration $record): bool
    {
        $user = $record->user;

        return (int) ($user?->year ?? 0) > 0
            && (int) ($user?->year ?? 0) <= 2024
            && in_array($user?->prody?->name ?? null, [
                'Komunikasi dan Penyiaran Islam',
                'Pendidikan Agama Islam',
                'Pendidikan Islam Anak Usia Dini',
            ], true);
    }

    protected static function scoreText(mixed $value): string
    {
        if (! is_numeric($value) || (float) $value <= 0) {
            return 'Belum tersedia';
        }

        $score = (float) $value;

        return fmod($score, 1.0) === 0.0
            ? (string) (int) $score
            : number_format($score, 2, ',', '.');
    }
}
