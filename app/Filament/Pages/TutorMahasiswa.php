<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\Prody;
use App\Models\BasicListeningGrade;
use App\Models\BasicListeningManualScore;
use App\Support\BlCompute;
use App\Support\BlGrading;

use Filament\Forms;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Pages\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Filament\Tables\Actions\ActionGroup;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TutorMahasiswaTemplateExport;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class TutorMahasiswa extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon   = 'heroicon-o-users';
    protected static ?string $navigationLabel  = 'Mahasiswa Binaan';
    protected static ?string $title            = 'Mahasiswa Binaan';
    protected static ?string $navigationGroup  = 'Basic Listening';
    protected static ?int    $navigationSort   = 2;

    protected static string $view = 'filament.pages.tutor-mahasiswa';

    /** Hanya Admin (A besar) atau Tutor (t kecil). */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->hasRole('Admin') || $user?->hasRole('tutor');
    }

    public function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Pages\Widgets\TutorMahasiswaSummaryStats::class,
        ];
    }
    
    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->query($this->baseQuery($user))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('srn')
                    ->label('NPM')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('prody.name')
                    ->label('Prodi')
                    ->sortable()
                    ->limit(15)
                    ->badge()
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('nomor_grup_bl')
                    ->label('Grup BL')
                    ->badge()
                    ->alignCenter()
                    ->width('6rem')
                    ->extraCellAttributes(['class' => 'px-2'])
                    ->formatStateUsing(fn ($state) => $state ? "Grup {$state}" : '—'),

                Tables\Columns\TextColumn::make('attempt_status')
                    ->label('Daily Online')
                    ->badge()
                    ->state(function (User $record) {
                        // Hitung berapa sesi (S1–S5) yang sudah submit
                        $count = $record->basicListeningAttempts()
                            ->whereNotNull('submitted_at')
                            ->distinct('session_id')
                            ->count('session_id');

                        if ($count === 0) {
                            return 'Belum attempt';
                        }

                        return $count . ' ' . Str::plural('daily', $count);
                    })
                    ->colors([
                        // 0 = abu-abu, 1-4 = warning, 5 = hijau
                        'gray'    => fn (string $state): bool =>
                            str_starts_with(strtolower($state), 'belum'),
                        'success' => fn (string $state): bool =>
                            ((int) (explode(' ', $state)[0] ?? 0)) === 5,
                        'warning' => fn (string $state): bool => 
                            (($n = (int) (explode(' ', $state)[0] ?? 0)) > 0 && $n < 5),
                    ])
                    ->toggleable(),


                // Attendance
                Tables\Columns\TextColumn::make('attendance_display')
                    ->label('Attendance')
                    ->alignCenter()
                    ->state(fn (User $record) => is_numeric(optional($record->basicListeningGrade)->attendance)
                        ? (string) $record->basicListeningGrade->attendance : '—')
                    ->toggleable(),

                // Daily
                Tables\Columns\TextColumn::make('daily_display')
                    ->label('Daily')
                    ->alignCenter()
                    ->state(function (User $record) {
                        $avg = BlCompute::dailyAvgForUser($record->id, $record->year);
                        return is_null($avg) ? '—' : number_format($avg, 2);
                    })
                    ->toggleable(),

                // Final Test
                Tables\Columns\TextColumn::make('final_test_display')
                    ->label('Final Test')
                    ->alignCenter()
                    ->state(fn (User $record) => is_numeric(optional($record->basicListeningGrade)->final_test)
                        ? (string) $record->basicListeningGrade->final_test : '—')
                    ->toggleable(),

                // Total Score (pakai cache dulu, fallback (A + D + F) / 3)
                Tables\Columns\TextColumn::make('final_numeric')
                    ->label('Total')
                    ->alignCenter()
                    ->state(function (User $record) {
                        $g = $record->basicListeningGrade;

                        if ($g?->final_numeric_cached !== null) {
                            return number_format($g->final_numeric_cached, 2);
                        }

                        $att = is_numeric($g?->attendance) ? (float) $g->attendance : null;
                        $fin = is_numeric($g?->final_test)  ? (float) $g->final_test  : null;
                        $dly = BlCompute::dailyAvgForUser($record->id, $record->year);
                        $dly = is_numeric($dly) ? (float) $dly : null;

                        $parts = array_values(array_filter([$att, $dly, $fin], fn ($v) => $v !== null));
                        if (! $parts) return '—';

                        $n = round(array_sum($parts) / count($parts), 2);
                        return number_format($n, 2);
                    })
                    ->toggleable(),

                // Grade
                Tables\Columns\TextColumn::make('final_letter')
                    ->label('Grade')
                    ->badge()
                    ->alignCenter()
                    ->state(function (User $record) {
                        $g = $record->basicListeningGrade;

                        if ($g?->final_letter_cached) {
                            return $g->final_letter_cached;
                        }

                        $att = is_numeric($g?->attendance) ? (float) $g->attendance : null;
                        $fin = is_numeric($g?->final_test)  ? (float) $g->final_test  : null;
                        $dly = BlCompute::dailyAvgForUser($record->id, $record->year);
                        $dly = is_numeric($dly) ? (float) $dly : null;

                        $parts = array_values(array_filter([$att, $dly, $fin], fn ($v) => $v !== null));
                        if (! $parts) return '—';

                        $n = round(array_sum($parts) / count($parts), 2);
                        return BlGrading::letter($n);
                    })
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['A', 'A-', 'B+', 'B'], true),
                        'warning' => fn ($state) => in_array($state, ['B-', 'C+', 'C'], true),
                        'danger'  => fn ($state) => in_array($state, ['C-', 'D', 'E'], true),
                    ])
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_attempt_at')
                    ->label('Attempt Terakhir')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->state(function (User $record) {
                        $last = $record->basicListeningAttempts()
                            ->latest('updated_at')
                            ->select(['id', 'submitted_at', 'updated_at'])
                            ->first();

                        return $last?->submitted_at ?? $last?->updated_at;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // ================== Prefix Angkatan (SRN) ==================
                Filter::make('angkatan')
                    ->label('Prefix Angkatan (SRN)')
                    ->form([
                        Forms\Components\TextInput::make('prefix')
                            ->placeholder('mis. 25')
                            ->default('25')
                            ->maxLength(4) // sedikit lebih longgar, tapi tetap aman
                            ->datalist(['25', '24', '23']),
                    ])
                    ->indicateUsing(fn (array $data): ?string =>
                        filled($data['prefix'] ?? null)
                            ? 'Angkatan: ' . trim((string) $data['prefix'])
                            : null
                    )
                    ->query(function (Builder $query, array $data) {
                        $prefix = trim((string) ($data['prefix'] ?? ''));
                        if ($prefix !== '') {
                            $query->where('srn', 'like', $prefix . '%');
                        }
                    }),

                // ================== Prodi ==================
                Tables\Filters\SelectFilter::make('prody_id')
                    ->label('Prodi')
                    ->options(function () use ($user) {
                        // Tutor: batasi ke prodi binaan (dengan guard)
                        if ($user?->hasRole('tutor')) {
                            $ids = method_exists($user, 'assignedProdyIds') ? (array) $user->assignedProdyIds() : [];
                            if (empty($ids)) {
                                // tidak ada prodi binaan → kosongkan opsi
                                return [];
                            }

                            return Prody::query()
                                ->whereIn('id', $ids)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        }

                        // Admin (atau role lain yg diizinkan): semua prodi
                        return Prody::query()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Prodi')
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if (filled($value)) {
                            $query->where('prody_id', $value);
                        }
                    }),

                // ================== Grup BL ==================
                Tables\Filters\SelectFilter::make('nomor_grup_bl')
                    ->label('Grup BL')
                    ->options(function () use ($user) {
                        // Tentukan daftar prodi yang diperbolehkan
                        $allowedProdyIds = [];

                        if ($user?->hasRole('tutor')) {
                            $allowedProdyIds = method_exists($user, 'assignedProdyIds') ? (array) $user->assignedProdyIds() : [];
                            if (empty($allowedProdyIds)) {
                                return []; // tutor belum punya prodi binaan
                            }
                        }

                        $q = \App\Models\User::query()
                            ->when(!empty($allowedProdyIds), function (Builder $qb) use ($allowedProdyIds) {
                                $qb->whereIn('prody_id', $allowedProdyIds);
                            })
                            ->whereNotNull('nomor_grup_bl')
                            ->distinct()
                            ->orderBy('nomor_grup_bl');

                        return $q->pluck('nomor_grup_bl', 'nomor_grup_bl')->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->placeholder('Semua Grup')
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if (filled($value)) {
                            $query->where('nomor_grup_bl', $value);
                        }
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    // === Atur Grup BL (modal searchable, ringkas di UI) ===
                    Tables\Actions\Action::make('atur_grup_bl')
                        ->label('Atur Grup')
                        ->icon('heroicon-o-user-group')
                        ->visible(fn (User $record) =>
                            auth()->user()?->hasRole('Admin') ||
                            (auth()->user()?->hasRole('tutor') &&
                            in_array($record->prody_id, auth()->user()->assignedProdyIds() ?? []))
                        )
                        ->form([
                            Forms\Components\Select::make('nomor_grup_bl')
                                ->label('Pilih Grup')
                                ->options(fn () =>
                                    User::query()
                                        ->whereNotNull('nomor_grup_bl')
                                        ->distinct()
                                        ->orderBy('nomor_grup_bl')
                                        ->pluck('nomor_grup_bl', 'nomor_grup_bl')
                                        ->toArray()
                                )
                                ->searchable()
                                ->preload()
                                ->placeholder('Pilih grup')
                                ->required(),
                        ])
                        ->action(function (User $record, array $data) {
                            $me = auth()->user();
                            if ($me?->hasRole('tutor') && ! in_array($record->prody_id, $me->assignedProdyIds() ?? [])) {
                                abort(403, 'Anda tidak diizinkan mengubah grup mahasiswa ini.');
                            }

                            $record->update(['nomor_grup_bl' => $data['nomor_grup_bl']]);

                            \Filament\Notifications\Notification::make()
                                ->title('Grup BL diperbarui')
                                ->success()
                                ->send();
                        })
                        ->modalWidth('sm'),

                    // === Edit Daily (tetap seperti punyamu) ===
                    Tables\Actions\Action::make('edit_daily')
                        ->label('Edit Daily')
                        ->icon('heroicon-o-pencil')
                        ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'tutor']))
                        ->form(function (User $record) {
                            // === Status attempt & nilai manual per meeting
                            $attemptStatus = [];
                            $currentScores = [];
                            $attemptScores = [];

                            // Ambil attempt terbaru per meeting
                            $attempts = $record->basicListeningAttempts()
                                ->whereNotNull('submitted_at')
                                ->select(['user_id', 'session_id', 'score', 'submitted_at'])
                                ->orderByDesc('submitted_at')
                                ->get()
                                ->groupBy('session_id')
                                ->map(function ($g) {
                                    return $g->first();
                                });

                            foreach ([1,2,3,4,5] as $meeting) {
                                $attempt = $attempts->get($meeting);
                                $hasAttempt = !is_null($attempt);

                                $attemptStatus[$meeting] = $hasAttempt;
                                $attemptScores[$meeting] = $hasAttempt ? $attempt->score : null;

                                $manualScore = BasicListeningManualScore::query()
                                    ->where('user_id', $record->id)
                                    ->where('user_year', $record->year)
                                    ->where('meeting', $meeting)
                                    ->value('score');

                                $currentScores[$meeting] = $manualScore;
                            }

                            // === UI
                            return [
                                Forms\Components\Placeholder::make('info')
                                    ->label('Untuk Apa Ini?')
                                    ->content(function () use ($attemptStatus) {
                                        $lockedMeetings = [];
                                        foreach ($attemptStatus as $meeting => $hasAttempt) {
                                            if ($hasAttempt) $lockedMeetings[] = "S{$meeting}";
                                        }
                                        if (!empty($lockedMeetings)) {
                                            return "Form ini digunakan untuk input nilai untuk setiap meeting (1-5) ketika tidak menggunakan fitur quiz. Nilai Meeting " . implode(', ', $lockedMeetings) . " terkunci karena sudah mengikuti Quiz Online.";
                                        }
                                        return "Form ini digunakan untuk input nilai untuk setiap meeting (1-5) ketika tidak menggunakan fitur quiz.";
                                    })
                                    ->extraAttributes(['class' => 'bg-gray-50 p-4 rounded']),

                                Forms\Components\Grid::make(5)->schema([
                                    // M1
                                    TextInput::make('m1')
                                        ->label('Meeting 1')
                                        ->numeric()->minValue(0)->maxValue(100)
                                        ->helperText(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[1];  // nilai quiz asli (jika ada)
                                            $man  = $currentScores[1];  // nilai manual (jika ada)
                                            if ($attemptStatus[1]) {
                                                if (is_numeric($man) && $man !== $orig) {
                                                    return "Override: Quiz {$orig} → Manual {$man}";
                                                }
                                                return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                            }
                                            return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                        })
                                        ->suffixIcon(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[1]; $man = $currentScores[1];
                                            if ($attemptStatus[1] && is_numeric($man) && $man !== $orig) return 'heroicon-o-adjustments-horizontal';
                                            return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                        })
                                        ->suffixIconColor(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[1]; $man = $currentScores[1];
                                            if ($attemptStatus[1] && is_numeric($man) && $man !== $orig) return 'warning';
                                            return is_numeric($man) ? 'success' : 'info';
                                        })
                                        ->placeholder(
                                            is_numeric($currentScores[1])
                                                ? "Nilai sekarang: {$currentScores[1]}"
                                                : (is_numeric($attemptScores[1]) ? "Nilai quiz: {$attemptScores[1]}" : '0-100')
                                        )
                                        ->default(
                                            is_numeric($currentScores[1]) ? $currentScores[1]
                                                : (is_numeric($attemptScores[1]) ? $attemptScores[1] : null)
                                        ),

                                    // M2
                                    TextInput::make('m2')
                                        ->label('Meeting 2')
                                        ->numeric()->minValue(0)->maxValue(100)
                                        ->helperText(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[2]; $man = $currentScores[2];
                                            if ($attemptStatus[2]) {
                                                if (is_numeric($man) && $man !== $orig) return "Override: Quiz {$orig} → Manual {$man}";
                                                return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                            }
                                            return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                        })
                                        ->suffixIcon(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[2]; $man = $currentScores[2];
                                            if ($attemptStatus[2] && is_numeric($man) && $man !== $orig) return 'heroicon-o-adjustments-horizontal';
                                            return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                        })
                                        ->suffixIconColor(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[2]; $man = $currentScores[2];
                                            if ($attemptStatus[2] && is_numeric($man) && $man !== $orig) return 'warning';
                                            return is_numeric($man) ? 'success' : 'info';
                                        })
                                        ->placeholder(
                                            is_numeric($currentScores[2])
                                                ? "Nilai sekarang: {$currentScores[2]}"
                                                : (is_numeric($attemptScores[2]) ? "Nilai quiz: {$attemptScores[2]}" : '0-100')
                                        )
                                        ->default(
                                            is_numeric($currentScores[2]) ? $currentScores[2]
                                                : (is_numeric($attemptScores[2]) ? $attemptScores[2] : null)
                                        ),

                                    // M3
                                    TextInput::make('m3')
                                        ->label('Meeting 3')
                                        ->numeric()->minValue(0)->maxValue(100)
                                        ->helperText(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[3]; $man = $currentScores[3];
                                            if ($attemptStatus[3]) {
                                                if (is_numeric($man) && $man !== $orig) return "Override: Quiz {$orig} → Manual {$man}";
                                                return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                            }
                                            return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                        })
                                        ->suffixIcon(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[3]; $man = $currentScores[3];
                                            if ($attemptStatus[3] && is_numeric($man) && $man !== $orig) return 'heroicon-o-adjustments-horizontal';
                                            return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                        })
                                        ->suffixIconColor(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[3]; $man = $currentScores[3];
                                            if ($attemptStatus[3] && is_numeric($man) && $man !== $orig) return 'warning';
                                            return is_numeric($man) ? 'success' : 'info';
                                        })
                                        ->placeholder(
                                            is_numeric($currentScores[3])
                                                ? "Nilai sekarang: {$currentScores[3]}"
                                                : (is_numeric($attemptScores[3]) ? "Nilai quiz: {$attemptScores[3]}" : '0-100')
                                        )
                                        ->default(
                                            is_numeric($currentScores[3]) ? $currentScores[3]
                                                : (is_numeric($attemptScores[3]) ? $attemptScores[3] : null)
                                        ),

                                    // M4
                                    TextInput::make('m4')
                                        ->label('Meeting 4')
                                        ->numeric()->minValue(0)->maxValue(100)
                                        ->helperText(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[4]; $man = $currentScores[4];
                                            if ($attemptStatus[4]) {
                                                if (is_numeric($man) && $man !== $orig) return "Override: Quiz {$orig} → Manual {$man}";
                                                return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                            }
                                            return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                        })
                                        ->suffixIcon(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[4]; $man = $currentScores[4];
                                            if ($attemptStatus[4] && is_numeric($man) && $man !== $orig) return 'heroicon-o-adjustments-horizontal';
                                            return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                        })
                                        ->suffixIconColor(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[4]; $man = $currentScores[4];
                                            if ($attemptStatus[4] && is_numeric($man) && $man !== $orig) return 'warning';
                                            return is_numeric($man) ? 'success' : 'info';
                                        })
                                        ->placeholder(
                                            is_numeric($currentScores[4])
                                                ? "Nilai sekarang: {$currentScores[4]}"
                                                : (is_numeric($attemptScores[4]) ? "Nilai quiz: {$attemptScores[4]}" : '0-100')
                                        )
                                        ->default(
                                            is_numeric($currentScores[4]) ? $currentScores[4]
                                                : (is_numeric($attemptScores[4]) ? $attemptScores[4] : null)
                                        ),

                                    // M5
                                    TextInput::make('m5')
                                        ->label('Meeting 5')
                                        ->numeric()->minValue(0)->maxValue(100)
                                        ->helperText(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[5]; $man = $currentScores[5];
                                            if ($attemptStatus[5]) {
                                                if (is_numeric($man) && $man !== $orig) return "Override: Quiz {$orig} → Manual {$man}";
                                                return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                            }
                                            return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                        })
                                        ->suffixIcon(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[5]; $man = $currentScores[5];
                                            if ($attemptStatus[5] && is_numeric($man) && $man !== $orig) return 'heroicon-o-adjustments-horizontal';
                                            return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                        })
                                        ->suffixIconColor(function () use ($attemptStatus, $attemptScores, $currentScores) {
                                            $orig = $attemptScores[5]; $man = $currentScores[5];
                                            if ($attemptStatus[5] && is_numeric($man) && $man !== $orig) return 'warning';
                                            return is_numeric($man) ? 'success' : 'info';
                                        })
                                        ->placeholder(
                                            is_numeric($currentScores[5])
                                                ? "Nilai sekarang: {$currentScores[5]}"
                                                : (is_numeric($attemptScores[5]) ? "Nilai quiz: {$attemptScores[5]}" : '0-100')
                                        )
                                        ->default(
                                            is_numeric($currentScores[5]) ? $currentScores[5]
                                                : (is_numeric($attemptScores[5]) ? $attemptScores[5] : null)
                                        ),
                                ]),
                            ];
                        })
                        ->action(function (User $record, array $data) {
                            $year = $record->year;
                            $updatedCount = 0;

                            foreach (['m1' => 1, 'm2' => 2, 'm3' => 3, 'm4' => 4, 'm5' => 5] as $key => $meeting) {
                                $val = $data[$key] ?? null;

                                // Selalu simpan manual override (tanpa memeriksa attempt).
                                $row = BasicListeningManualScore::firstOrCreate([
                                    'user_id'   => $record->id,
                                    'user_year' => $year,
                                    'meeting'   => $meeting,
                                ]);

                                // Kosong/null = hapus override (kembali pakai nilai quiz kalau ada)
                                if ($val === '' || $val === null) {
                                    $prev = $row->score;
                                    $row->score = null;
                                    $row->save();
                                    if ($prev !== null) {
                                        $updatedCount++;
                                    }
                                    continue;
                                }

                                if (is_numeric($val)) {
                                    $new = max(0, min(100, (int) $val));
                                    if ((int) ($row->score ?? -1) !== $new) {
                                        $row->score = $new;
                                        $row->save();
                                        $updatedCount++;
                                    }
                                }
                            }

                            // Refresh cache final/grade
                            $grade = BasicListeningGrade::firstOrCreate([
                                'user_id'   => $record->id,
                                'user_year' => $year,
                            ]);
                            $grade->save();

                            Notification::make()
                                ->title('Nilai daily diperbarui')
                                ->body($updatedCount > 0
                                    ? "{$updatedCount} meeting diupdate (manual override disimpan)."
                                    : 'Tidak ada perubahan.')
                                ->success()
                                ->send();
                        })
                        ->slideOver(),

                    // === Edit Attendance & Final (tetap seperti punyamu) ===
                    Tables\Actions\Action::make('edit_scores')
                        ->label('Insert Final')
                        ->icon('heroicon-o-pencil-square')
                        ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'tutor']))
                        ->form(function (User $record) {
                            $g = $record->basicListeningGrade;
                            return [
                                Forms\Components\Placeholder::make('mhs')
                                    ->label('Mahasiswa')
                                    ->content($record->srn . ' — ' . $record->name),

                                TextInput::make('attendance')
                                    ->label('Attendance (Nilai Kehadiran) (0–100)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->validationMessages([
                                            'numeric' => 'Nilai harus berupa angka',
                                            'min'     => 'Nilai minimal 0',
                                            'max'     => 'Nilai maksimal 100',
                                        ])
                                    ->default($g?->attendance),

                                TextInput::make('final_test')
                                    ->label('Final Test (0–100)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->validationMessages([
                                            'numeric' => 'Nilai harus berupa angka',
                                            'min'     => 'Nilai minimal 0',
                                            'max'     => 'Nilai maksimal 100',
                                        ])
                                    ->default($g?->final_test),
                            ];
                        })
                        ->action(function (User $record, array $data) {
                            $grade = BasicListeningGrade::firstOrCreate([
                                'user_id'   => $record->id,
                                'user_year' => $record->year,
                            ]);

                            $grade->attendance = $data['attendance'] !== null ? (int) $data['attendance'] : null;
                            $grade->final_test = $data['final_test'] !== null ? (int) $data['final_test'] : null;
                            $grade->save(); // model auto-update cache

                            Notification::make()
                                ->title('Nilai berhasil diperbarui')
                                ->success()
                                ->send();
                        })
                        ->modalWidth('xl')
                        ->slideOver(),
                ])
                ->label('Aksi')
                ->icon('heroicon-m-cog-6-tooth'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulk_edit_daily')
                    ->label('Edit Daily')
                    ->icon('heroicon-o-pencil')
                    ->form(function (Collection $records) {
                        $studentsData = [];

                        // Preload
                        $records->load([
                            'basicListeningAttempts' => function ($query) {
                                $query->whereNotNull('submitted_at')
                                    ->select(['id', 'user_id', 'session_id', 'score', 'submitted_at']);
                            },
                            'basicListeningManualScores',
                        ]);

                        foreach ($records as $record) {
                            $attemptsBySession = $record->basicListeningAttempts
                                ->groupBy('session_id')
                                ->map(function ($attempts) {
                                    return $attempts->sortByDesc('submitted_at')->first();
                                });

                            $attemptStatus = [];
                            $currentScores = [];
                            $attemptScores = [];

                            foreach ([1, 2, 3, 4, 5] as $meeting) {
                                $attempt = $attemptsBySession->get($meeting);
                                $hasAttempt = ! is_null($attempt);

                                $attemptStatus[$meeting] = $hasAttempt;

                                $manualScore = $record->basicListeningManualScores
                                    ->where('meeting', $meeting)
                                    ->first()
                                    ?->score;

                                $currentScores[$meeting] = $manualScore;
                                $attemptScores[$meeting] = $hasAttempt ? $attempt->score : null;
                            }

                            // manual kalau ada; jika tidak ada, pakai attempt score
                            $studentsData[] = [
                                'user_id' => $record->id,
                                'name'    => $record->name,
                                'srn'     => $record->srn,

                                'm1' => is_numeric($currentScores[1]) ? $currentScores[1] : (is_numeric($attemptScores[1]) ? $attemptScores[1] : null),
                                'm2' => is_numeric($currentScores[2]) ? $currentScores[2] : (is_numeric($attemptScores[2]) ? $attemptScores[2] : null),
                                'm3' => is_numeric($currentScores[3]) ? $currentScores[3] : (is_numeric($attemptScores[3]) ? $attemptScores[3] : null),
                                'm4' => is_numeric($currentScores[4]) ? $currentScores[4] : (is_numeric($attemptScores[4]) ? $attemptScores[4] : null),
                                'm5' => is_numeric($currentScores[5]) ? $currentScores[5] : (is_numeric($attemptScores[5]) ? $attemptScores[5] : null),

                                'attempt_1' => $attemptStatus[1],
                                'attempt_2' => $attemptStatus[2],
                                'attempt_3' => $attemptStatus[3],
                                'attempt_4' => $attemptStatus[4],
                                'attempt_5' => $attemptStatus[5],

                                'attempt_score_1' => $attemptScores[1],
                                'attempt_score_2' => $attemptScores[2],
                                'attempt_score_3' => $attemptScores[3],
                                'attempt_score_4' => $attemptScores[4],
                                'attempt_score_5' => $attemptScores[5],
                            ];
                        }

                        return [
                            Forms\Components\Repeater::make('students')
                                ->label('Data Mahasiswa')
                                ->schema([
                                    Forms\Components\Hidden::make('user_id'),
                                    Forms\Components\Hidden::make('name'),
                                    Forms\Components\Hidden::make('srn'),
                                    Forms\Components\Hidden::make('attempt_1'),
                                    Forms\Components\Hidden::make('attempt_2'),
                                    Forms\Components\Hidden::make('attempt_3'),
                                    Forms\Components\Hidden::make('attempt_4'),
                                    Forms\Components\Hidden::make('attempt_5'),
                                    Forms\Components\Hidden::make('attempt_score_1'),
                                    Forms\Components\Hidden::make('attempt_score_2'),
                                    Forms\Components\Hidden::make('attempt_score_3'),
                                    Forms\Components\Hidden::make('attempt_score_4'),
                                    Forms\Components\Hidden::make('attempt_score_5'),

                                    Forms\Components\Grid::make(5)->schema([
                                        // M1
                                        TextInput::make('m1')
                                            ->label('Meeting 1')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->suffixIcon(function ($get) {
                                                $orig = $get('attempt_score_1'); // nilai quiz asli
                                                $man  = $get('m1');              // nilai manual saat ini
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'heroicon-o-adjustments-horizontal'; // override
                                                }
                                                return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $orig = $get('attempt_score_1'); $man = $get('m1');
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'warning';
                                                }
                                                return is_numeric($man) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                $hasAttempt = filter_var($get('attempt_1'), FILTER_VALIDATE_BOOLEAN);
                                                $orig = $get('attempt_score_1'); $man = $get('m1');
                                                if ($hasAttempt) {
                                                    if (is_numeric($man) && is_numeric($orig) && (int)$man !== (int)$orig) {
                                                        return "Override: Quiz {$orig} → Manual {$man}";
                                                    }
                                                    return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                                }
                                                return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                            })
                                            ->placeholder(function ($get) {
                                                $man = $get('m1'); $orig = $get('attempt_score_1');
                                                return is_numeric($man) ? "Nilai sekarang: {$man}" : (is_numeric($orig) ? "Nilai quiz: {$orig}" : '0-100');
                                            }),

                                        // M2
                                        TextInput::make('m2')
                                            ->label('Meeting 2')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->suffixIcon(function ($get) {
                                                $orig = $get('attempt_score_2'); $man = $get('m2');
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'heroicon-o-adjustments-horizontal';
                                                }
                                                return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $orig = $get('attempt_score_2'); $man = $get('m2');
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'warning';
                                                }
                                                return is_numeric($man) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                $hasAttempt = filter_var($get('attempt_2'), FILTER_VALIDATE_BOOLEAN);
                                                $orig = $get('attempt_score_2'); $man = $get('m2');
                                                if ($hasAttempt) {
                                                    if (is_numeric($man) && is_numeric($orig) && (int)$man !== (int)$orig) {
                                                        return "Override: Quiz {$orig} → Manual {$man}";
                                                    }
                                                    return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                                }
                                                return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                            })
                                            ->placeholder(function ($get) {
                                                $man = $get('m2'); $orig = $get('attempt_score_2');
                                                return is_numeric($man) ? "Nilai sekarang: {$man}" : (is_numeric($orig) ? "Nilai quiz: {$orig}" : '0-100');
                                            }),

                                        // M3
                                        TextInput::make('m3')
                                            ->label('Meeting 3')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->suffixIcon(function ($get) {
                                                $orig = $get('attempt_score_3'); $man = $get('m3');
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'heroicon-o-adjustments-horizontal';
                                                }
                                                return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $orig = $get('attempt_score_3'); $man = $get('m3');
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'warning';
                                                }
                                                return is_numeric($man) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                $hasAttempt = filter_var($get('attempt_3'), FILTER_VALIDATE_BOOLEAN);
                                                $orig = $get('attempt_score_3'); $man = $get('m3');
                                                if ($hasAttempt) {
                                                    if (is_numeric($man) && is_numeric($orig) && (int)$man !== (int)$orig) {
                                                        return "Override: Quiz {$orig} → Manual {$man}";
                                                    }
                                                    return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                                }
                                                return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                            })
                                            ->placeholder(function ($get) {
                                                $man = $get('m3'); $orig = $get('attempt_score_3');
                                                return is_numeric($man) ? "Nilai sekarang: {$man}" : (is_numeric($orig) ? "Nilai quiz: {$orig}" : '0-100');
                                            }),

                                        // M4
                                        TextInput::make('m4')
                                            ->label('Meeting 4')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->suffixIcon(function ($get) {
                                                $orig = $get('attempt_score_4'); $man = $get('m4');
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'heroicon-o-adjustments-horizontal';
                                                }
                                                return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $orig = $get('attempt_score_4'); $man = $get('m4');
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'warning';
                                                }
                                                return is_numeric($man) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                $hasAttempt = filter_var($get('attempt_4'), FILTER_VALIDATE_BOOLEAN);
                                                $orig = $get('attempt_score_4'); $man = $get('m4');
                                                if ($hasAttempt) {
                                                    if (is_numeric($man) && is_numeric($orig) && (int)$man !== (int)$orig) {
                                                        return "Override: Quiz {$orig} → Manual {$man}";
                                                    }
                                                    return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                                }
                                                return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                            })
                                            ->placeholder(function ($get) {
                                                $man = $get('m4'); $orig = $get('attempt_score_4');
                                                return is_numeric($man) ? "Nilai sekarang: {$man}" : (is_numeric($orig) ? "Nilai quiz: {$orig}" : '0-100');
                                            }),

                                        // M5
                                        TextInput::make('m5')
                                            ->label('Meeting 5')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->suffixIcon(function ($get) {
                                                $orig = $get('attempt_score_5'); $man = $get('m5');
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'heroicon-o-adjustments-horizontal';
                                                }
                                                return is_numeric($man) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $orig = $get('attempt_score_5'); $man = $get('m5');
                                                if (is_numeric($orig) && is_numeric($man) && (int)$man !== (int)$orig) {
                                                    return 'warning';
                                                }
                                                return is_numeric($man) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                $hasAttempt = filter_var($get('attempt_5'), FILTER_VALIDATE_BOOLEAN);
                                                $orig = $get('attempt_score_5'); $man = $get('m5');
                                                if ($hasAttempt) {
                                                    if (is_numeric($man) && is_numeric($orig) && (int)$man !== (int)$orig) {
                                                        return "Override: Quiz {$orig} → Manual {$man}";
                                                    }
                                                    return is_numeric($orig) ? "Nilai quiz: {$orig} (bisa diubah)" : 'Belum ada nilai quiz';
                                                }
                                                return is_numeric($man) ? 'Sudah diisi (manual)' : 'Perlu diisi';
                                            })
                                            ->placeholder(function ($get) {
                                                $man = $get('m5'); $orig = $get('attempt_score_5');
                                                return is_numeric($man) ? "Nilai sekarang: {$man}" : (is_numeric($orig) ? "Nilai quiz: {$orig}" : '0-100');
                                            }),
                                    ])->columnSpanFull(),
                                ])
                                ->columns(1)
                                ->itemLabel(fn (array $state): string =>
                                    ($state['name'] ?? 'Mahasiswa') . ' (' . ($state['srn'] ?? 'N/A') . ')'
                                )
                                ->collapsible()
                                ->disableItemCreation()
                                ->disableItemDeletion()
                                ->default($studentsData),
                        ];
                    })
                    ->action(function (Collection $records, array $data) {
                        $updatedCount = 0;

                        // Safety: kalau tidak ada data students, hentikan dengan info
                        if (empty($data['students']) || !is_array($data['students'])) {
                            Notification::make()
                                ->title('Tidak ada data untuk diperbarui')
                                ->body('Form tidak mengirimkan item mahasiswa.')
                                ->warning()
                                ->send();
                            return;
                        }

                        foreach ($data['students'] as $studentData) {
                            $userId = $studentData['user_id'] ?? null;
                            if (!$userId) {
                                continue;
                            }

                            /** @var \App\Models\User|null $user */
                            $user = User::find($userId);
                            if (!$user) {
                                continue;
                            }

                            $year = $user->year;
                            $studentUpdated = false;

                            foreach (['m1'=>1, 'm2'=>2, 'm3'=>3, 'm4'=>4, 'm5'=>5] as $key => $meeting) {
                                $val = $studentData[$key] ?? null;

                                // Ambil/siapkan baris manual score
                                $row = BasicListeningManualScore::firstOrCreate([
                                    'user_id'   => $userId,
                                    'user_year' => $year,
                                    'meeting'   => $meeting,
                                ]);

                                $before = $row->score;

                                // Kosong/null = hapus override manual (gunakan nilai quiz jika ada)
                                if ($val === '' || $val === null) {
                                    if ($before !== null) {
                                        $row->score = null;
                                        $row->save();
                                        $updatedCount++;
                                        $studentUpdated = true;
                                    }
                                    continue;
                                }

                                // Normalisasi nilai 0..100 (hanya simpan kalau berubah)
                                if (is_numeric($val)) {
                                    $new = max(0, min(100, (int) $val));
                                    if ((int) ($before ?? -1) !== $new) {
                                        $row->score = $new;
                                        $row->save();
                                        $updatedCount++;
                                        $studentUpdated = true;
                                    }
                                }
                            }

                            // Refresh cache final/grade untuk mahasiswa ini jika ada perubahan
                            if ($studentUpdated) {
                                $grade = BasicListeningGrade::firstOrCreate([
                                    'user_id'   => $userId,
                                    'user_year' => $year,
                                ]);
                                $grade->save();
                            }
                        }

                        Notification::make()
                            ->title('Bulk update selesai')
                            ->body($updatedCount > 0
                                ? "{$updatedCount} nilai meeting diupdate (manual override disimpan)."
                                : 'Tidak ada perubahan.')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\BulkAction::make('bulk_edit_scores')
                    ->label('Insert Final')
                    ->icon('heroicon-o-pencil-square')
                    ->form(function (Collection $records) {
                        $studentsData = [];

                        foreach ($records as $record) {
                            $grade = $record->basicListeningGrade;

                            $studentsData[] = [
                                'user_id' => $record->id,
                                'name' => $record->name,
                                'srn' => $record->srn,
                                'attendance' => $grade?->attendance,
                                'final_test' => $grade?->final_test,
                            ];
                        }

                        return [
                            Forms\Components\Repeater::make('students')
                                ->label('Data Mahasiswa')
                                ->schema([
                                    Forms\Components\Hidden::make('user_id'),
                                    Forms\Components\Hidden::make('name'),
                                    Forms\Components\Hidden::make('srn'),
                                    Forms\Components\Grid::make(2)->schema([
                                        TextInput::make('attendance')
                                            ->label('Attendance (Nilai Kehadiran)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->placeholder('0-100'),
                                        TextInput::make('final_test')
                                            ->label('Final Test')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->placeholder('0-100'),
                                    ])->columnSpanFull(),
                                ])
                                ->columns(1)
                                ->itemLabel(fn (array $state): string =>
                                    ($state['name'] ?? 'Mahasiswa') . ' (' . ($state['srn'] ?? 'N/A') . ')'
                                )
                                ->collapsible()
                                ->disableItemCreation()
                                ->disableItemDeletion()
                                ->default($studentsData)
                        ];
                    })
                    ->action(function (Collection $records, array $data) {
                        foreach ($data['students'] as $studentData) {
                            $userId = $studentData['user_id'];
                            $user = User::find($userId);

                            if (!$user) continue;

                            $grade = BasicListeningGrade::firstOrCreate([
                                'user_id'   => $userId,
                                'user_year' => $user->year,
                            ]);

                            $grade->attendance = $studentData['attendance'] !== null && $studentData['attendance'] !== ''
                                ? (int) $studentData['attendance']
                                : null;
                            $grade->final_test = $studentData['final_test'] !== null && $studentData['final_test'] !== ''
                                ? (int) $studentData['final_test']
                                : null;

                            $grade->save();
                        }

                        Notification::make()
                            ->title('Nilai Attendance & Final Test berhasil diperbarui')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('bulk_reset_scores')
                    ->label('Reset Nilai')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Nilai Mahasiswa')
                    ->modalSubheading('Apakah Anda yakin ingin mereset nilai untuk mahasiswa yang dipilih?')
                    ->form([
                        Forms\Components\CheckboxList::make('reset_items')
                            ->label('Reset Item Berikut:')
                            ->options([
                                'daily' => 'Daily Scores (S1-S5)',
                                'attendance' => 'Attendance',
                                'final_test' => 'Final Test',
                            ])
                            ->default(['daily', 'attendance', 'final_test'])
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $resetCount = 0;

                        foreach ($records as $record) {
                            $year = $record->year;

                            if (in_array('daily', $data['reset_items'])) {
                                BasicListeningManualScore::where('user_id', $record->id)
                                    ->where('user_year', $year)
                                    ->whereIn('meeting', [1,2,3,4,5])
                                    ->delete();
                            }

                            $grade = BasicListeningGrade::firstOrCreate([
                                'user_id' => $record->id,
                                'user_year' => $year,
                            ]);

                            if (in_array('attendance', $data['reset_items'])) {
                                $grade->attendance = null;
                            }

                            if (in_array('final_test', $data['reset_items'])) {
                                $grade->final_test = null;
                            }

                            $grade->save();
                            $resetCount++;
                        }

                        Notification::make()
                            ->title('Reset nilai berhasil')
                            ->body("{$resetCount} mahasiswa berhasil direset")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                Tables\Actions\BulkAction::make('bulk_set_group_bl')
                    ->label('Atur Grup BL')
                    ->icon('heroicon-o-user-group')
                    ->color('success')
                    ->visible(fn () => auth()->user()?->hasAnyRole(['Admin','tutor']))
                    ->form([
                        Forms\Components\Select::make('nomor_grup_bl')
                            ->label('Pilih Grup')
                            ->options(fn () =>
                                \App\Models\User::query()
                                    ->whereNotNull('nomor_grup_bl')
                                    ->distinct()
                                    ->orderBy('nomor_grup_bl')
                                    ->pluck('nomor_grup_bl', 'nomor_grup_bl')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Toggle::make('only_empty')
                            ->label('Hanya yang belum punya grup')
                            ->default(true),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $me   = auth()->user();
                        $isAdmin  = $me?->hasRole('Admin');
                        $isTutor  = $me?->hasRole('tutor');
                        $allowed  = $isTutor && method_exists($me, 'assignedProdyIds') ? (array) $me->assignedProdyIds() : [];

                        $targetGroup = $data['nomor_grup_bl'] ?? null;
                        $onlyEmpty   = (bool) ($data['only_empty'] ?? false);

                        $updated = 0; $skippedRestricted = 0; $skippedAlready = 0;

                        foreach ($records as $user) {
                            /** @var \App\Models\User $user */
                            // Batasan tutor: hanya prodi binaan
                            if ($isTutor && ! $isAdmin) {
                                if (empty($allowed) || ! in_array($user->prody_id, $allowed, true)) {
                                    $skippedRestricted++;
                                    continue;
                                }
                            }

                            if ($onlyEmpty && filled($user->nomor_grup_bl)) {
                                $skippedAlready++;
                                continue;
                            }

                            $user->nomor_grup_bl = $targetGroup;
                            $user->save();
                            $updated++;
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Bulk atur grup selesai')
                            ->body("Diupdate: {$updated}" .
                                ($skippedAlready ? " | Dilewati (sudah punya grup): {$skippedAlready}" : '') .
                                ($skippedRestricted ? " | Dibatasi akses: {$skippedRestricted}" : ''))
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export_excel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        // Default: gunakan FILTER AKTIF; tapi sediakan opsi override di modal export
                        Select::make('prody_id')
                            ->label('Prodi')
                            ->options(function () {
                                return \App\Models\Prody::query()
                                    ->orderBy('name')->pluck('name','id')->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Gunakan filter tabel'),
                        Select::make('nomor_grup_bl')
                            ->label('Nomor Grup BL')
                            ->options(function () {
                                return \App\Models\User::query()
                                    ->whereNotNull('nomor_grup_bl')
                                    ->distinct()
                                    ->orderBy('nomor_grup_bl')
                                    ->pluck('nomor_grup_bl','nomor_grup_bl')
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->placeholder('Gunakan filter tabel'),
                        Toggle::make('only_complete')
                            ->label('Hanya yang nilainya lengkap (S1–S5, Attendance, Final Test)')
                            ->default(true),
                    ])
                    ->action(function (array $data) {
                        // Ambil filter aktif dari tabel
                        $filters = $this->getTableFiltersForm()->getState();

                        $prodyFromFilter = $filters['prody_id']['value'] ?? null;
                        $groupFromFilter = $filters['nomor_grup_bl']['value'] ?? null;

                        // Jika user memilih override di modal export, pakai itu
                        $prodyId = $data['prody_id'] ?? $prodyFromFilter;
                        $groupNo = $data['nomor_grup_bl'] ?? $groupFromFilter;

                        // Base query + relasi yang diperlukan untuk export
                        $q = $this->baseQuery(auth()->user())
                            ->with([
                                'prody:id,name',
                                'basicListeningGrade:id,user_id,user_year,attendance,final_test,final_numeric_cached,final_letter_cached',
                                'basicListeningManualScores:id,user_id,user_year,meeting,score',
                                'basicListeningAttempts' => function ($qq) {
                                    $qq->select(['id','user_id','session_id','score','submitted_at']);
                                },
                            ])
                            ->when($prodyId, fn($qq) => $qq->where('prody_id', $prodyId))
                            ->when($groupNo, fn($qq) => $qq->where('nomor_grup_bl', $groupNo));

                        $users = $q->get();

                        // Validasi kelengkapan nilai bila diminta
                        $onlyComplete = (bool) ($data['only_complete'] ?? true);

                        if ($onlyComplete) {
                            $incomplete = [];

                            foreach ($users as $u) {
                                // Attendance & Final Test wajib ada
                                $att   = optional($u->basicListeningGrade)->attendance;
                                $final = optional($u->basicListeningGrade)->final_test;

                                // S1..S5 wajib ada (manual > attempt submitted)
                                $missingMeetings = [];
                                foreach ([1,2,3,4,5] as $m) {
                                    $manual = $u->basicListeningManualScores
                                        ->firstWhere('meeting', $m)
                                        ->score ?? null;

                                    if (is_numeric($manual)) {
                                        continue;
                                    }

                                    $attempt = $u->basicListeningAttempts
                                        ->where('session_id', $m)
                                        ->filter(fn ($a) => !is_null($a->submitted_at))
                                        ->sortByDesc('submitted_at')
                                        ->first();

                                    if (!is_numeric($attempt?->score)) {
                                        $missingMeetings[] = "S{$m}";
                                    }
                                }

                                $isComplete = (is_numeric($att) && is_numeric($final) && empty($missingMeetings));

                                if (!$isComplete) {
                                    $incomplete[] = [
                                        'name' => $u->name,
                                        'srn'  => $u->srn,
                                        'missing' => (is_numeric($att) ? [] : ['Attendance'])
                                            + (is_numeric($final) ? [] : ['Final Test'])
                                            // tampilkan deskripsi meeting yang kurang
                                    ];
                                }
                            }

                            if (!empty($incomplete)) {
                                // Tampilkan notifikasi ringkas siapa saja yang belum lengkap
                                $list = collect($users)->filter(function ($u) {
                                    // hitung status lengkap lagi cepat (biar simpel)
                                    $att   = optional($u->basicListeningGrade)->attendance;
                                    $final = optional($u->basicListeningGrade)->final_test;
                                    $ok = is_numeric($att) && is_numeric($final);
                                    if (!$ok) return true;

                                    foreach ([1,2,3,4,5] as $m) {
                                        $manual = $u->basicListeningManualScores->firstWhere('meeting', $m)->score ?? null;
                                        if (is_numeric($manual)) continue;
                                        $attempt = $u->basicListeningAttempts
                                            ->where('session_id', $m)
                                            ->filter(fn ($a) => !is_null($a->submitted_at))
                                            ->sortByDesc('submitted_at')->first();
                                        if (!is_numeric($attempt?->score)) {
                                            return true;
                                        }
                                    }
                                    return false;
                                })
                                ->take(8)
                                ->map(fn($u) => "{$u->srn} — {$u->name}")
                                ->implode(', ');

                                \Filament\Notifications\Notification::make()
                                    ->title('Export dibatalkan: masih ada nilai yang belum lengkap')
                                    ->body($list ? "Contoh belum lengkap: {$list}." : 'Ada data belum lengkap.')
                                    ->danger()
                                    ->send();

                                return;
                            }
                        }

                        // Siapkan file export (pakai data users yang sudah difilter)
                        $fileName = 'BL_Export_' . now()->format('Ymd_His') . '.xlsx';

                        $prodyName = null;
                        if ($prodyId) {
                            $prodyName = optional(\App\Models\Prody::find($prodyId))->name;
                        }

                        return Excel::download(
                            new TutorMahasiswaTemplateExport($users, $groupNo, $prodyName),
                            $fileName
                        );
                    }),
            ])
            ->emptyStateHeading('Belum ada data')
            ->emptyStateDescription('Ubah filter angkatan atau pastikan prodi yang Anda ampu sudah diatur.');
    }

    /** Scope: Admin lihat semua; tutor hanya prodi binaannya + SRN prefix 25 default. */
    protected function baseQuery(?User $user): Builder
    {
        $query = User::query()
            ->with(['prody', 'basicListeningGrade', 'basicListeningManualScores'])
            ->whereNotNull('srn');

        if ($user?->hasRole('Admin')) return $query;

        if ($user?->hasRole('tutor')) {
            $ids = $user->assignedProdyIds();
            if (empty($ids)) return $query->whereRaw('1=0');

            return $query
                ->whereIn('prody_id', $ids)
                ->where('srn', 'like', '25%');
        }

        return $query->whereRaw('1=0');
    }

    protected function getActions(): array
    {
        return [
            Action::make('dashboard')
                ->label('Kembali ke Dasbor')
                ->url(route('filament.admin.pages.2'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }
}
