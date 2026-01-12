<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EptQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'listening_duration',
        'structure_duration',
        'reading_duration',
        'listening_count',
        'structure_count',
        'reading_count',
        'scoring_config',
        'is_active',
    ];

    protected $casts = [
        'scoring_config' => 'array',
        'is_active' => 'boolean',
    ];

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function questions(): HasMany
    {
        return $this->hasMany(EptQuestion::class, 'quiz_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(EptSession::class, 'quiz_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(EptAttempt::class, 'quiz_id');
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    public function getTotalDurationAttribute(): int
    {
        return $this->listening_duration + $this->structure_duration + $this->reading_duration;
    }

    public function getTotalQuestionsAttribute(): int
    {
        return $this->listening_count + $this->structure_count + $this->reading_count;
    }

    public function getQuestionsBySection(string $section)
    {
        return $this->questions()
            ->where('section', $section)
            ->orderBy('order')
            ->get();
    }
}
