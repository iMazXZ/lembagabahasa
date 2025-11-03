{{-- resources/views/front/posts/show.blade.php --}}
@extends('layouts.front')
@section('title', ($post->title ?? 'Detail') . ' - Lembaga Bahasa')

@php
    use Illuminate\Support\Facades\Route;

    // Mode lebar untuk konten khusus (jadwal/nilai)
    $isWide = in_array($post->type ?? null, ['schedule','scores'], true);

    // Tentukan fallback route untuk tombol kembali
    $candidates = [
        'front.posts.index',
        'front.post.index',
        'front.news.index',
        'front.news',
        'front.index',
        'home',
    ];
    $fallbackIndex = null;
    foreach ($candidates as $name) {
        if (Route::has($name)) { $fallbackIndex = route($name); break; }
    }
    $fallbackIndex ??= (url('/berita') ?: url('/'));

    $prevUrl  = url()->previous();
    $backHref = ($prevUrl && $prevUrl !== url()->current()) ? $prevUrl : $fallbackIndex;

    /**
     * Fallback sanitizer untuk $body
     */
    $sanitizeHtml = function (?string $html) {
        $html = (string) $html;
        $html = \Illuminate\Support\Str::of($html)
            ->replaceMatches('/<script\b[^>]*>.*?<\/script>/is', '')
            ->replaceMatches('/\son\w+\s*=\s*"[^"]*"/i', '')
            ->replaceMatches("/\son\w+\s*=\s*'[^']*'/i", '')
            ->replaceMatches('/\son\w+\s*=\s*[^\s>]+/i', '')
            ->toString();
        return $html;
    };
    $renderBody = function (?string $html) use ($sanitizeHtml) {
        if (function_exists('clean')) { try { return clean($html, 'post'); } catch (\Throwable $e) {} }
        return $sanitizeHtml($html);
    };

    // ===== Helper format tampilan views (1.2K / 3.4M) =====
    $formatViews = function ($n) {
        $n = (int) ($n ?? 0);
        if ($n >= 1000000) return number_format($n / 1000000, 1) . 'M';
        if ($n >= 1000)    return number_format($n / 1000, 1) . 'K';
        return number_format($n);
    };
@endphp

