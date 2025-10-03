<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EptSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nilai_tes_1', 'tanggal_tes_1', 'foto_path_1',
        'nilai_tes_2', 'tanggal_tes_2', 'foto_path_2',
        'nilai_tes_3', 'tanggal_tes_3', 'foto_path_3',
        'status',
        'catatan_admin',
        'verification_code', 'verification_url', 'surat_nomor',
        'approved_at', 'approved_by', 'rejected_at', 'rejected_by',
    ];

    protected $casts = [
        'tanggal_tes_1' => 'date',
        'tanggal_tes_2' => 'date',
        'tanggal_tes_3' => 'date',
        'approved_at'   => 'datetime',
        'rejected_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
