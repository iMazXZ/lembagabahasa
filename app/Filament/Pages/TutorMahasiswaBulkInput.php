<?php

namespace App\Filament\Pages;

use App\Models\BasicListeningGrade;
use App\Models\BasicListeningManualScore;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class TutorMahasiswaBulkInput extends Page implements HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationLabel = 'Input Nilai Mahasiswa';
    protected static ?string $navigationIcon  = 'heroicon-o-document-plus';
    protected static ?string $title           = 'Input Nilai Mahasiswa';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.tutor-mahasiswa-bulk-input';

    public bool $showDownloadButton = false;
    public array $data = [
        'student_ids' => [],
        'students'    => [],
    ];

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->hasAnyRole(['Admin', 'tutor']) ?? false;
    }

    public function mount(): void
    {
        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Cari & Pilih Mahasiswa')
                    ->description('Cari berdasarkan SRN atau nama, pilih beberapa sekaligus lalu data otomatis ditarik ke tabel input.')
                    ->schema([
                        Select::make('student_ids')
                            ->label('Mahasiswa')
                            ->multiple()
                            ->searchable()
                            ->placeholder('Ketik SRN atau nama...')
                            ->helperText('Pilih beberapa mahasiswa sekaligus untuk isi nilai bersama.')
                            ->getSearchResultsUsing(fn (string $search) => $this->searchStudents($search))
                            ->getOptionLabelsUsing(fn (array $values) => $this->getStudentLabels(collect($values)))
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('students', $this->buildStudentsState((array) $state));
                                $this->showDownloadButton = false;
                            }),
                    ])
                    ->columns(1),

                Section::make('Input Nilai')
                    ->description('Placeholder menampilkan skor attempt terbaru. Kosongkan untuk memakai skor attempt.')
                    ->schema([
                        Repeater::make('students')
                            ->label('Nilai per Mahasiswa')
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(1)
                            ->itemLabel(fn (array $state): string => ($state['srn'] ?? '-') . ' — ' . ($state['name'] ?? 'Mahasiswa'))
                            ->visible(fn (callable $get) => filled($get('students')))
                            ->schema([
                                Forms\Components\Hidden::make('user_id'),
                                Forms\Components\Hidden::make('final_attempt_score'),
                                Forms\Components\Hidden::make('attempt_score_1'),
                                Forms\Components\Hidden::make('attempt_score_2'),
                                Forms\Components\Hidden::make('attempt_score_3'),
                                Forms\Components\Hidden::make('attempt_score_4'),
                                Forms\Components\Hidden::make('attempt_score_5'),

                                Grid::make(2)->schema([
                                    TextInput::make('attendance')
                                        ->label('Attendance')
                                        ->numeric()
                                        ->maxValue(100),
                                    TextInput::make('final_test')
                                        ->label('Final Exam')
                                        ->numeric()
                                        ->maxValue(100)
                                        ->placeholder(fn ($get) => $get('final_attempt_score'))
                                        ->helperText(fn ($get) => $get('final_attempt_score') ? 'Skor attempt: ' . $get('final_attempt_score') : 'Belum ada attempt'),
                                ]),

                                Grid::make(5)->schema($this->dailyInputsSchema()),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $students = $state['students'] ?? [];

        foreach ($students as $row) {
            $this->saveStudentRow($row);
        }

        $downloadUrl = $this->buildDownloadUrl($students);
        $this->showDownloadButton = (bool) $downloadUrl;

        Notification::make()
            ->title('Nilai disimpan')
            ->body('Ingin unduh Excel untuk data ini?')
            ->success()
            ->actions([
                NotificationAction::make('download_excel')
                    ->label('Download Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url($downloadUrl)
                    ->openUrlInNewTab()
                    ->visible((bool) $downloadUrl),
            ])
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Nilai')
                ->submit('save')
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }

    public function getSelectedCountProperty(): int
    {
        return count($this->data['students'] ?? []);
    }

    public function getDownloadUrlProperty(): ?string
    {
        return $this->buildDownloadUrl($this->data['students'] ?? []);
    }

    private function buildDownloadUrl(array $students): ?string
    {
        $ids = collect($students)->pluck('user_id')->filter()->unique()->values()->all();
        if (empty($ids)) {
            return null;
        }

        return URL::temporarySignedRoute(
            'bl.tutor-mahasiswa.export',
            now()->addMinutes(10),
            [
                'ids' => implode(',', $ids),
            ]
        );
    }

    private function searchStudents(string $term): array
    {
        if (trim($term) === '') {
            return [];
        }

        return User::query()
            ->whereNotNull('srn')
            ->where(function ($q) use ($term) {
                $q->where('srn', 'like', "%{$term}%")
                    ->orWhere('name', 'like', "%{$term}%");
            })
            ->orderBy('srn')
            ->limit(20)
            ->pluck('name', 'id')
            ->toArray();
    }

    private function getStudentLabels(Collection $ids): array
    {
        return User::query()
            ->whereIn('id', $ids)
            ->orderBy('srn')
            ->get()
            ->mapWithKeys(fn (User $u) => [$u->id => "{$u->srn} — {$u->name}"])
            ->toArray();
    }

    private function buildStudentsState(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        /** @var EloquentCollection<int, User> $users */
        $users = User::query()
            ->with([
                'basicListeningGrade',
                'basicListeningManualScores',
                'basicListeningAttempts' => fn ($q) => $q->whereNotNull('submitted_at'),
            ])
            ->whereIn('id', $ids)
            ->orderBy('srn')
            ->get();

        return $users->map(function (User $u) {
            $manuals = $u->basicListeningManualScores->pluck('score', 'meeting');
            $attempts = $u->basicListeningAttempts
                ->groupBy('session_id')
                ->map(fn (Collection $g) => $g->sortByDesc('submitted_at')->first());

            $row = [
                'user_id'             => $u->id,
                'name'                => $u->name,
                'srn'                 => $u->srn,
                'attendance'          => $u->basicListeningGrade?->attendance,
                'final_attempt_score' => $this->attemptScore($attempts, 6),
                'final_test'          => $u->basicListeningGrade?->final_test ?? $this->attemptScore($attempts, 6),
            ];

            foreach (range(1, 5) as $m) {
                $row["m{$m}"] = $manuals[$m] ?? null;
                $row["attempt_score_{$m}"] = $this->attemptScore($attempts, $m);
            }

            return $row;
        })->values()->toArray();
    }

    private function attemptScore(Collection $attempts, int $session): ?float
    {
        $attempt = $attempts[$session] ?? null;
        $score = $attempt?->score ?? null;
        return is_numeric($score) ? (float) $score : null;
    }

    private function dailyInputsSchema(): array
    {
        $fields = [];
        foreach (range(1, 5) as $m) {
            $fields[] = TextInput::make("m{$m}")
                ->label("Daily {$m}")
                ->numeric()
                ->maxValue(100)
                ->placeholder(fn ($get) => $get("attempt_score_{$m}"))
                ->helperText(fn ($get) => $get("attempt_score_{$m}") ? 'Skor attempt: ' . $get("attempt_score_{$m}") : 'Belum ada attempt');
        }
        return $fields;
    }

    private function saveStudentRow(array $row): void
    {
        $user = User::find($row['user_id'] ?? null);
        if (! $user) {
            return;
        }

        foreach (range(1, 5) as $m) {
            if (! array_key_exists("m{$m}", $row)) {
                continue;
            }

            $val = $row["m{$m}"];
            $scoreRow = BasicListeningManualScore::firstOrCreate(
                ['user_id' => $user->id, 'user_year' => $user->year, 'meeting' => $m]
            );
            $scoreRow->score = ($val === '' || $val === null) ? null : (int) $val;
            $scoreRow->save();
        }

        $grade = BasicListeningGrade::firstOrCreate(['user_id' => $user->id, 'user_year' => $user->year]);
        $grade->attendance = $row['attendance'] ?? null;
        $grade->final_test = $row['final_test'] ?? null;
        $grade->save();
    }
}
