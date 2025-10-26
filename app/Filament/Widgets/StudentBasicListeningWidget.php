<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use App\Models\BasicListeningGrade;
use App\Support\BlCompute;
use App\Support\BlSource;
use App\Support\BlGrading;

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

        // Ambil nilai akhir via helper (fallback otomatis)
        [$finalNumeric, $finalLetter] = BlSource::finalFor($u);

        // Jika cache belum ada, hitung manual dan simpan
        if ($finalNumeric === null && $attendance !== null && $finalTest !== null) {
            $parts = array_values(array_filter([$attendance, $daily, $finalTest], fn ($v) => $v !== null));
            if ($parts) {
                $finalNumeric = round(array_sum($parts) / count($parts), 2);
                $finalLetter  = BlGrading::letter($finalNumeric);

                $grade->final_numeric_cached = $finalNumeric;
                $grade->final_letter_cached  = $finalLetter;
                $grade->save();
            }
        }

        // Attendance & Final Test terisi ⇒ boleh download
        $canDownload = is_numeric($attendance) && is_numeric($finalTest);

        return [
            'user'         => $u,
            'attendance'   => $attendance,
            'daily'        => $daily,
            'finalTest'    => $finalTest,
            'finalNumeric' => $finalNumeric,
            'finalLetter'  => $finalLetter,
            'canDownload'  => $canDownload,
        ];
    }
}
