<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterGrupTes extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function pendaftaranGrupTes()
    {
        return $this->hasMany(\App\Models\PendaftaranGrupTes::class, 'grup_tes_id');
    }
}
