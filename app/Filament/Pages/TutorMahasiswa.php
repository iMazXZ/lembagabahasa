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
                    ->badge(),

                Tables\Columns\TextColumn::make('nomor_grup_bl')
                    ->label('Grup BL')
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => $state ? 'Grup '.$state : '—'),

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
                // Filter Angkatan via prefix SRN
                Filter::make('angkatan')
                    ->label('Prefix Angkatan (SRN)')
                    ->form([
                        Forms\Components\TextInput::make('prefix')
                            ->placeholder('mis. 25')
                            ->default('25')
                            ->maxLength(2)
                            ->datalist(['25', '24', '23']),
                    ])
                    ->indicateUsing(fn (array $data): ?string =>
                        filled($data['prefix'] ?? null)
                            ? 'Angkatan: ' . $data['prefix']
                            : null
                    )
                    ->query(function (Builder $query, array $data) {
                        $prefix = trim((string)($data['prefix'] ?? ''));
                        if ($prefix !== '') {
                            $query->where('srn', 'like', $prefix . '%');
                        }
                    }),

                // Filter Prodi
                Tables\Filters\SelectFilter::make('prody_id')
                    ->label('Prodi')
                    ->options(function () use ($user) {
                        if ($user?->hasRole('tutor')) {
                            return Prody::query()
                                ->whereIn('id', $user->assignedProdyIds())
                                ->orderBy('name')
                                ->pluck('name', 'id');
                        }

                        return Prody::query()
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    })
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('prody_id', $data['value']);
                        }
                    }),

                Tables\Filters\SelectFilter::make('nomor_grup_bl')
                    ->label('Filter Grup BL')
                    ->options(function () {
                        // Ambil daftar grup yang ada di mahasiswa binaan saja
                        return \App\Models\User::query()
                            ->whereIn('prody_id', auth()->user()->assignedProdyIds())
                            ->whereNotNull('nomor_grup_bl')
                            ->distinct()
                            ->orderBy('nomor_grup_bl')
                            ->pluck('nomor_grup_bl', 'nomor_grup_bl')
                            ->toArray();
                    }),
            ])
            ->actions([
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
                                TextInput::make('m1')
                                    ->label('Meeting 1')
                                    ->numeric()->minValue(0)->maxValue(100)
                                    ->disabled($attemptStatus[1])
                                    ->helperText($attemptStatus[1] ? 'Terkunci' : 'Input Nilai')
                                    ->placeholder(is_numeric($currentScores[1]) ? "Nilai sekarang: {$currentScores[1]}" : (is_numeric($attemptScores[1]) ? "Nilai attempt: {$attemptScores[1]}" : '0-100'))
                                    ->default(is_numeric($currentScores[1]) ? $currentScores[1] : (is_numeric($attemptScores[1]) ? $attemptScores[1] : null)),

                                TextInput::make('m2')
                                    ->label('Meeting 2')
                                    ->numeric()->minValue(0)->maxValue(100)
                                    ->disabled($attemptStatus[2])
                                    ->helperText($attemptStatus[2] ? 'Terkunci' : 'Input Nilai')
                                    ->placeholder(is_numeric($currentScores[2]) ? "Nilai sekarang: {$currentScores[2]}" : (is_numeric($attemptScores[2]) ? "Nilai attempt: {$attemptScores[2]}" : '0-100'))
                                    ->default(is_numeric($currentScores[2]) ? $currentScores[2] : (is_numeric($attemptScores[2]) ? $attemptScores[2] : null)),

                                TextInput::make('m3')
                                    ->label('Meeting 3')
                                    ->numeric()->minValue(0)->maxValue(100)
                                    ->disabled($attemptStatus[3])
                                    ->helperText($attemptStatus[3] ? 'Terkunci' : 'Input Nilai')
                                    ->placeholder(is_numeric($currentScores[3]) ? "Nilai sekarang: {$currentScores[3]}" : (is_numeric($attemptScores[3]) ? "Nilai attempt: {$attemptScores[3]}" : '0-100'))
                                    ->default(is_numeric($currentScores[3]) ? $currentScores[3] : (is_numeric($attemptScores[3]) ? $attemptScores[3] : null)),

                                TextInput::make('m4')
                                    ->label('Meeting 4')
                                    ->numeric()->minValue(0)->maxValue(100)
                                    ->disabled($attemptStatus[4])
                                    ->helperText($attemptStatus[4] ? 'Terkunci' : 'Input Nilai')
                                    ->placeholder(is_numeric($currentScores[4]) ? "Nilai sekarang: {$currentScores[4]}" : (is_numeric($attemptScores[4]) ? "Nilai attempt: {$attemptScores[4]}" : '0-100'))
                                    ->default(is_numeric($currentScores[4]) ? $currentScores[4] : (is_numeric($attemptScores[4]) ? $attemptScores[4] : null)),

                                TextInput::make('m5')
                                    ->label('Meeting 5')
                                    ->numeric()->minValue(0)->maxValue(100)
                                    ->disabled($attemptStatus[5])
                                    ->helperText($attemptStatus[5] ? 'Terkunci' : 'Input Nilai')
                                    ->placeholder(is_numeric($currentScores[5]) ? "Nilai sekarang: {$currentScores[5]}" : (is_numeric($attemptScores[5]) ? "Nilai attempt: {$attemptScores[5]}" : '0-100'))
                                    ->default(is_numeric($currentScores[5]) ? $currentScores[5] : (is_numeric($attemptScores[5]) ? $attemptScores[5] : null)),
                            ]),
                        ];
                    })
                    ->action(function (User $record, array $data) {
                        $year = $record->year;
                        $updatedCount = 0;

                        foreach (['m1'=>1,'m2'=>2,'m3'=>3,'m4'=>4,'m5'=>5] as $key => $meeting) {
                            $val = $data[$key] ?? null;

                            // Skip jika meeting ini ada attempt (field akan disabled jadi tidak bisa diubah)
                            $hasAttempt = $record->basicListeningAttempts()
                                ->where('session_id', $meeting)
                                ->whereNotNull('submitted_at')
                                ->exists();

                            if ($hasAttempt) {
                                continue;
                            }

                            // Hanya simpan untuk meeting yang tidak ada attempt
                            $row = BasicListeningManualScore::firstOrCreate([
                                'user_id'   => $record->id,
                                'user_year' => $year,
                                'meeting'   => $meeting,
                            ]);

                            $row->score = is_numeric($val) ? max(0, min(100, (int)$val)) : null;
                            $row->save();
                            $updatedCount++;
                        }

                        // Update cache
                        $grade = BasicListeningGrade::firstOrCreate([
                            'user_id'   => $record->id,
                            'user_year' => $year,
                        ]);
                        $grade->save();

                        Notification::make()
                            ->title('Nilai daily berhasil diperbarui')
                            ->body($updatedCount > 0 ? "{$updatedCount} meeting diupdate." : 'Tidak ada perubahan (semua meeting terkunci).')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('xl')
                    ->slideOver(),

                // === Edit Attendance & Final
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
                                        TextInput::make('m1')
                                            ->label('Meeting 1')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->disabled(fn ($get) => filter_var($get('attempt_1'), FILTER_VALIDATE_BOOLEAN))
                                            ->suffixIcon(function ($get) {
                                                $locked = filter_var($get('attempt_1'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'heroicon-o-lock-closed';
                                                return is_numeric($get('m1')) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $locked = filter_var($get('attempt_1'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'gray';
                                                return is_numeric($get('m1')) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                if (filter_var($get('attempt_1'), FILTER_VALIDATE_BOOLEAN)) {
                                                    $attemptScore = $get('attempt_score_1');
                                                    return 'Terkunci' . (is_numeric($attemptScore) ? " (Quiz: {$attemptScore})" : '');
                                                }
                                                return is_numeric($get('m1')) ? 'Sudah diisi' : 'Perlu diisi';
                                            })
                                            ->placeholder(fn ($get) => is_numeric($get('m1')) ? "Nilai sekarang: {$get('m1')}" : '0-100'),

                                        TextInput::make('m2')
                                            ->label('Meeting 2')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->disabled(fn ($get) => filter_var($get('attempt_2'), FILTER_VALIDATE_BOOLEAN))
                                            ->suffixIcon(function ($get) {
                                                $locked = filter_var($get('attempt_2'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'heroicon-o-lock-closed';
                                                return is_numeric($get('m2')) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $locked = filter_var($get('attempt_2'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'gray';
                                                return is_numeric($get('m2')) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                if (filter_var($get('attempt_2'), FILTER_VALIDATE_BOOLEAN)) {
                                                    $attemptScore = $get('attempt_score_2');
                                                    return 'Terkunci' . (is_numeric($attemptScore) ? " (Quiz: {$attemptScore})" : '');
                                                }
                                                return is_numeric($get('m2')) ? 'Sudah diisi' : 'Perlu diisi';
                                            })
                                            ->placeholder(fn ($get) => is_numeric($get('m2')) ? "Nilai sekarang: {$get('m2')}" : '0-100'),

                                        TextInput::make('m3')
                                            ->label('Meeting 3')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->disabled(fn ($get) => filter_var($get('attempt_3'), FILTER_VALIDATE_BOOLEAN))
                                            ->suffixIcon(function ($get) {
                                                $locked = filter_var($get('attempt_3'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'heroicon-o-lock-closed';
                                                return is_numeric($get('m3')) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $locked = filter_var($get('attempt_3'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'gray';
                                                return is_numeric($get('m3')) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                if (filter_var($get('attempt_3'), FILTER_VALIDATE_BOOLEAN)) {
                                                    $attemptScore = $get('attempt_score_3');
                                                    return 'Terkunci' . (is_numeric($attemptScore) ? " (Quiz: {$attemptScore})" : '');
                                                }
                                                return is_numeric($get('m3')) ? 'Sudah diisi' : 'Perlu diisi';
                                            })
                                            ->placeholder(fn ($get) => is_numeric($get('m3')) ? "Nilai sekarang: {$get('m3')}" : '0-100'),

                                        TextInput::make('m4')
                                            ->label('Meeting 4')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->disabled(fn ($get) => filter_var($get('attempt_4'), FILTER_VALIDATE_BOOLEAN))
                                            ->suffixIcon(function ($get) {
                                                $locked = filter_var($get('attempt_4'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'heroicon-o-lock-closed';
                                                return is_numeric($get('m4')) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $locked = filter_var($get('attempt_4'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'gray';
                                                return is_numeric($get('m4')) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                if (filter_var($get('attempt_4'), FILTER_VALIDATE_BOOLEAN)) {
                                                    $attemptScore = $get('attempt_score_4');
                                                    return 'Terkunci' . (is_numeric($attemptScore) ? " (Quiz: {$attemptScore})" : '');
                                                }
                                                return is_numeric($get('m4')) ? 'Sudah diisi' : 'Perlu diisi';
                                            })
                                            ->placeholder(fn ($get) => is_numeric($get('m4')) ? "Nilai sekarang: {$get('m4')}" : '0-100'),

                                        TextInput::make('m5')
                                            ->label('Meeting 5')
                                            ->numeric()->minValue(0)->maxValue(100)
                                            ->validationMessages([
                                                'numeric' => 'Nilai harus berupa angka',
                                                'min'     => 'Nilai minimal 0',
                                                'max'     => 'Nilai maksimal 100',
                                            ])
                                            ->disabled(fn ($get) => filter_var($get('attempt_5'), FILTER_VALIDATE_BOOLEAN))
                                            ->suffixIcon(function ($get) {
                                                $locked = filter_var($get('attempt_5'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'heroicon-o-lock-closed';
                                                return is_numeric($get('m5')) ? 'heroicon-o-check-circle' : 'heroicon-o-pencil-square';
                                            })
                                            ->suffixIconColor(function ($get) {
                                                $locked = filter_var($get('attempt_5'), FILTER_VALIDATE_BOOLEAN);
                                                if ($locked) return 'gray';
                                                return is_numeric($get('m5')) ? 'success' : 'info';
                                            })
                                            ->helperText(function ($get) {
                                                if (filter_var($get('attempt_5'), FILTER_VALIDATE_BOOLEAN)) {
                                                    $attemptScore = $get('attempt_score_5');
                                                    return 'Terkunci' . (is_numeric($attemptScore) ? " (Quiz: {$attemptScore})" : '');
                                                }
                                                return is_numeric($get('m5')) ? 'Sudah diisi' : 'Perlu diisi';
                                            })
                                            ->placeholder(fn ($get) => is_numeric($get('m5')) ? "Nilai sekarang: {$get('m5')}" : '0-100'),
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
                        $skippedCount = 0;
                        $attemptViolations = [];

                        foreach ($data['students'] as $studentData) {
                            $userId = $studentData['user_id'];
                            $user = User::find($userId);

                            if (!$user) continue;

                            $year = $user->year;

                            foreach (['m1'=>1, 'm2'=>2, 'm3'=>3, 'm4'=>4, 'm5'=>5] as $key => $meeting) {
                                $val = $studentData[$key] ?? null;

                                $hasAttempt = $user->basicListeningAttempts()
                                    ->where('session_id', $meeting)
                                    ->whereNotNull('submitted_at')
                                    ->exists();

                                if ($hasAttempt) {
                                    $currentManualScore = BasicListeningManualScore::query()
                                        ->where('user_id', $userId)
                                        ->where('user_year', $year)
                                        ->where('meeting', $meeting)
                                        ->value('score');

                                    $newValue = is_numeric($val) ? (int)$val : null;
                                    $currentValue = is_numeric($currentManualScore) ? (int)$currentManualScore : null;

                                    if ($newValue !== $currentValue) {
                                        $attemptViolations[] = "{$user->name} S{$meeting}";
                                        $skippedCount++;
                                        continue;
                                    }
                                }

                                if ($val !== null && $val !== '') {
                                    $row = BasicListeningManualScore::firstOrCreate([
                                        'user_id'   => $userId,
                                        'user_year' => $year,
                                        'meeting'   => $meeting,
                                    ]);

                                    $row->score = max(0, min(100, (int)$val));
                                    $row->save();
                                    $updatedCount++;
                                }
                            }

                            if ($updatedCount > 0) {
                                $grade = BasicListeningGrade::firstOrCreate([
                                    'user_id'   => $userId,
                                    'user_year' => $year,
                                ]);
                                $grade->save();
                            }
                        }

                        if (!empty($attemptViolations)) {
                            $violationList = implode(', ', array_slice($attemptViolations, 0, 5));
                            $moreText = count($attemptViolations) > 5 ? ' dan lainnya...' : '';

                            Notification::make()
                                ->title('Bulk update dengan peringatan')
                                ->body("{$updatedCount} nilai diupdate. Beberapa tidak dapat diubah karena ada attempt: {$violationList}{$moreText}")
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Bulk update selesai')
                                ->body("{$updatedCount} nilai diupdate, {$skippedCount} di-skip (ada attempt)")
                                ->success()
                                ->send();
                        }
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
