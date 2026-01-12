<?php

namespace App\Support;

/**
 * TOEFL ITP Scoring Helper
 * 
 * Converts raw scores to scaled scores (31-68 per section)
 * Total score range: 310-677
 */
class ToeflScoring
{
    /**
     * Listening Comprehension conversion table (50 questions max)
     * Raw score → Scaled score
     */
    private static array $listeningTable = [
        50 => 68, 49 => 67, 48 => 66, 47 => 65, 46 => 63,
        45 => 62, 44 => 61, 43 => 60, 42 => 59, 41 => 58,
        40 => 57, 39 => 57, 38 => 56, 37 => 55, 36 => 54,
        35 => 54, 34 => 53, 33 => 52, 32 => 52, 31 => 51,
        30 => 50, 29 => 49, 28 => 49, 27 => 48, 26 => 47,
        25 => 47, 24 => 46, 23 => 45, 22 => 45, 21 => 44,
        20 => 43, 19 => 42, 18 => 41, 17 => 41, 16 => 40,
        15 => 38, 14 => 37, 13 => 36, 12 => 35, 11 => 34,
        10 => 33, 9 => 32, 8 => 32, 7 => 31, 6 => 31,
        5 => 31, 4 => 31, 3 => 31, 2 => 31, 1 => 31, 0 => 31,
    ];

    /**
     * Structure & Written Expression conversion table (40 questions max)
     */
    private static array $structureTable = [
        40 => 68, 39 => 67, 38 => 65, 37 => 63, 36 => 61,
        35 => 60, 34 => 58, 33 => 57, 32 => 56, 31 => 55,
        30 => 54, 29 => 53, 28 => 52, 27 => 51, 26 => 50,
        25 => 49, 24 => 48, 23 => 47, 22 => 46, 21 => 45,
        20 => 44, 19 => 43, 18 => 42, 17 => 41, 16 => 40,
        15 => 39, 14 => 38, 13 => 37, 12 => 36, 11 => 35,
        10 => 34, 9 => 33, 8 => 32, 7 => 31, 6 => 31,
        5 => 31, 4 => 31, 3 => 31, 2 => 31, 1 => 31, 0 => 31,
    ];

    /**
     * Reading Comprehension conversion table (50 questions max)
     */
    private static array $readingTable = [
        50 => 67, 49 => 66, 48 => 65, 47 => 63, 46 => 61,
        45 => 60, 44 => 59, 43 => 58, 42 => 57, 41 => 56,
        40 => 55, 39 => 54, 38 => 54, 37 => 53, 36 => 52,
        35 => 52, 34 => 51, 33 => 50, 32 => 49, 31 => 49,
        30 => 48, 29 => 47, 28 => 47, 27 => 46, 26 => 45,
        25 => 44, 24 => 44, 23 => 43, 22 => 42, 21 => 41,
        20 => 40, 19 => 39, 18 => 38, 17 => 37, 16 => 36,
        15 => 35, 14 => 34, 13 => 33, 12 => 32, 11 => 32,
        10 => 31, 9 => 31, 8 => 31, 7 => 31, 6 => 31,
        5 => 31, 4 => 31, 3 => 31, 2 => 31, 1 => 31, 0 => 31,
    ];

    /**
     * Scale listening raw score to TOEFL scale
     */
    public static function scaleListening(int $correct, int $total = 50): int
    {
        // Normalize if total != 50
        if ($total !== 50 && $total > 0) {
            $correct = (int) round(($correct / $total) * 50);
        }
        
        $correct = max(0, min(50, $correct));
        return self::$listeningTable[$correct] ?? 31;
    }

    /**
     * Scale structure raw score to TOEFL scale
     */
    public static function scaleStructure(int $correct, int $total = 40): int
    {
        // Normalize if total != 40
        if ($total !== 40 && $total > 0) {
            $correct = (int) round(($correct / $total) * 40);
        }
        
        $correct = max(0, min(40, $correct));
        return self::$structureTable[$correct] ?? 31;
    }

    /**
     * Scale reading raw score to TOEFL scale
     */
    public static function scaleReading(int $correct, int $total = 50): int
    {
        // Normalize if total != 50
        if ($total !== 50 && $total > 0) {
            $correct = (int) round(($correct / $total) * 50);
        }
        
        $correct = max(0, min(50, $correct));
        return self::$readingTable[$correct] ?? 31;
    }

    /**
     * Calculate total TOEFL score from scaled section scores
     * Formula: (Listening + Structure + Reading) × 10 / 3
     */
    public static function totalScore(int $listening, int $structure, int $reading): int
    {
        return (int) round(($listening + $structure + $reading) * 10 / 3);
    }

    /**
     * Get score interpretation
     */
    public static function getInterpretation(int $totalScore): string
    {
        return match (true) {
            $totalScore >= 600 => 'Advanced',
            $totalScore >= 550 => 'Upper Intermediate',
            $totalScore >= 500 => 'Intermediate',
            $totalScore >= 450 => 'Pre-Intermediate',
            $totalScore >= 400 => 'Elementary',
            default => 'Beginner',
        };
    }
}
