@props(['post', 'compact' => false])

@php
    $cat = $post->category ?? 'news';
    $chipClass = match ($cat) {
        'schedule' => 'bg-emerald-50 text-emerald-700',
        'scores'   => 'bg-violet-50 text-violet-700',
        default    => 'bg-slate-100 text-slate-700',
    };
    $chipLabel = match ($cat) {
        'schedule' => 'Jadwal',
        'scores'   => 'Nilai',
        default    => 'Berita',
    };
@endphp

<article class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100">
  @unless($compact)
    <a href="{{ route('front.post.show', $post->slug) }}" class="block relative h-56 overflow-hidden bg-gray-200">
      <img
        loading="lazy"
        src="{{ $post->cover_url }}"
        alt="{{ $post->title }}"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
      <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </a>
  @endunless

  <div class="p-6">
    {{-- CHIP + tanggal --}}
    <div class="flex items-center flex-wrap gap-2 text-xs text-gray-500 mb-3">
      <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-medium {{ $chipClass }}">
        {{ strtoupper($chipLabel) }}
      </span>
      <span class="text-slate-400">•</span>
      <span>{{ optional($post->published_at)->translatedFormat('d M Y') }}</span>
    </div>

    <a href="{{ route('front.post.show', $post->slug) }}"
       class="block text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors duration-300"
       aria-label="Baca {{ $post->title }}">
      {{ $post->title }}
    </a>

    @unless($compact)
      @if($post->excerpt)
        <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $post->excerpt }}</p>
      @endif
    @endunless

    <a href="{{ route('front.post.show', $post->slug) }}"
       class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm hover:gap-3 transition-all duration-300"
       aria-label="Baca {{ $post->title }}">
      <span>Baca Selengkapnya</span>
      <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </a>
  </div>
</article>
