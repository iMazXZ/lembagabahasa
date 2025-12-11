<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToeflQuestion extends Model
{
    protected $guarded = [];

    public function package(): BelongsTo
    {
        return $this->belongsTo(ToeflPackage::class, 'package_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ToeflAnswer::class, 'question_id');
    }

    public function isCorrect(string $answer): bool
    {
        return strtoupper($answer) === strtoupper($this->correct_answer);
    }
}
