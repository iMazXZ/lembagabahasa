<?php

namespace App\Support;

use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningManualScore;

class BlCompute
{
    /**
     * Rata-rata S1..S5 dengan prioritas skor manual (jika ada),
     * fallback ke skor attempt web (submitted).
     */
    public static function dailyAvgForUser(int $userId, ?string $userYear = null): ?float
  {
      $meetings = [1,2,3,4,5];
      $values = [];

      $manualByMeeting = \App\Models\BasicListeningManualScore::query()
          ->where('user_id', $userId)
          ->when($userYear !== null, fn($q) => $q->where('user_year', $userYear))
          ->whereIn('meeting', $meetings)
          ->pluck('score', 'meeting');

      foreach ($meetings as $m) {
          $manual = $manualByMeeting[$m] ?? null;
          if (is_numeric($manual)) { $values[] = (float)$manual; continue; }

          $attemptScore = \App\Models\BasicListeningAttempt::query()
              ->where('user_id', $userId)
              ->where('session_id', $m)
              ->whereNotNull('submitted_at')
              ->orderByDesc('updated_at')
              ->value('score');

          if (is_numeric($attemptScore)) $values[] = (float)$attemptScore;
      }

      return $values ? round(array_sum($values)/count($values), 2) : null;
  }
}
