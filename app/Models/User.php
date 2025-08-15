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
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements HasAvatar
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
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
        'nilaibasiclistening'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function prody()
    {
        return $this->belongsTo(\App\Models\Prody::class);
    }

    // Relationship untuk database notifications
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        // Cek apakah ada nilai di kolom 'image'
        if ($this->image) {
            // Jika ada, buat URL publiknya
            return Storage::url($this->image);
        }

        // Jika tidak ada, kembalikan null (Filament akan menampilkan inisial)
        return null;
    }

    public function eptSubmissions(): HasMany
    {
        return $this->hasMany(EptSubmission::class);
    }
}
