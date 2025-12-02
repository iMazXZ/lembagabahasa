<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningSurvey;
use App\Models\BasicListeningSurveyAnswer;
use App\Models\BasicListeningSurveyResponse;
use App\Models\User;
use App\Models\BasicListeningSupervisor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlSurveyResultsExportController extends Controller
{
    public function __invoke(Request $request)
    {
        $category     = $request->query('category', 'tutor');
        $tutorId      = $request->query('tutor');
        $supervisorId = $request->query('supervisor');

        $survey = BasicListeningSurvey::query()
            ->where('category', $category)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->firstOrFail();

        // Ambil agregasi
        $rows = BasicListeningSurveyAnswer::query()
            ->select([
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
            ->when($category === 'tutor' && $tutorId, function ($q) use ($tutorId) {
                $q->where('basic_listening_survey_responses.tutor_id', $tutorId);
            })
            ->when($category === 'supervisor' && $supervisorId, function ($q) use ($supervisorId) {
                $q->where('basic_listening_survey_responses.supervisor_id', $supervisorId);
            })
            ->groupBy('basic_listening_survey_questions.id', 'basic_listening_survey_questions.question')
            ->orderBy('basic_listening_survey_questions.id')
            ->get();

        // Ringkasan responden & rata-rata
        $respQuery = BasicListeningSurveyResponse::query()->where('survey_id', $survey->id);
        if ($category === 'tutor' && $tutorId) {
            $respQuery->where('tutor_id', $tutorId);
        }
        if ($category === 'supervisor' && $supervisorId) {
            $respQuery->where('supervisor_id', $supervisorId);
        }

        $responseIds = $respQuery->pluck('id');
        $avgOverall  = BasicListeningSurveyAnswer::query()
            ->whereIn('response_id', $responseIds)
            ->avg('likert_value');

        $meta = [
            'category'    => ucfirst($category),
            'tutor'       => $tutorId ? (User::find($tutorId)?->name ?? 'Tutor ID ' . $tutorId) : 'Semua Tutor',
            'supervisor'  => $supervisorId ? (BasicListeningSupervisor::find($supervisorId)?->name ?? 'Lembaga ID ' . $supervisorId) : 'Semua Lembaga',
            'respondents' => $respQuery->count(),
            'avg'         => $avgOverall ? number_format((float) $avgOverall, 2) : '-',
            'generatedAt' => now()->timezone(config('app.timezone','Asia/Jakarta'))->format('d M Y H:i'),
            'surveyTitle' => $survey->title ?? 'Kuesioner ' . ucfirst($category),
        ];

        $pdf = Pdf::loadView('exports.bl-survey-results', [
            'meta'  => $meta,
            'rows'  => $rows,
            'category' => $category,
        ])->setPaper('a4', 'portrait');

        $fileName = 'Hasil_Kuesioner_' . $category . '_' . now()->format('Ymd_His') . '.pdf';
        return $pdf->download($fileName);
    }
}
