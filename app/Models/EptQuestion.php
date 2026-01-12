<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EptQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'section',
        'order',
        'question',
        'audio_url',
        'passage',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'passage_group',
    ];

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(EptQuiz::class, 'quiz_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(EptAnswer::class, 'question_id');
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    public function scopeListening($query)
    {
        return $query->where('section', 'listening');
    }

    public function scopeStructure($query)
    {
        return $query->where('section', 'structure');
    }

    public function scopeReading($query)
    {
        return $query->where('section', 'reading');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('section')->orderBy('order');
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    public function getSectionLabelAttribute(): string
    {
        return match ($this->section) {
            'listening' => 'Listening Comprehension',
            'structure' => 'Structure & Written Expression',
            'reading' => 'Reading Comprehension',
            default => $this->section,
        };
    }

    public function hasAudio(): bool
    {
        return !empty($this->audio_url);
    }

    public function hasPassage(): bool
    {
        return !empty($this->passage);
    }
}
