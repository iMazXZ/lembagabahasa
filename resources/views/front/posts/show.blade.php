@extends('layouts.front')
@section('title', $post->title.' - Lembaga Bahasa')

@php
    // Atur full width untuk konten tertentu (jadwal & nilai)
    $isWide = in_array($post->type, ['schedule','scores']);
@endphp

@section('content')
<style>
  .tbl-wrap{overflow:auto;border:1px solid #e5e7eb;border-radius:12px;background:#fff;box-shadow:0 1px 1px rgba(0,0,0,.02);margin:1rem 0 1.25rem}
  .prose .tbl-wrap table{width:100%;border-collapse:separate;border-spacing:0;font-size:.95rem}
  .prose .tbl-wrap thead th{position:sticky;top:0;z-index:1;background:#f9fafb;color:#111827;font-weight:600;padding:12px 16px;text-align:left;white-space:nowrap;border-bottom:1px solid #e5e7eb}
  .prose .tbl-wrap tbody td{padding:12px 16px;vertical-align:top;color:#374151;border-bottom:1px solid #f3f4f6;word-break:break-word}
  .prose .tbl-wrap tbody tr:nth-child(even) td{background:#fcfcfd}
  .prose .tbl-wrap thead th:first-child,.prose .tbl-wrap tbody td:first-child{text-align:center;width:56px}
  .prose .tbl-wrap thead th:last-child,.prose .tbl-wrap tbody td:last-child{text-align:center;white-space:nowrap}
  @media (max-width:640px){.prose .tbl-wrap thead th,.prose .tbl-wrap tbody td{padding:10px 12px;font-size:.95rem}}
</style>

<section class="max-w-7xl mx-auto px-4 lg:px-6 py-8">
  <div class="mb-4">
    <a href="{{ url()->previous() }}" class="text-blue-600 hover:underline">&larr; Kembali</a>
  </div>

  {{-- ========== HEADER ARTIKEL ========== --}}
  <h1 class="text-3xl lg:text-4xl font-bold leading-tight">{{ $post->title }}</h1>
  <p class="text-sm text-gray-500 mt-2">
    {{ optional($post->published_at)->translatedFormat('d M Y, H:i') }}
    @if($post->author?->name) · {{ $post->author->name }} @endif
  </p>

  @if($post->cover_url)
    <img src="{{ $post->cover_url }}" alt="{{ $post->title }}"
         class="w-full aspect-[16/9] object-cover my-6 lg:my-8 rounded-xl shadow-md"
         loading="lazy" decoding="async">
  @endif

  @if($isWide)
    {{-- ========== MODE SATU KOLOM (FULL WIDTH) ========== --}}
    <article>
      <div id="post-body"
           class="prose prose-lg lg:prose-xl max-w-none
                  prose-headings:font-semibold prose-headings:text-gray-900
                  prose-a:text-um-blue hover:prose-a:underline
                  prose-img:rounded-xl prose-img:shadow
                  prose-figure:my-6 prose-figcaption:text-sm prose-figcaption:text-gray-500">
        {!! $body !!}
      </div>
    </article>

    @if($related->count())
      <hr class="my-12">
      <h2 class="text-lg font-semibold mb-4">Artikel Terkait</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($related as $r)
          <a href="{{ route('front.post.show', $r->slug) }}" class="block p-4 rounded-xl border hover:bg-gray-50">
            <div class="text-[11px] uppercase text-gray-500">
              {{ strtoupper(optional($r->published_at)->translatedFormat('d M Y')) }}
            </div>
            <div class="font-medium text-um-blue">{{ $r->title }}</div>
          </a>
        @endforeach
      </div>
    @endif

  @else
    {{-- ========== MODE DUA KOLOM (NEWS) ========== --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
      <article class="lg:col-span-8">
        <div id="post-body"
             class="prose prose-lg lg:prose-xl max-w-none
                    prose-headings:font-semibold prose-headings:text-gray-900
                    prose-a:text-um-blue hover:prose-a:underline
                    prose-img:rounded-xl prose-img:shadow
                    prose-figure:my-6 prose-figcaption:text-sm prose-figcaption:text-gray-500">
          {!! $body !!}
        </div>
      </article>

      <aside class="lg:col-span-4">
        @if($related->count())
          <div class="sticky top-24">
            <h2 class="text-lg font-semibold mb-3">Artikel Terkait</h2>
            <div class="divide-y rounded-xl border bg-white">
              @foreach($related as $r)
                <a href="{{ route('front.post.show', $r->slug) }}" class="block p-4 hover:bg-gray-50">
                  <div class="text-[11px] uppercase text-gray-500">
                    {{ strtoupper(optional($r->published_at)->translatedFormat('d M Y')) }}
                  </div>
                  <div class="font-medium text-um-blue hover:underline">{{ $r->title }}</div>
                </a>
              @endforeach
            </div>
          </div>
        @endif
      </aside>
    </div>
  @endif
</section>

<script>
  // Rapikan semua tabel (buang inline style + bungkus)
  document.addEventListener('DOMContentLoaded', function () {
    const root = document.getElementById('post-body');
    if (!root) return;

    root.querySelectorAll('table').forEach((tbl) => {
      tbl.removeAttribute('style'); tbl.removeAttribute('width');
      tbl.querySelectorAll('th,td,tr,thead,tbody').forEach(el => {
        el.removeAttribute('style'); el.removeAttribute('width'); el.removeAttribute('height');
      });
      if (!(tbl.parentElement && tbl.parentElement.classList.contains('tbl-wrap'))) {
        const wrap = document.createElement('div');
        wrap.className = 'tbl-wrap';
        tbl.parentNode.insertBefore(wrap, tbl);
        wrap.appendChild(tbl);
      }
    });
  });
</script>
@endsection
