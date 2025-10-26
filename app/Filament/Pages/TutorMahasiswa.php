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
use Illuminate\Database\Eloquent\Builder;

class TutorMahasiswa extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon   = 'heroicon-o-users';
    protected static ?string $navigationLabel  = 'Mahasiswa Binaan';
    protected static ?string $title            = 'Mahasiswa Binaan';
    protected static ?string $navigationGroup  = 'Basic Listening';
    protected static ?int    $navigationSort   = 15;

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
                // Hanya filter Prodi saja, filter angkatan dihapus karena sudah dihandle di baseQuery()
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
            ])
            ->actions([
                // === Edit Daily per meeting (S1..S5)
                Tables\Actions\Action::make('edit_daily')
                    ->label('Edit Daily')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'tutor']))
                    ->form([
                        Forms\Components\Grid::make(5)->schema([
                            TextInput::make('m1')->label('S1')->numeric()->minValue(0)->maxValue(100),
                            TextInput::make('m2')->label('S2')->numeric()->minValue(0)->maxValue(100),
                            TextInput::make('m3')->label('S3')->numeric()->minValue(0)->maxValue(100),
                            TextInput::make('m4')->label('S4')->numeric()->minValue(0)->maxValue(100),
                            TextInput::make('m5')->label('S5')->numeric()->minValue(0)->maxValue(100),
                        ]),
                    ])
                    ->mountUsing(function (ComponentContainer $form, User $record) {
                        // Prefill dari manual atau attempt
                        $manual = BasicListeningManualScore::query()
                            ->where('user_id', $record->id)
                            ->where('user_year', $record->year)
                            ->whereIn('meeting', [1,2,3,4,5])
                            ->pluck('score', 'meeting')
                            ->all();

                        $prefill = [];
                        foreach ([1,2,3,4,5] as $m) {
                            $prefill[$m] = $manual[$m] ?? null;
                            if (!is_numeric($prefill[$m])) {
                                $attemptScore = \App\Models\BasicListeningAttempt::query()
                                    ->where('user_id', $record->id)
                                    ->where('session_id', $m)
                                    ->whereNotNull('submitted_at')
                                    ->orderByDesc('updated_at')
                                    ->value('score');
                                if (is_numeric($attemptScore)) {
                                    $prefill[$m] = (float) $attemptScore;
                                }
                            }
                        }

                        $form->fill([
                            'm1' => $prefill[1],
                            'm2' => $prefill[2],
                            'm3' => $prefill[3],
                            'm4' => $prefill[4],
                            'm5' => $prefill[5],
                        ]);
                    })
                    ->action(function (User $record, array $data) {
                        $year = $record->year;

                        foreach (['m1'=>1,'m2'=>2,'m3'=>3,'m4'=>4,'m5'=>5] as $key => $meeting) {
                            $val = $data[$key] ?? null;

                            $row = BasicListeningManualScore::firstOrCreate([
                                'user_id'   => $record->id,
                                'user_year' => $year,
                                'meeting'   => $meeting,
                            ]);

                            $row->score = is_numeric($val) ? max(0, min(100, (int)$val)) : null;
                            $row->save();
                        }

                        // Perbarui cache otomatis
                        $grade = BasicListeningGrade::firstOrCreate([
                            'user_id'   => $record->id,
                            'user_year' => $year,
                        ]);
                        $grade->save();
                    })
                    ->modalWidth('md')
                    ->slideOver(),

                // === Edit Attendance & Final
                Tables\Actions\Action::make('edit_scores')
                    ->label('Edit Nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->visible(fn () => auth()->user()?->hasAnyRole(['Admin', 'tutor']))
                    ->form(function (User $record) {
                        $g = $record->basicListeningGrade;
                        return [
                            Forms\Components\Placeholder::make('mhs')
                                ->label('Mahasiswa')
                                ->content($record->srn . ' — ' . $record->name),

                            TextInput::make('attendance')
                                ->label('Attendance (0–100)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->default($g?->attendance),

                            TextInput::make('final_test')
                                ->label('Final Test (0–100)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
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
                    })
                    ->modalWidth('md')
                    ->slideOver(),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada data')
            ->emptyStateDescription('Pastikan prodi yang Anda ampu sudah diatur.');
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