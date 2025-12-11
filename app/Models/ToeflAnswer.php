<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToeflAnswer extends Model
{
    protected $guarded = [];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ToeflAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ToeflQuestion::class, 'question_id');
    }

    public function isCorrect(): bool
    {
        return $this->question && $this->question->isCorrect($this->answer ?? '');
    }
}
