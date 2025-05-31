<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranGrupTes extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function masterGrupTes()
    {
        return $this->belongsTo(MasterGrupTes::class, 'grup_tes_id');
    }

    public function pendaftaranEpt()
    {
        return $this->belongsTo(PendaftaranEpt::class, 'pendaftaran_ept_id');
    }

    public function dataNilaiTes()
    {
        return $this->hasOne(\App\Models\DataNilaiTes::class, 'pendaftaran_grup_tes_id');
    }
}
