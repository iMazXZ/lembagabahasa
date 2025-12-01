<?php

namespace App\Filament\Widgets;

use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningManualScore;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class BlSummaryWidget extends Widget
{
    use HasWidgetShield;

    protected static string $view = 'filament.widgets.bl-summary-widget';
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['Admin', 'tutor', 'Tutor']);
    }

    protected function getViewData(): array
    {
        $now       = now();
        $startWeek = Carbon::now()->startOfWeek();

        $attemptsThisWeek = BasicListeningAttempt::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $startWeek)
            ->count();

        $avgScoreWeek = BasicListeningAttempt::query()
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', $startWeek)
            ->whereNotNull('score')
            ->avg('score');

        $pendingSubmit = BasicListeningAttempt::query()
            ->whereNull('submitted_at')
            ->count();

        $manualScores = BasicListeningManualScore::query()->count();

        $latestAttempts = BasicListeningAttempt::query()
            ->with(['user.prody'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->take(5)
            ->get();

        return [
            'attemptsThisWeek' => $attemptsThisWeek,
            'avgScoreWeek'     => $avgScoreWeek ? round($avgScoreWeek, 1) : null,
            'pendingSubmit'    => $pendingSubmit,
            'manualScores'     => $manualScores,
            'latestAttempts'   => $latestAttempts,
            'startWeek'        => $startWeek,
            'now'              => $now,
        ];
    }
}
