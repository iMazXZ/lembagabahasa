<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo};
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BasicListeningSurveyAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'question_id',
        'likert_value',  // 1..5 jika type=likert
        'text_value',    // jika type=text
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(BasicListeningSurveyResponse::class, 'response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(BasicListeningSurveyQuestion::class, 'question_id');
    }
}
