<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EptGroup extends Model
{
    protected $fillable = [
        'name',
        'quota',
        'jadwal',
        'lokasi',
    ];

    protected $casts = [
        'quota' => 'integer',
        'jadwal' => 'datetime',
    ];

    /**
     * Registrations yang masuk grup ini (sebagai grup_1)
     */
    public function registrationsAsGrup1(): HasMany
    {
        return $this->hasMany(EptRegistration::class, 'grup_1_id');
    }

    /**
     * Registrations yang masuk grup ini (sebagai grup_2)
     */
    public function registrationsAsGrup2(): HasMany
    {
        return $this->hasMany(EptRegistration::class, 'grup_2_id');
    }

    /**
     * Registrations yang masuk grup ini (sebagai grup_3)
     */
    public function registrationsAsGrup3(): HasMany
    {
        return $this->hasMany(EptRegistration::class, 'grup_3_id');
    }

    /**
     * Registrations yang masuk grup ini (sebagai grup_4)
     */
    public function registrationsAsGrup4(): HasMany
    {
        return $this->hasMany(EptRegistration::class, 'grup_4_id');
    }

    /**
     * Semua registration yang masuk grup ini
     */
    public function allRegistrations()
    {
        return EptRegistration::where('grup_1_id', $this->id)
            ->orWhere('grup_2_id', $this->id)
            ->orWhere('grup_3_id', $this->id)
            ->orWhere('grup_4_id', $this->id);
    }

    /**
     * Cek apakah jadwal sudah ditetapkan
     */
    public function hasSchedule(): bool
    {
        return $this->jadwal !== null;
    }
}
