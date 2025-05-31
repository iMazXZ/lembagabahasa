<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranEpt extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pendaftaranGrupTes()
    {
        return $this->hasMany(\App\Models\PendaftaranGrupTes::class, 'pendaftaran_ept_id');
    }

    public function nilaiTes()
    {
        return $this->hasOne(DataNilaiTes::class);
    }

    public function getTotalScoreAttribute()
    {
        return $this->pendaftaranGrupTes->first()?->dataNilaiTes?->total_score ?? '-';
    }

    public function getRankAttribute()
    {
        return $this->pendaftaranGrupTes->first()?->dataNilaiTes?->rank ?? '-';
    }

    public function getListeningComprehensionAttribute()
    {
        return $this->pendaftaranGrupTes->first()?->dataNilaiTes?->listening_comprehension ?? '-';
    }

    public function getStructureWrittenExprAttribute()
    {
        return $this->pendaftaranGrupTes->first()?->dataNilaiTes?->structure_written_expr ?? '-';
    }

    public function getReadingComprehensionAttribute()
    {
        return $this->pendaftaranGrupTes->first()?->dataNilaiTes?->reading_comprehension ?? '-';
    }
    
}
