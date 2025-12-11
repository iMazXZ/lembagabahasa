<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToeflConnectCode extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'bool',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(ToeflExam::class, 'exam_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ToeflAttempt::class, 'connect_code_id');
    }

    public function withinWindow(): bool
    {
        $now = now();
        return $this->is_active && $now->between($this->starts_at, $this->ends_at);
    }

    public function usageCount(): int
    {
        return $this->attempts()->count();
    }

    public function hasReachedLimit(): bool
    {
        if (is_null($this->max_uses)) {
            return false;
        }
        return $this->usageCount() >= $this->max_uses;
    }
}
