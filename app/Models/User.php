<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Storage;
use Filament\Models\Contracts\HasAvatar;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements HasAvatar, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Kolom yang boleh diisi mass-assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'srn',
        'prody_id',
        'year',
        'image',
        'nilaibasiclistening',
        'nomor_grup_bl',
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting kolom.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'nomor_grup_bl' => 'integer',
        ];
    }

    /**
     * Relasi ke Prodi (FK: prody_id).
     */
    public function prody(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Prody::class, 'prody_id');
    }

    /**
     * Relasi ke notifikasi database (urut terbaru).
     */
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Avatar untuk Filament Admin.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->image) {
            return Storage::url($this->image);
        }

        return null; // Filament akan tampilkan inisial
    }

    /**
     * Contoh relasi ke pengajuan EPT (biarkan sesuai kebutuhanmu).
     */
    public function eptSubmissions(): HasMany
    {
        return $this->hasMany(\App\Models\EptSubmission::class);
    }

    /**
     * Relasi many-to-many: Tutor ↔ Prodi yang diampu.
     * Pivot: tutor_prody (user_id, prody_id).
     */
    public function tutorProdies(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Prody::class, 'tutor_prody')
            ->withTimestamps();
    }

    /**
     * Relasi ke attempts Basic Listening milik user (peserta).
     */
    public function basicListeningAttempts(): HasMany
    {
        return $this->hasMany(\App\Models\BasicListeningAttempt::class, 'user_id');
    }

    public function basicListeningGrade(): HasOne
    {
        return $this->hasOne(\App\Models\BasicListeningGrade::class);
    }

    public function basicListeningManualScores()
    {
        return $this->hasMany(\App\Models\BasicListeningManualScore::class);
    }

    public function getBlFinalNumericAttribute(): ?float
    {
        [$n, ] = \App\Support\BlSource::finalFor($this);
        return $n;
    }

    public function getBlFinalLetterAttribute(): ?string
    {
        [, $l] = \App\Support\BlSource::finalFor($this);
        return $l;
    }

    /**
     * Helper: ambil array ID prodi yang diampu tutor (memoized per-request).
     * Menggunakan pluck('id') dari tabel relasi (prodies).
     */
    public function assignedProdyIds(): array
    {
        static $cacheByUser = [];

        if (! isset($cacheByUser[$this->id])) {
            // Ambil dari pivot untuk menghindari ambiguitas kolom 'id'
            $ids = $this->tutorProdies()
                ->pluck('tutor_prody.prody_id')   // <- penting: kwalifikasi kolom
                ->unique()
                ->map(fn ($v) => (int) $v)
                ->values()
                ->all();

            $cacheByUser[$this->id] = $ids;
        }

        return $cacheByUser[$this->id];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasAnyRole([
                'Admin',
                'Staf',
                'Staf Administrasi',
                'Kepala Lembaga',
                'super_admin',
                'pendaftar',
                'tutor',
            ]);
        }

        return false;
    }

}
