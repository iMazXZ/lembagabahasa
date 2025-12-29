<?php

namespace App\Filament\Pages;

use App\Models\BasicListeningSurvey;
use App\Models\BasicListeningSurveyAnswer;
use App\Models\BasicListeningSurveyResponse;
use App\Models\BasicListeningCategory;
use App\Models\BasicListeningSupervisor;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

class BlSurveyResults extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    /** ====== Navigasi & metadata sidebar ====== */
    protected static ?string $navigationIcon  = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Hasil Kuesioner';
    protected static ?string $title           = 'Hasil Kuesioner';
    protected static ?string $navigationGroup = 'Basic Listening';
    protected static ?string $navigationParentItem = 'Kuesioner';
    protected static ?int $navigationSort = 2;
    protected static ?string $slug            = 'basic-listening/survey-results';

    /** Blade view untuk page ini */
    protected static string $view = 'filament.pages.bl-survey-results';

    /** Selalu tampil di sidebar (akses bisa dikontrol via Shield/Policy) */
    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canAccess(): bool
    {
        return true;
    }

    /** ====== State filter ====== */
    public ?string $category = 'tutor'; // default di-set ke kategori aktif pertama
    public ?int $tutorId = null;
    public ?int $supervisorId = null;

    public function mount(): void
    {
        $options = $this->categoryOptions();
        $this->category = array_key_first($options) ?? 'tutor';
        $this->tutorId = null;
        $this->supervisorId = null;
        $this->form->fill([
            'category'    => $this->category,
            'tutorId'     => $this->tutorId,
            'supervisorId'=> $this->supervisorId,
        ]);
    }

    /** =========================
     *  FORM (Filter di atas tabel)
     *  ========================= */
    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\Select::make('category')
                    ->label('Kategori')
                    ->options(fn () => $this->categoryOptions())
                    ->default(fn () => array_key_first($this->categoryOptions()))
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        // Reset child filter ketika kategori berubah
                        $set('tutorId', null);
                        $set('supervisorId', null);
                        $this->tutorId = null;
                        $this->supervisorId = null;
                        if (method_exists($this, 'resetTable')) {
                            $this->resetTable();
                        }
                    })
                    ->columnSpan(12),

                Forms\Components\Select::make('tutorId')
                    ->label('Pilih Tutor')
                    ->options(function () {
                        // Ambil survey aktif untuk kategori tutor
                        $survey = BasicListeningSurvey::query()
                            ->where('category', 'tutor')
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderByDesc('id')
                            ->first();

                        // Hitung jumlah response per tutor
                        $responseCounts = $survey
                            ? BasicListeningSurveyResponse::query()
                                ->where('survey_id', $survey->id)
                                ->whereNotNull('tutor_id')
                                ->selectRaw('tutor_id, COUNT(*) as count')
                                ->groupBy('tutor_id')
                                ->pluck('count', 'tutor_id')
                                ->all()
                            : [];

                        return User::query()
                            ->role('tutor')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function ($user) use ($responseCounts) {
                                $count = $responseCounts[$user->id] ?? 0;
                                $label = $count > 0 
                                    ? "{$user->name} ({$count})" 
                                    : $user->name;
                                return [$user->id => $label];
                            })
                            ->all();
                    })
                    ->visible(fn (Get $get) => $get('category') === 'tutor')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->tutorId = $state ? (int) $state : null;
                        if (method_exists($this, 'resetTable')) $this->resetTable();
                    })
                    ->columnSpan(6),

                Forms\Components\Select::make('supervisorId')
                    ->label('Pilih Supervisor')
                    ->options(function () {
                        // Ambil survey aktif untuk kategori supervisor
                        $survey = BasicListeningSurvey::query()
                            ->where('category', 'supervisor')
                            ->where('is_active', true)
                            ->orderBy('sort_order')
                            ->orderByDesc('id')
                            ->first();

                        // Hitung jumlah response per supervisor
                        $responseCounts = $survey
                            ? BasicListeningSurveyResponse::query()
                                ->where('survey_id', $survey->id)
                                ->whereNotNull('supervisor_id')
                                ->selectRaw('supervisor_id, COUNT(*) as count')
                                ->groupBy('supervisor_id')
                                ->pluck('count', 'supervisor_id')
                                ->all()
                            : [];

                        return BasicListeningSupervisor::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function ($supervisor) use ($responseCounts) {
                                $count = $responseCounts[$supervisor->id] ?? 0;
                                $label = $count > 0 
                                    ? "{$supervisor->name} ({$count})" 
                                    : $supervisor->name;
                                return [$supervisor->id => $label];
                            })
                            ->all();
                    })
                    ->visible(fn (Get $get) => $get('category') === 'supervisor')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->supervisorId = $state ? (int) $state : null;
                        if (method_exists($this, 'resetTable')) $this->resetTable();
                    })
                    ->columnSpan(6),
            ]),
        ]);
    }

    /** =========================
     *  TABEL (Ringkasan per-pertanyaan)
     *  ========================= */
    public function table(Table $table): Table
    {
        return $table
            // Penting: pakai closure agar query dipanggil ulang saat state filter berubah.
            ->query(fn () => $this->buildAggregateQuery())
            ->columns([
                TextColumn::make('question_text')
                    ->label('Pertanyaan')
                    ->wrap()
                    ->limit(120)
                    ->sortable(),

                TextColumn::make('avg_score')
                    ->label('Rata-rata')
                    ->state(fn ($record) => number_format((float) $record->avg_score, 2))
                    ->sortable()
                    ->color(fn ($record) => $this->colorForAvg((float) $record->avg_score))
                    ->alignCenter(),

                TextColumn::make('responses_count')
                    ->label('Responden')
                    ->sortable()
                    ->alignRight(),
            ])
            ->defaultSort('id')
            ->paginated(false)
            ->striped()
            ->emptyStateHeading('Belum ada data')
            ->emptyStateDescription('Atur kategori/filter di atas atau pastikan respon kuesioner sudah masuk.');
    }

    /** Warna indikator rata-rata */
    private function colorForAvg(float $avg): string
    {
        if ($avg >= 4.5) return 'success';
        if ($avg >= 3.5) return 'warning';
        return 'danger';
    }

    /** Query agregasi rata-rata likert per pertanyaan */
    private function buildAggregateQuery(): Builder
    {
        // Ambil survey aktif terakhir untuk kategori yang dipilih
        $survey = BasicListeningSurvey::query()
            ->where('category', $this->category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->first();

        if (! $survey) {
            // Query kosong agar tabel tidak error (tidak ada survey aktif)
            return BasicListeningSurveyAnswer::query()->whereRaw('1=0');
        }

        $q = BasicListeningSurveyAnswer::query()
            ->select([
                // Filament butuh kolom 'id' untuk key record:
                'basic_listening_survey_questions.id as id',
                'basic_listening_survey_questions.question as question_text',
                DB::raw('AVG(basic_listening_survey_answers.likert_value) as avg_score'),
                DB::raw('COUNT(DISTINCT basic_listening_survey_responses.user_id) as responses_count'),
            ])
            ->join(
                'basic_listening_survey_responses',
                'basic_listening_survey_answers.response_id',
                '=',
                'basic_listening_survey_responses.id'
            )
            ->join(
                'basic_listening_survey_questions',
                'basic_listening_survey_answers.question_id',
                '=',
                'basic_listening_survey_questions.id'
            )
            ->where('basic_listening_survey_responses.survey_id', $survey->id)
            ->whereNotNull('basic_listening_survey_answers.likert_value')
            ->groupBy('basic_listening_survey_questions.id', 'basic_listening_survey_questions.question')
            ->orderBy('basic_listening_survey_questions.id');

        // Filter tambahan berdasarkan kategori terpilih
        if ($this->category === 'tutor') {
            if ($this->tutorId) {
                $q->where('basic_listening_survey_responses.tutor_id', $this->tutorId);
            }
        } elseif ($this->category === 'supervisor') {
            if ($this->supervisorId) {
                $q->where('basic_listening_survey_responses.supervisor_id', $this->supervisorId);
            }
        }

        return $q;
    }

    /** Ringkasan angka untuk ditampilkan di header (opsional, dipakai di Blade) */
    public function getTopStats(): array
    {
        $survey = BasicListeningSurvey::query()
            ->where('category', $this->category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->first();

        if (! $survey) {
            return [
                'avg' => null, 
                'respondents' => 0,
                'likertDistribution' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
                'lowScoreCount' => 0,
                'totalQuestions' => 0,
            ];
        }

        // Build base query untuk responses
        $respQuery = BasicListeningSurveyResponse::query()->where('survey_id', $survey->id);

        if ($this->category === 'tutor' && $this->tutorId) {
            $respQuery->where('tutor_id', $this->tutorId);
        }

        if ($this->category === 'supervisor' && $this->supervisorId) {
            $respQuery->where('supervisor_id', $this->supervisorId);
        }

        // Subquery untuk filter answers
        $responseSubquery = function ($query) use ($survey) {
            $query->select('id')
                ->from('basic_listening_survey_responses')
                ->where('survey_id', $survey->id);

            if ($this->category === 'tutor' && $this->tutorId) {
                $query->where('tutor_id', $this->tutorId);
            }

            if ($this->category === 'supervisor' && $this->supervisorId) {
                $query->where('supervisor_id', $this->supervisorId);
            }
        };

        // Rata-rata keseluruhan
        $avg = BasicListeningSurveyAnswer::query()
            ->whereIn('response_id', $responseSubquery)
            ->avg('likert_value');

        // Distribusi Likert (1-5)
        $likertDistribution = BasicListeningSurveyAnswer::query()
            ->whereIn('response_id', $responseSubquery)
            ->whereNotNull('likert_value')
            ->selectRaw('likert_value, COUNT(*) as count')
            ->groupBy('likert_value')
            ->pluck('count', 'likert_value')
            ->all();

        // Pastikan semua nilai 1-5 ada
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $likertDistribution[$i] ?? 0;
        }

        // Hitung pertanyaan dengan rata-rata < 3.5
        $lowScoreQuestions = DB::table('basic_listening_survey_answers as a')
            ->join('basic_listening_survey_responses as r', 'a.response_id', '=', 'r.id')
            ->select('a.question_id')
            ->where('r.survey_id', $survey->id)
            ->when($this->category === 'tutor' && $this->tutorId, fn($q) => $q->where('r.tutor_id', $this->tutorId))
            ->when($this->category === 'supervisor' && $this->supervisorId, fn($q) => $q->where('r.supervisor_id', $this->supervisorId))
            ->whereNotNull('a.likert_value')
            ->groupBy('a.question_id')
            ->havingRaw('AVG(a.likert_value) < 3.5')
            ->get()
            ->count();

        $totalQuestions = $survey->questions()->count();

        return [
            'avg'               => $avg ? number_format((float) $avg, 2) : null,
            'respondents'       => $respQuery->count(),
            'likertDistribution'=> $distribution,
            'lowScoreCount'     => $lowScoreQuestions,
            'totalQuestions'    => $totalQuestions,
        ];
    }

    /** State untuk modal detail Likert */
    public bool $showLikertModal = false;
    public ?int $selectedLikertValue = null;
    public array $likertRespondents = [];
    public int $likertLimit = 20;
    public int $likertTotal = 0;

    /** Tampilkan modal dengan daftar responden berdasarkan nilai Likert */
    public function showLikertDetail(int $likertValue): void
    {
        $survey = BasicListeningSurvey::query()
            ->where('category', $this->category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->first();

        if (! $survey) {
            $this->likertRespondents = [];
            return;
        }

        // Query untuk mendapatkan responden yang memberikan nilai tertentu
        $query = BasicListeningSurveyAnswer::query()
            ->join('basic_listening_survey_responses as r', 'basic_listening_survey_answers.response_id', '=', 'r.id')
            ->join('users as u', 'r.user_id', '=', 'u.id')
            ->join('basic_listening_survey_questions as q', 'basic_listening_survey_answers.question_id', '=', 'q.id')
            ->where('r.survey_id', $survey->id)
            ->where('basic_listening_survey_answers.likert_value', $likertValue);

        if ($this->category === 'tutor' && $this->tutorId) {
            $query->where('r.tutor_id', $this->tutorId);
        }

        if ($this->category === 'supervisor' && $this->supervisorId) {
            $query->where('r.supervisor_id', $this->supervisorId);
        }

        // Hitung total dulu
        $this->likertTotal = (clone $query)->count();

        $this->likertRespondents = $query
            ->select([
                'u.name as respondent_name',
                'q.question as question_text',
                'basic_listening_survey_answers.likert_value',
            ])
            ->orderBy('u.name')
            ->limit($this->likertLimit)
            ->get()
            ->toArray();

        $this->selectedLikertValue = $likertValue;
        $this->showLikertModal = true;
    }

    /** Load more data */
    public function loadMoreLikert(): void
    {
        $this->likertLimit += 20;
        if ($this->selectedLikertValue) {
            $this->showLikertDetail($this->selectedLikertValue);
        }
    }

    /** Tutup modal */
    public function closeLikertModal(): void
    {
        $this->showLikertModal = false;
        $this->selectedLikertValue = null;
        $this->likertRespondents = [];
        $this->likertLimit = 20;
        $this->likertTotal = 0;
    }

    /** Widget header - kosongkan karena stats dirender inline di blade */
    public function getHeaderWidgets(): array
    {
        return [];
    }

    /** Tombol header */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-arrow-down-on-square')
                ->color('primary')
                ->modalWidth('lg')
                ->form([
                    Select::make('category')
                        ->label('Kategori')
                        ->options(fn () => $this->categoryOptions())
                        ->default(fn () => $this->category)
                        ->reactive(),

                    Forms\Components\ToggleButtons::make('mode')
                        ->label('Mode export')
                        ->options(fn (Get $get) => match ($get('category')) {
                            'tutor' => [
                                'per_entity' => 'Per Tutor (1 file, multi halaman)',
                                'single'     => 'Tutor tertentu saja',
                                'overall'    => 'Rekap keseluruhan',
                            ],
                            'supervisor' => [
                                'per_entity' => 'Per Supervisor (1 file, multi halaman)',
                                'single'     => 'Supervisor tertentu saja',
                                'overall'    => 'Rekap keseluruhan',
                            ],
                            default => [
                                'overall' => 'Rekap keseluruhan',
                            ],
                        })
                        ->default(fn (Get $get) => in_array($get('category'), ['tutor', 'supervisor'], true) ? 'per_entity' : 'overall')
                        ->live()
                        ->inline()
                        ->columnSpanFull()
                        ->helperText('Pilih “Per …” untuk satu file dengan halaman terpisah per entitas, atau pilih salah satu entitas saja.'),

                    Select::make('tutor')
                        ->label('Tutor (opsional)')
                        ->options(fn () => User::query()
                            ->role('tutor')
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => $get('category') === 'tutor' && $get('mode') === 'single'),

                    Select::make('supervisor')
                        ->label('Supervisor (opsional)')
                        ->options(fn () => BasicListeningSupervisor::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->visible(fn (Get $get) => $get('category') === 'supervisor' && $get('mode') === 'single'),
                ])
                ->action(function (array $data) {
                    $mode = $data['mode'] ?? 'overall';
                    $category = $data['category'] ?? 'tutor';
                    $tutor = $mode === 'single' && $category === 'tutor' ? ($data['tutor'] ?? null) : null;
                    $supervisor = $mode === 'single' && $category === 'supervisor' ? ($data['supervisor'] ?? null) : null;

                    return redirect()->route('bl.survey-results.export', [
                        'category'   => $category,
                        'mode'       => $mode,
                        'tutor'      => $tutor,
                        'supervisor' => $supervisor,
                    ]);
                }),
        ];
    }

    /** Ambil opsi kategori aktif, fallback ke default jika kosong */
    private function categoryOptions(): array
    {
        $options = BasicListeningCategory::query()
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->pluck('name', 'slug')
            ->all();

        return $options ?: [
            'tutor'      => 'Tutor',
            'materi'     => 'Materi',
            'supervisor' => 'Supervisor',
            'institute'  => 'Lembaga',
        ];
    }
}
