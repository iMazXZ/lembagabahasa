<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToeflAttempt extends Model
{
    protected $guarded = [];

    protected $casts = [
        'listening_started_at' => 'datetime',
        'listening_ended_at' => 'datetime',
        'structure_started_at' => 'datetime',
        'structure_ended_at' => 'datetime',
        'reading_started_at' => 'datetime',
        'reading_ended_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(ToeflExam::class, 'exam_id');
    }

    public function connectCode(): BelongsTo
    {
        return $this->belongsTo(ToeflConnectCode::class, 'connect_code_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ToeflAnswer::class, 'attempt_id');
    }

    public function isSubmitted(): bool
    {
        return !is_null($this->submitted_at);
    }

    public function currentSection(): ?string
    {
        if (is_null($this->listening_started_at)) {
            return 'listening';
        }
        if (is_null($this->listening_ended_at)) {
            return 'listening';
        }
        if (is_null($this->structure_started_at)) {
            return 'structure';
        }
        if (is_null($this->structure_ended_at)) {
            return 'structure';
        }
        if (is_null($this->reading_started_at)) {
            return 'reading';
        }
        if (is_null($this->reading_ended_at)) {
            return 'reading';
        }
        return null; // All sections completed
    }

    public function sectionStartedAt(string $section): ?\Carbon\Carbon
    {
        return $this->{$section . '_started_at'};
    }

    public function sectionEndedAt(string $section): ?\Carbon\Carbon
    {
        return $this->{$section . '_ended_at'};
    }

    public function getSectionDuration(string $section): int
    {
        $package = $this->exam->package;
        return match ($section) {
            'listening' => $package->listening_duration,
            'structure' => $package->structure_duration,
            'reading' => $package->reading_duration,
            default => 30,
        };
    }

    public function getSectionExpiresAt(string $section): ?\Carbon\Carbon
    {
        $startedAt = $this->sectionStartedAt($section);
        if (!$startedAt) {
            return null;
        }
        $duration = $this->getSectionDuration($section);
        return $startedAt->copy()->addMinutes($duration);
    }

    public function isSectionExpired(string $section): bool
    {
        $expiresAt = $this->getSectionExpiresAt($section);
        return $expiresAt && now()->isAfter($expiresAt);
    }
}
