<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index(string $type)
    {
        abort_unless(in_array($type, ['news','schedule','scores']), 404);

        $title = Post::TYPES[$type] ?? ucfirst($type);
        $posts = Post::published()->type($type)
            ->latest('published_at')
            ->select(['id','title','slug','excerpt','cover_path','published_at','author_id'])
            ->with(['author:id,name'])
            ->paginate(9);

        return view('front.posts.index', compact('posts','title'));
    }

    public function show(string $slug)
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with(['author:id,name'])
            ->firstOrFail();

        $related = Post::published()
            ->type($post->type)
            ->where('id','!=',$post->id)
            ->latest('published_at')
            ->limit(4)
            ->get(['title','slug','cover_path','published_at']);

        // 👉 Bersihkan & rapikan HTML body
        $body = $this->formatBody($post->body ?? '');

        return view('front.posts.show', compact('post','related','body'));
    }

    private function formatBody(string $html): string
    {
        if ($html === '') return $html;

        // 1) Buang inline style/class/width/height
        $html = preg_replace('/\s(?:style|class|width|height)="[^"]*"/i', '', $html);

        // 2) Hapus baris "nama file + ukuran" yang suka nyangkut
        $html = preg_replace(
            '/(?:^|>)[^<\S]*(?:IMG|DSC|PXL|PHOTO|WhatsApp Image|Screenshot)[^<\s]*\.(?:jpe?g|png|gif|webp)(?:[^<]*?(?:KB|MB))?[^<\S]*(?=<|$)/im',
            '',
            $html
        );

        // 3) Hapus anchor ke file gambar (jika TIDAK membungkus <img>)
        //    - match jika HREF mengandung ekstensi gambar
        //    - ATAU jika TEKS anchor terlihat seperti nama file gambar / ukuran file
        $html = preg_replace_callback('/<a\b([^>]*)>(.*?)<\/a>/is', function ($m) {
            $attrs = $m[1];
            $inner = $m[2];

            // biarkan jika membungkus <img>
            if (stripos($inner, '<img') !== false) return $m[0];

            $hrefHasExt = preg_match('/href="[^"]+\.(?:jpe?g|png|gif|webp)(?:\?[^"]*)?"/i', $attrs);

            $text = trim(strip_tags($inner));
            $textLooksLikeImage =
                preg_match('/\.(?:jpe?g|png|gif|webp)$/i', $text) ||                      // nama file .png/.jpg, dsb.
                preg_match('/^\d{1,4}(?:[.,]\d{1,2})?\s?(KB|MB)$/i', $text) ||            // "999 KB" / "1.2 MB"
                preg_match('/^(?:WhatsApp Image|Screenshot).*/i', $text);                 // variasi umum

            if ($hrefHasExt || $textLooksLikeImage) {
                return ''; // hapus anchor beserta teksnya
            }
            return $m[0];
        }, $html);

        // 4) Hapus teks ukuran file yang berdiri sendiri (tanpa anchor)
        $html = preg_replace('/(?:^|>)[^<\S]*\d{1,4}(?:[.,]\d{1,2})?\s?(?:KB|MB)[^<\S]*(?=<|$)/im', '', $html);

        // 5) Hapus paragraf/baris yang hanya berisi "nama file gambar"
        $html = preg_replace('/(?:^|>)\s*[^<>\s]+\.(?:jpe?g|png|gif|webp)\s*(?=<|$)/im', '', $html);

        // 6) Rapikan semua <img>
        $html = preg_replace_callback('/<img\b([^>]*)>/i', function ($m) {
            $attrs = preg_replace('/\s(?:width|height)="[^"]*"/i', '', $m[1]);
            if (!preg_match('/\balt="/i', $attrs)) $attrs .= ' alt=""';
            $attrs .= ' loading="lazy" decoding="async" class="mx-auto my-6 rounded-2xl shadow-md"';
            return '<img'.$attrs.'>';
        }, $html);

        // 7) Bersihkan paragraf kosong beruntun
        $html = preg_replace('/(<p>\s*<\/p>)+/i', '', $html);

        return $html;
    }

}
