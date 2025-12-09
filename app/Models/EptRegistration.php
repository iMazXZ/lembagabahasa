<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EptRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bukti_pembayaran',
        'status',
        'rejection_reason',
        'grup_1', 'jadwal_1',
        'grup_2', 'jadwal_2',
        'grup_3', 'jadwal_3',
    ];

    protected $casts = [
        'jadwal_1' => 'datetime',
        'jadwal_2' => 'datetime',
        'jadwal_3' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    public function hasSchedule(): bool
    {
        return $this->jadwal_1 && $this->jadwal_2 && $this->jadwal_3;
    }
}
