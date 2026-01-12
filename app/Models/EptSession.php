<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class EptSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'name',
        'date',
        'start_time',
        'end_time',
        'max_participants',
        'passcode',
        'zoom_meeting_id',
        'zoom_passcode',
        'zoom_link',
        'mode',
        'is_active',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active' => 'boolean',
    ];

    // ─────────────────────────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────────────────────────

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(EptQuiz::class, 'quiz_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EptRegistration::class, 'session_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(EptAttempt::class, 'session_id');
    }

    // ─────────────────────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->toDateString());
    }

    public function scopeOnline($query)
    {
        return $query->where('mode', 'online');
    }

    public function scopeOffline($query)
    {
        return $query->where('mode', 'offline');
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    public function getFullDateTimeAttribute(): string
    {
        return $this->date->translatedFormat('l, d F Y') . ' ' . $this->start_time . ' - ' . $this->end_time;
    }

    public function getParticipantCountAttribute(): int
    {
        return $this->registrations()->count();
    }

    public function isFull(): bool
    {
        return $this->participant_count >= $this->max_participants;
    }

    public function isOnline(): bool
    {
        return $this->mode === 'online';
    }

    public function hasZoom(): bool
    {
        return !empty($this->zoom_link);
    }

    public function getStartDateTimeAttribute(): Carbon
    {
        return Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->start_time);
    }

    public function getEndDateTimeAttribute(): Carbon
    {
        return Carbon::parse($this->date->format('Y-m-d') . ' ' . $this->end_time);
    }

    public function isInProgress(): bool
    {
        $now = now();
        return $now->between($this->start_date_time, $this->end_date_time);
    }
}
