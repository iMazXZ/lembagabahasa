<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ToeflPackage extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(ToeflQuestion::class, 'package_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(ToeflExam::class, 'package_id');
    }

    public function listeningQuestions(): HasMany
    {
        return $this->questions()->where('section', 'listening')->orderBy('question_number');
    }

    public function structureQuestions(): HasMany
    {
        return $this->questions()->where('section', 'structure')->orderBy('question_number');
    }

    public function readingQuestions(): HasMany
    {
        return $this->questions()->where('section', 'reading')->orderBy('question_number');
    }
}
