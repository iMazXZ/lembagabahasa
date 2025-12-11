<?php

namespace App\Services;

use App\Models\ToeflAttempt;

class ToeflScoringService
{
    /**
     * TOEFL conversion tables.
     * Maps raw score to converted score.
     */
    protected array $listeningTable = [
        50 => 68, 49 => 67, 48 => 66, 47 => 65, 46 => 63,
        45 => 62, 44 => 61, 43 => 60, 42 => 59, 41 => 58,
        40 => 57, 39 => 57, 38 => 56, 37 => 55, 36 => 54,
        35 => 54, 34 => 53, 33 => 52, 32 => 52, 31 => 51,
        30 => 51, 29 => 50, 28 => 49, 27 => 49, 26 => 48,
        25 => 48, 24 => 47, 23 => 47, 22 => 46, 21 => 45,
        20 => 45, 19 => 44, 18 => 43, 17 => 42, 16 => 41,
        15 => 41, 14 => 40, 13 => 39, 12 => 38, 11 => 37,
        10 => 36, 9 => 35, 8 => 34, 7 => 33, 6 => 32,
        5 => 31, 4 => 30, 3 => 29, 2 => 28, 1 => 26, 0 => 24,
    ];

    protected array $structureTable = [
        40 => 68, 39 => 67, 38 => 65, 37 => 63, 36 => 61,
        35 => 60, 34 => 58, 33 => 57, 32 => 56, 31 => 55,
        30 => 54, 29 => 53, 28 => 52, 27 => 51, 26 => 50,
        25 => 49, 24 => 48, 23 => 47, 22 => 46, 21 => 45,
        20 => 44, 19 => 43, 18 => 42, 17 => 41, 16 => 40,
        15 => 40, 14 => 39, 13 => 38, 12 => 37, 11 => 36,
        10 => 35, 9 => 34, 8 => 32, 7 => 31, 6 => 30,
        5 => 29, 4 => 28, 3 => 27, 2 => 26, 1 => 25, 0 => 20,
    ];

    protected array $readingTable = [
        50 => 67, 49 => 66, 48 => 65, 47 => 63, 46 => 61,
        45 => 60, 44 => 59, 43 => 58, 42 => 57, 41 => 56,
        40 => 55, 39 => 54, 38 => 54, 37 => 53, 36 => 52,
        35 => 52, 34 => 51, 33 => 50, 32 => 49, 31 => 49,
        30 => 48, 29 => 47, 28 => 47, 27 => 46, 26 => 45,
        25 => 45, 24 => 44, 23 => 43, 22 => 43, 21 => 42,
        20 => 41, 19 => 41, 18 => 40, 17 => 39, 16 => 38,
        15 => 38, 14 => 37, 13 => 36, 12 => 35, 11 => 34,
        10 => 33, 9 => 32, 8 => 31, 7 => 31, 6 => 30,
        5 => 29, 4 => 28, 3 => 27, 2 => 26, 1 => 25, 0 => 21,
    ];

    /**
     * Calculate all scores for an attempt.
     */
    public function calculate(ToeflAttempt $attempt): array
    {
        $package = $attempt->exam->package;
        $answers = $attempt->answers()->with('question')->get();

        // Count correct answers per section
        $listeningCorrect = $this->countCorrect($answers, 'listening');
        $structureCorrect = $this->countCorrect($answers, 'structure');
        $readingCorrect = $this->countCorrect($answers, 'reading');

        // Convert to TOEFL scores
        $listeningScore = $this->convertScore($listeningCorrect, 'listening');
        $structureScore = $this->convertScore($structureCorrect, 'structure');
        $readingScore = $this->convertScore($readingCorrect, 'reading');

        // Calculate total: (L + S + R) * 10 / 3
        $totalScore = (int) round(($listeningScore + $structureScore + $readingScore) * 10 / 3);

        return [
            'listening_correct' => $listeningCorrect,
            'structure_correct' => $structureCorrect,
            'reading_correct' => $readingCorrect,
            'listening_score' => $listeningScore,
            'structure_score' => $structureScore,
            'reading_score' => $readingScore,
            'total_score' => $totalScore,
        ];
    }

    /**
     * Count correct answers for a section.
     */
    protected function countCorrect($answers, string $section): int
    {
        return $answers->filter(function ($answer) use ($section) {
            return $answer->question &&
                   $answer->question->section === $section &&
                   $answer->isCorrect();
        })->count();
    }

    /**
     * Convert raw score to TOEFL scaled score.
     */
    protected function convertScore(int $rawScore, string $section): int
    {
        $table = match ($section) {
            'listening' => $this->listeningTable,
            'structure' => $this->structureTable,
            'reading' => $this->readingTable,
            default => [],
        };

        // Clamp to valid range
        $maxScore = max(array_keys($table));
        $rawScore = min($rawScore, $maxScore);
        $rawScore = max($rawScore, 0);

        return $table[$rawScore] ?? 20;
    }
}
