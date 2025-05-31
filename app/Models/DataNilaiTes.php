<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataNilaiTes extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function pendaftaranGrupTes()
    {
        return $this->belongsTo(PendaftaranGrupTes::class, 'pendaftaran_grup_tes_id');
    }
}
