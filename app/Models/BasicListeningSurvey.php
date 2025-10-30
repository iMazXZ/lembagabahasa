<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{HasMany, BelongsTo};
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BasicListeningSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'require_for_certificate',
        'target',         // 'final' | 'session'
        'session_id',     // opsional jika target='session'
        'starts_at',
        'ends_at',
        'is_active',
        'category',
    ];

    protected $casts = [
        'require_for_certificate' => 'boolean',
        'is_active'               => 'boolean',
        'starts_at'               => 'datetime',
        'ends_at'                 => 'datetime',
        'category'                => 'string',
    ];

    /** Pertanyaan-pertanyaan pada survey ini */
    public function questions(): HasMany
    {
        return $this->hasMany(BasicListeningSurveyQuestion::class, 'survey_id')
                    ->orderBy('order');
    }

    /** Jawaban/respons per user */
    public function responses(): HasMany
    {
        return $this->hasMany(BasicListeningSurveyResponse::class, 'survey_id');
    }

    /** Jika kamu punya model BasicListeningSession, aktifkan relasi ini */
    public function session(): BelongsTo
    {
        return $this->belongsTo(BasicListeningSession::class, 'session_id');
    }

    public function scopeRequiredFinal($q) {
        return $q->where('require_for_certificate', true)
                ->where('target', 'final')
                ->where('is_active', true);
    }

    /** Cek apakah survey sedang terbuka untuk diisi */
    public function isOpen(): bool
    {
        $now = now();
        if (! $this->is_active) return false;
        if ($this->starts_at && $now->lt($this->starts_at)) return false;
        if ($this->ends_at && $now->gt($this->ends_at)) return false;
        return true;
    }
}
