@extends('layouts.front')
@section('title', $post->title.' - Lembaga Bahasa')

@php
    // Atur full width untuk konten tertentu (jadwal & nilai)
    $isWide = in_array($post->type, ['schedule','scores']);
@endphp

@section('content')
<style>
  .tbl-wrap{overflow:auto;border:1px solid #e5e7eb;border-radius:12px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.08);margin:1.5rem 0}
  .prose .tbl-wrap table{width:100%;border-collapse:separate;border-spacing:0;font-size:.95rem}
  .prose .tbl-wrap thead th{position:sticky;top:0;z-index:1;background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);color:#fff;font-weight:600;padding:14px 16px;text-align:left;white-space:nowrap;border-bottom:2px solid #1d4ed8}
  .prose .tbl-wrap tbody td{padding:12px 16px;vertical-align:top;color:#374151;border-bottom:1px solid #f3f4f6;word-break:break-word}
  .prose .tbl-wrap tbody tr:nth-child(even) td{background:#f9fafb}
  .prose .tbl-wrap tbody tr:hover td{background:#eff6ff}
  .prose .tbl-wrap thead th:first-child,.prose .tbl-wrap tbody td:first-child{text-align:center;width:56px}
  .prose .tbl-wrap thead th:last-child,.prose .tbl-wrap tbody td:last-child{text-align:center;white-space:nowrap}
  @media (max-width:640px){.prose .tbl-wrap thead th,.prose .tbl-wrap tbody td{padding:10px 12px;font-size:.9rem}}
</style>

<!-- Breadcrumb & Back Button -->
<div class="bg-gray-50 border-b">
  <div class="max-w-7xl mx-auto px-4 lg:px-6 py-4">
    <a href="{{ url()->previous() }}" 
       class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-medium transition-colors group">
      <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
      </svg>
      <span>Kembali</span>
    </a>
  </div>
</div>

<section class="max-w-7xl mx-auto px-4 lg:px-6 py-8 lg:py-12">
  
  {{-- ========== HEADER ARTIKEL ========== --}}
  <header class="mb-8">
    <h1 class="text-3xl lg:text-5xl font-bold leading-tight text-gray-900 mb-4">
      {{ $post->title }}
    </h1>
    
    <!-- Meta Information -->
    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>{{ optional($post->published_at)->translatedFormat('d M Y, H:i') }}</span>
      </div>
      
      @if($post->author?->name)
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          <span>{{ $post->author->name }}</span>
        </div>
      @endif
    </div>
  </header>

  @if($post->cover_url && !$isWide)
    <div class="relative mb-10 lg:mb-12 rounded-2xl overflow-hidden shadow-2xl">
      <img src="{{ $post->cover_url }}" 
           alt="{{ $post->title }}"
           class="w-full aspect-[16/9] object-cover"
           loading="lazy" 
           decoding="async">
      <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
    </div>
  @endif

  @if($isWide)
    {{-- ========== MODE SATU KOLOM (FULL WIDTH) ========== --}}
    <article class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-10">
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
        {!! $body !!}
      </div>
    </article>

    @if($related->count())
      <div class="mt-16">
        <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-6">Artikel Terkait</h2>
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
            {!! $body !!}
          </div>
        </div>
      </article>

      <aside class="lg:col-span-4">
        @if($related->count())
          <div class="sticky top-24">
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-100">
              <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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