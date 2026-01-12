<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EptRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'bukti_pembayaran',
        'status',
        'rejection_reason',
        'grup_1_id',
        'grup_2_id',
        'grup_3_id',
        'cbt_token',
        'token_released_at',
        'selfie_path',
    ];

    protected $casts = [
        'token_released_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grup1(): BelongsTo
    {
        return $this->belongsTo(EptGroup::class, 'grup_1_id');
    }

    public function grup2(): BelongsTo
    {
        return $this->belongsTo(EptGroup::class, 'grup_2_id');
    }

    public function grup3(): BelongsTo
    {
        return $this->belongsTo(EptGroup::class, 'grup_3_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(EptSession::class, 'session_id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(EptAttempt::class, 'registration_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'Menunggu Verifikasi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'  => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default    => 'gray',
        };
    }

    /**
     * Cek apakah ada jadwal (minimal 1 grup punya jadwal)
     */
    public function hasSchedule(): bool
    {
        return $this->grup1?->jadwal || $this->grup2?->jadwal || $this->grup3?->jadwal;
    }

    /**
     * Cek apakah semua jadwal sudah lengkap
     */
    public function hasAllSchedules(): bool
    {
        return $this->grup1?->jadwal && $this->grup2?->jadwal && $this->grup3?->jadwal;
    }

    /**
     * Cek apakah token CBT sudah dirilis
     */
    public function hasToken(): bool
    {
        return !empty($this->cbt_token) && $this->token_released_at !== null;
    }

    /**
     * Generate token CBT
     */
    public function generateToken(): string
    {
        $this->cbt_token = strtoupper(bin2hex(random_bytes(4))); // 8 karakter
        $this->token_released_at = now();
        $this->save();
        return $this->cbt_token;
    }
}

