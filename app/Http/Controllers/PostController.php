<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * List postingan per kategori (news|schedule|scores)
     * + dukung ?q=search & ?sort=new|old|az
     */
    public function index(Request $request, string $type)
    {
        abort_unless(in_array($type, ['news', 'schedule', 'scores'], true), 404);

        $title = Post::TYPES[$type] ?? ucfirst($type);

        $query = Post::published()
            ->type($type) // karena $type selalu salah satu dari tiga di atas
            ->select(['id', 'title', 'slug', 'excerpt', 'cover_path', 'published_at', 'author_id', 'type', 'views'])
            ->with(['author:id,name']);

        // Pencarian (opsional)
        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($qq) use ($search) {
                $qq->where('title', 'like', "%{$search}%")
                   ->orWhere('excerpt', 'like', "%{$search}%")
                   ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // Sort (opsional)
        $sort = $request->query('sort', 'new'); // new|old|az
        if ($sort === 'old') {
            $query->oldest('published_at');
        } elseif ($sort === 'az') {
            $query->orderBy('title');
        } else {
            $query->latest('published_at');
        }

        $posts = $query->paginate(12)->withQueryString();

        // untuk komponen card di index
        $category = $type;

        return view('front.posts.index', compact('posts', 'title', 'category'));
    }

    public function show(Post $post)
    {
        // Lengkapi relasi bila belum dimuat
        $post->loadMissing(['author:id,name']);

        $related = Post::published()
            ->type($post->type)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(4)
            ->get(['title', 'slug', 'cover_path', 'published_at']);

        $body = $this->formatBody($post->body ?? '');

        return view('front.posts.show', compact('post', 'related', 'body'));
    }

    /**
     * Merapikan HTML body agar bersih untuk ditampilkan.
     */
    private function formatBody(string $html): string
    {
        if ($html === '') return $html;

        // buang style/class/size inline
        $html = preg_replace('/\s(?:style|class|width|height)="[^"]*"/i', '', $html);

        // bersihkan baris “nama file + ukuran”
        $html = preg_replace(
            '/(?:^|>)[^<\S]*(?:IMG|DSC|PXL|PHOTO|WhatsApp Image|Screenshot)[^<\s]*\.(?:jpe?g|png|gif|webp)(?:[^<]*?(?:KB|MB))?[^<\S]*(?=<|$)/im',
            '',
            $html
        );

        // hapus anchor ke file gambar (jika tidak membungkus <img>)
        $html = preg_replace_callback('/<a\b([^>]*)>(.*?)<\/a>/is', function ($m) {
            $attrs = $m[1];
            $inner = $m[2];
            if (stripos($inner, '<img') !== false) return $m[0];

            $hrefHasExt = preg_match('/href="[^"]+\.(?:jpe?g|png|gif|webp)(?:\?[^"]*)?"/i', $attrs);
            $text = trim(strip_tags($inner));
            $textLooksLikeImage =
                preg_match('/\.(?:jpe?g|png|gif|webp)$/i', $text) ||
                preg_match('/^\d{1,4}(?:[.,]\d{1,2})?\s?(KB|MB)$/i', $text) ||
                preg_match('/^(?:WhatsApp Image|Screenshot).*/i', $text);

            return ($hrefHasExt || $textLooksLikeImage) ? '' : $m[0];
        }, $html);

        // angka ukuran file berdiri sendiri
        $html = preg_replace('/(?:^|>)[^<\S]*\d{1,4}(?:[.,]\d{1,2})?\s?(?:KB|MB)[^<\S]*(?=<|$)/im', '', $html);
        // nama file gambar berdiri sendiri
        $html = preg_replace('/(?:^|>)\s*[^<>\s]+\.(?:jpe?g|png|gif|webp)\s*(?=<|$)/im', '', $html);

        // rapikan img
        $html = preg_replace_callback('/<img\b([^>]*)>/i', function ($m) {
            $attrs = preg_replace('/\s(?:width|height)="[^"]*"/i', '', $m[1]);
            if (!preg_match('/\balt="/i', $attrs)) $attrs .= ' alt=""';
            $attrs .= ' loading="lazy" decoding="async" class="mx-auto my-6 rounded-2xl shadow-md"';
            return '<img' . $attrs . '>';
        }, $html);

        // buang paragraf kosong bertumpuk
        $html = preg_replace('/(<p>\s*<\/p>)+/i', '', $html);

        return $html;
    }
}
