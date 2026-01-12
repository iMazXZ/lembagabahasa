<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class EptAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'registration_id',
        'quiz_id',
        'session_id',
        'started_at',
        'listening_started_at',
        'structure_started_at',
        'reading_started_at',
        'submitted_at',
        'current_section',
        'score_listening',
        'score_structure',
        'score_reading',
        'scaled_listening',
        'scaled_structure',
        'scaled_reading',
        'total_score',
        'selfie_path',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'listening_started_at' => 'datetime',
        'structure_started_at' => 'datetime',
        'reading_started_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EptRegistration::class, 'registration_id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(EptQuiz::class, 'quiz_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(EptSession::class, 'session_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EptAnswer::class, 'attempt_id');
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('submitted_at');
    }

    public function scopeInProgress($query)
    {
        return $query->whereNotNull('started_at')->whereNull('submitted_at');
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->submitted_at !== null;
    }

    public function isInProgress(): bool
    {
        return $this->started_at !== null && $this->submitted_at === null;
    }

    public function getCurrentSectionLabelAttribute(): string
    {
        return match ($this->current_section) {
            'listening' => 'Listening Comprehension',
            'structure' => 'Structure & Written Expression',
            'reading' => 'Reading Comprehension',
            default => $this->current_section,
        };
    }

    /**
     * Get remaining seconds for current section
     */
    public function getRemainingSecondsForSection(): int
    {
        if (!$this->quiz || $this->isCompleted()) {
            return 0;
        }

        $section = $this->current_section;
        $startedAt = match ($section) {
            'listening' => $this->listening_started_at,
            'structure' => $this->structure_started_at,
            'reading' => $this->reading_started_at,
            default => null,
        };

        if (!$startedAt) {
            return 0;
        }

        $durationMinutes = match ($section) {
            'listening' => $this->quiz->listening_duration,
            'structure' => $this->quiz->structure_duration,
            'reading' => $this->quiz->reading_duration,
            default => 0,
        };

        $deadline = $startedAt->copy()->addMinutes($durationMinutes);
        $remaining = now()->diffInSeconds($deadline, false);

        return max(0, $remaining);
    }

    /**
     * Get answer for a specific question
     */
    public function getAnswerFor(int $questionId): ?EptAnswer
    {
        return $this->answers->firstWhere('question_id', $questionId);
    }

    /**
     * Count answered questions for a section
     */
    public function getAnsweredCountForSection(string $section): int
    {
        return $this->answers()
            ->whereHas('question', fn($q) => $q->where('section', $section))
            ->whereNotNull('answer')
            ->count();
    }
}