@push('styles')
<style>
  /* ================== TABEL UTAMA ================== */
  .tbl-wrap{
    overflow:auto;border:1px solid #e5e7eb;border-radius:12px;background:#fff;
    box-shadow:0 1px 3px rgba(0,0,0,.08);margin:.2rem 0;
    position:relative;
  }
  .prose .tbl-wrap table{width:100%;border-collapse:separate;border-spacing:0;font-size:.8rem}
  .prose .tbl-wrap thead th{
    position:sticky;top:0;z-index:10;background:#2563eb;
    background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);color:#fff;font-weight:600;
    padding:6px 8px;text-align:left;white-space:nowrap;border-bottom:2px solid #1d4ed8
  }
  .prose .tbl-wrap tbody td{
    padding:6px 8px;vertical-align:top;color:#374151;border-bottom:1px solid #f3f4f6;word-break:break-word
  }
  .prose .tbl-wrap tbody tr:nth-child(even) td{background:#f9fafb}
  .prose .tbl-wrap tbody tr:hover td{background:#eff6ff}
  .prose .tbl-wrap thead th:first-child,.prose .tbl-wrap tbody td:first-child{text-align:center;width:56px}
  .prose .tbl-wrap thead th:last-child,.prose .tbl-wrap tbody td:last-child{text-align:center;white-space:nowrap}

  /* Scroll hint indicator untuk mobile */
  @media (max-width:640px){
    .tbl-wrap.can-scroll::before{
      content:'← Geser untuk melihat semua kolom →';
      position:sticky;
      left:0;
      display:block;
      text-align:center;
      padding:8px;
      background:linear-gradient(135deg,#dbeafe 0%,#e0e7ff 100%);
      color:#2563eb;
      font-size:11px;
      font-weight:600;
      border-bottom:2px solid #93c5fd;
      z-index:8;
      letter-spacing:0.3px;
      animation:pulse 2s infinite;
    }

    @keyframes pulse{
      0%,100%{opacity:1}
      50%{opacity:0.7}
    }

    .tbl-wrap.scrolled::before{
      display:none;
    }
  }

  /* ====== MODE COMPACT MOBILE (≤640px) ====== */
  @media (max-width:640px){
    .tbl-wrap{border-radius:8px;box-shadow:none;margin:.1rem 0}
    .prose .tbl-wrap table{font-size:clamp(10px,3.1vw,12px);line-height:1.2;border-spacing:0}
    .prose .tbl-wrap thead th,.prose .tbl-wrap tbody td{padding:4px 6px}
    .prose .tbl-wrap thead th:first-child,.prose .tbl-wrap tbody td:first-child{width:36px;text-align:center;font-variant-numeric:tabular-nums}
    .prose .tbl-wrap thead th{top:0;z-index:12}
    .prose .tbl-wrap tbody tr:nth-child(even) td{background:#fafafa}
    .prose .tbl-wrap tbody tr:hover td{background:#f3f6ff}
  }

  /* Opsi super-compact untuk pembungkus bertag .compact-table */
  @media (max-width:640px){
    .compact-table .tbl-wrap table{font-size:10.5px;line-height:1.15}
    .compact-table .tbl-wrap th,.compact-table .tbl-wrap td{padding:2px 4px}
    .compact-table .tbl-wrap thead th:first-child,.compact-table .tbl-wrap tbody td:first-child{width:30px}
  }
</style>
@endpush

@section('content')
<!-- Breadcrumb & Back Button -->
<div class="bg-gray-50 border-b">
  <div class="max-w-7xl mx-auto px-4 lg:px-6 py-4">
    <a href="{{ $backHref }}"
       class="inline-flex items-center gap-2 text-blue-400 hover:text-blue-700 font-medium transition-colors group"
       aria-label="Kembali ke halaman sebelumnya">
      <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
      <span>Kembali</span>
    </a>
  </div>
</div>

<section class="max-w-7xl mx-auto px-4 lg:px-6 py-8 lg:py-12 {{ $isWide ? 'compact-table' : '' }}">
  
  {{-- ========== HEADER ARTIKEL ========== --}}
  <header class="mb-8 text-center">
    <h1 class="text-2xl lg:text-3xl font-bold leading-tight text-gray-900 mb-4">
      {{ $post->title }}
    </h1>
    
    <!-- Meta Information -->
    <div class="flex flex-wrap justify-center items-center gap-4 text-sm text-gray-600">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>{{ optional($post->published_at)->translatedFormat('d M Y, H:i') }}</span>
      </div>
      
      @if($post->author?->name)
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <span>{{ $post->author->name }}</span>
        </div>
      @endif

      {{-- ====== Tampilkan jumlah views ====== --}}
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true" focusable="false">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z"/>
          <circle cx="12" cy="12" r="3" stroke-width="2" stroke="currentColor" fill="none"/>
        </svg>
        <span>{{ $formatViews($post->views ?? 0) }} views</span>
      </div>
    </div>
  </header>

  @if($post->cover_url && !$isWide)
    <div class="relative mb-10 lg:mb-12 rounded-2xl overflow-hidden shadow-2xl">
      <img src="{{ $post->cover_url }}" 
           alt="{{ $post->title }}"
           class="w-full aspect-[16/9] object-cover"
           loading="lazy" decoding="async"
           width="1280" height="720">
      <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
    </div>
  @endif

  @if($isWide)
    {{-- ========== MODE SATU KOLOM (FULL WIDTH) ========== --}}
    <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 lg:p-6">
      <div id="post-body"
           class="prose prose-sm lg:prose-lg max-w-none
                  prose-headings:font-bold prose-headings:text-gray-900 prose-headings:mt-2 prose-headings:mb-2
                  prose-p:text-gray-700 prose-p:leading-relaxed
                  prose-a:text-blue-600 prose-a:font-medium hover:prose-a:text-blue-700 hover:prose-a:underline
                  prose-img:rounded-xl prose-img:shadow-lg prose-img:my-8
                  prose-figure:my-2 prose-figcaption:text-center prose-figcaption:text-sm prose-figcaption:text-gray-500 prose-figcaption:mt-2
                  prose-ul:my-2 prose-ol:my-2
                  prose-li:text-gray-700 prose-li:my-2
                  prose-strong:text-gray-900 prose-strong:font-semibold
                  prose-blockquote:border-l-4 prose-blockquote:border-blue-500 prose-blockquote:bg-blue-50 prose-blockquote:py-2 prose-blockquote:px-6 prose-blockquote:rounded-r-lg">
        {!! $renderBody($body) !!}
      </div>
    </article>

    @if($related->count())
      <div class="mt-16">
        <h2 class="text-2xl text-center lg:text-2xl font-bold text-gray-900 mb-6">Informasi Lainnya</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          @foreach($related as $r)
            <a href="{{ route('front.post.show', $r->slug) }}" 
               class="group block p-6 rounded-xl border border-gray-200 bg-white hover:shadow-lg hover:border-blue-300 transition-all duration-300">
              <div class="text-xs font-semibold uppercase text-blue-600 mb-2">
                {{ strtoupper(optional($r->published_at)->translatedFormat('d M Y')) }}
              </div>
              <div class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
                {{ $r->title }}
              </div>
            </a>
          @endforeach
        </div>
      </div>
    @endif

  @else
    {{-- ========== MODE DUA KOLOM (NEWS) ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
      <article class="lg:col-span-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-10">
          <div id="post-body"
               class="prose prose-lg lg:prose-xl max-w-none
                      prose-headings:font-bold prose-headings:text-gray-900 prose-headings:mt-8 prose-headings:mb-4
                      prose-p:text-gray-700 prose-p:leading-relaxed
                      prose-a:text-blue-600 prose-a:font-medium hover:prose-a:text-blue-700 hover:prose-a:underline
                      prose-img:rounded-xl prose-img:shadow-lg prose-img:my-8
                      prose-figure:my-8 prose-figcaption:text-center prose-figcaption:text-sm prose-figcaption:text-gray-500 prose-figcaption:mt-3
                      prose-ul:my-6 prose-ol:my-6
                      prose-li:text-gray-700 prose-li:my-2
                      prose-strong:text-gray-900 prose-strong:font-semibold
                      prose-blockquote:border-l-4 prose-blockquote:border-blue-500 prose-blockquote:bg-blue-50 prose-blockquote:py-2 prose-blockquote:px-6 prose-blockquote:rounded-r-lg">
            {!! $renderBody($body) !!}
          </div>
        </div>
      </article>

      <aside class="lg:col-span-4">
        @if($related->count())
          <div class="sticky top-24">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
              <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <span>Artikel Terkait</span>
              </h2>
              <div class="space-y-3">
                @foreach($related as $r)
                  <a href="{{ route('front.post.show', $r->slug) }}" 
                     class="block p-4 rounded-xl bg-white hover:bg-blue-50 border border-gray-200 hover:border-blue-300 transition-all duration-300 group">
                    <div class="text-xs font-semibold uppercase text-blue-600 mb-1">
                      {{ strtoupper(optional($r->published_at)->translatedFormat('d M Y')) }}
                    </div>
                    <div class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors line-clamp-2">
                      {{ $r->title }}
                    </div>
                  </a>
                @endforeach
              </div>
            </div>
          </div>
        @endif
      </aside>
    </div>
  @endif
</section>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('post-body');
    if (!root) return;

    root.querySelectorAll('table').forEach((tbl) => {
      tbl.removeAttribute('style');

      tbl.querySelectorAll('th,td,thead,tbody,tr').forEach(el => {
        el.removeAttribute('style');
        el.removeAttribute('height');
      });

      if (!tbl.parentElement || !tbl.parentElement.classList.contains('tbl-wrap')) {
        const wrap = document.createElement('div');
        wrap.className = 'tbl-wrap';
        
        // Aktifkan mode compact otomatis di layar kecil
        if (window.matchMedia('(max-width: 640px)').matches) {
          wrap.classList.add('compact-table');
        }
        
        tbl.parentNode.insertBefore(wrap, tbl);
        wrap.appendChild(tbl);

        // Deteksi jika tabel bisa di-scroll (untuk mobile)
        const checkScroll = () => {
          if (window.innerWidth <= 640) {
            if (wrap.scrollWidth > wrap.clientWidth) {
              wrap.classList.add('can-scroll');
            } else {
              wrap.classList.remove('can-scroll');
            }
          } else {
            wrap.classList.remove('can-scroll');
          }
        };

        checkScroll();
        window.addEventListener('resize', checkScroll);

        // Hapus hint setelah user scroll
        wrap.addEventListener('scroll', function() {
          if (this.scrollLeft > 10) {
            this.classList.add('scrolled');
            this.classList.remove('can-scroll');
          }
        });
      }
    });

    // Amankan tautan eksternal
    root.querySelectorAll('a[href]').forEach(a => {
      try {
        const href = a.getAttribute('href');
        const u = new URL(href, window.location.origin);
        if (u.origin !== window.location.origin) {
          a.target = '_blank';
          const rel = (a.rel || '').split(/\s+/);
          if (!rel.includes('noopener')) rel.push('noopener');
          if (!rel.includes('noreferrer')) rel.push('noreferrer');
          if (!rel.includes('nofollow')) rel.push('nofollow');
          a.rel = rel.join(' ').trim();
        }
      } catch (e) { /* abaikan URL tidak valid */ }
    });
  });
</script>
@endpush
