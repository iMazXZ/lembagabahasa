<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToeflExam extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_active' => 'bool',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(ToeflPackage::class, 'package_id');
    }

    public function connectCodes(): HasMany
    {
        return $this->hasMany(ToeflConnectCode::class, 'exam_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ToeflAttempt::class, 'exam_id');
    }

    public function isOpen(): bool
    {
        return $this->is_active && $this->scheduled_at <= now();
    }
}
