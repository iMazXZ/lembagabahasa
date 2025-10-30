<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Models\BasicListeningGrade;
use App\Support\BlCompute;
use App\Support\BlGrading;
use App\Support\BlSource;

// + ADD: model survey & response
use App\Models\BasicListeningSurvey;
use App\Models\BasicListeningSurveyResponse;

class StudentBasicListeningWidget extends Widget
{
    protected static string $view = 'filament.widgets.student-basic-listening-widget';

    /** Span penuh di grid panel */
    protected int|string|array $columnSpan = 'full';

    /**
     * Render widget hanya jika:
     * - user angkatan >= 2025, dan
     * - sudah ada attendance & final_test numerik.
     */
    public static function canView(): bool
    {
        $u = Auth::user();
        if ($u === null || (int) ($u->year ?? 0) < 2025) {
            return false;
        }

        $grade = BasicListeningGrade::query()
            ->where('user_id', $u->id)
            ->where('user_year', $u->year)
            ->first();

        return $grade !== null
            && is_numeric($grade->attendance)
            && is_numeric($grade->final_test);
    }

    protected function getViewData(): array
    {
        $u = Auth::user();

        $grade = BasicListeningGrade::query()
            ->where('user_id', $u->id)
            ->where('user_year', $u->year)
            ->first();

        // Nilai dasar
        $attendance = is_numeric(optional($grade)->attendance) ? (float) $grade->attendance : null;
        $finalTest  = is_numeric(optional($grade)->final_test)  ? (float) $grade->final_test  : null;

        // Nilai daily dari helper
        $daily = BlCompute::dailyAvgForUser($u->id, $u->year);
        $daily = is_numeric($daily) ? (float) $daily : null;

        // Cache final (jika tersedia)
        $finalNumeric = $grade->final_numeric_cached ?? null;
        $finalLetter  = $grade->final_letter_cached ?? null;

        // === Survey gate ===
        // Cari survey aktif & wajib untuk sertifikat (target final)
        $survey = BasicListeningSurvey::query()
            ->where('require_for_certificate', true)
            ->where('target', 'final')
            ->where('is_active', true)
            ->latest('id')
            ->first();

        // Survey dianggap "required" hanya bila ada & sedang open
        $surveyRequired = $survey ? $survey->isOpen() : false;

        // Cek apakah user sudah submit survey
        $surveyDone = false;
        if ($surveyRequired) {
            $surveyDone = BasicListeningSurveyResponse::where([
                'survey_id'  => $survey->id,
                'user_id'    => $u->id,
                'session_id' => null, // final
            ])->whereNotNull('submitted_at')->exists();
        }

        // Boleh download jika nilai dasar OK + (tidak butuh survey atau survey sudah selesai)
        $baseEligible = is_numeric($attendance) && is_numeric($finalTest);
        $canDownload  = $baseEligible && (! $surveyRequired || $surveyDone);

        return [
            'user'          => $u,
            'attendance'    => $attendance,
            'daily'         => $daily,
            'finalTest'     => $finalTest,
            'finalNumeric'  => $finalNumeric,
            'finalLetter'   => $finalLetter,

            // === variabel untuk Blade ===
            'canDownload'   => $canDownload,
            'surveyRequired'=> $surveyRequired,
            'surveyDone'    => $surveyDone,
            'surveyUrl'     => route('bl.survey.required'),
            'downloadUrl'   => route('bl.certificate.download'),    // unduh
            'previewUrl'    => route('bl.certificate.download', ['inline' => 1]), // preview di browser
        ];
    }
}
