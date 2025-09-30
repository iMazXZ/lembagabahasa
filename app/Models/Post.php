<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'type',
        'excerpt',
        'body',
        'cover_path',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Konstanta label untuk dropdown / tampilan
    public const TYPES = [
        'news'     => 'Berita',
        'schedule' => 'Jadwal Ujian',
        'scores'   => 'Nilai Ujian',
    ];

    /**
     * Scope postingan yang sudah dipublikasikan.
     */
    public function scopePublished(Builder $q): Builder
    {
        return $q->where('is_published', true)
                 ->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
    }

    /**
     * Scope untuk filter berdasarkan jenis (news/schedule/scores).
     */
    public function scopeType(Builder $q, string $type): Builder
    {
        return $q->where('type', $type);
    }

    /**
     * Relasi ke author (user).
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Auto-generate slug saat menyimpan.
     * - Jika slug kosong, diisi dari title.
     * - Jika slug tabrakan, tambahkan suffix -2, -3, dst.
     * - Saat edit judul, slug tidak otomatis berubah (SEO friendly).
     */
    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            // Jika slug kosong, isi dari title
            if (blank($post->slug) && filled($post->title)) {
                $post->slug = Str::slug($post->title);
            }

            // Pastikan slug unik
            if (filled($post->slug)) {
                $base = $post->slug;
                $i = 2;

                $exists = static::where('slug', $post->slug)
                    ->when($post->exists, fn ($q) => $q->where('id', '!=', $post->id))
                    ->exists();

                while ($exists) {
                    $post->slug = $base . '-' . $i++;
                    $exists = static::where('slug', $post->slug)
                        ->when($post->exists, fn ($q) => $q->where('id', '!=', $post->id))
                        ->exists();
                }
            }
        });
    }

     /** path relatif dari /public */
    public const DEFAULT_COVERS = [
        'news'     => 'images/covers/news.jpg',
        'schedule' => 'images/covers/schedule.jpg',
        'scores'   => 'images/covers/scores.jpg',
    ];

    public function getCoverUrlAttribute(): string
    {
        if ($this->cover_path) {
            return asset('storage/'.$this->cover_path);
        }

        $path = self::DEFAULT_COVERS[$this->type] ?? 'images/covers/default.jpg';
        return asset($path);
    }

    public function getExcerptAttribute($value)
    {
        if (!empty($value)) return $value;

        // fallback dari body bersih HTML
        return Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($this->body))), 160);
    }
}
