@props(['post', 'compact' => false])

@php
    $type = $post->type ?? $post->category ?? 'news';

    $chipClass = match ($type) {
        'schedule' => 'bg-emerald-50 text-emerald-700',
        'scores'   => 'bg-violet-50 text-violet-700',
        default    => 'bg-slate-100 text-slate-700',
    };

    $chipLabel = match ($type) {
        'schedule' => 'Jadwal',
        'scores'   => 'Nilai',
        default    => 'Berita',
    };
@endphp

<article class="group bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100">
  @if($compact && $type === 'schedule')
    @php
      $eventDate = $post->event_date ?? null;
      $eventTime = $post->event_time ?? null;
      $eventDateTime = null;
      if ($eventDate) {
        $eventDateTime = \Carbon\Carbon::parse($eventDate);
        if ($eventTime) {
          $time = \Carbon\Carbon::parse($eventTime);
          $eventDateTime->setTime($time->hour, $time->minute);
        }
      }

      $isToday = $eventDateTime?->isToday() ?? false;
      $isUpcoming = $eventDateTime?->isFuture() ?? false;
      $daysLeft = $eventDateTime ? now()->startOfDay()->diffInDays($eventDateTime->startOfDay(), false) : null;
      $statusLabel = $isToday
        ? 'Hari Ini'
        : ($isUpcoming && $daysLeft !== null
            ? ($daysLeft === 1 ? 'Besok' : "{$daysLeft} Hari Lagi")
            : 'Selesai');

      $statusClass = $isToday
        ? 'bg-emerald-600 text-white'
        : ($isUpcoming ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600');

      $scorePost = null;
      if ($post->relationLoaded('relatedScores')) {
        $scorePost = $post->relatedScores->first();
      }
    @endphp

    <div class="p-6">
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mb-2">
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-semibold {{ $chipClass }}">
            {{ strtoupper($chipLabel) }}
          </span>
          @if($eventDateTime)
            <span class="text-slate-400">•</span>
            <time datetime="{{ $eventDateTime->toDateString() }}">
              {{ $eventDateTime->translatedFormat('d M Y') }}
            </time>
          @endif
          <span class="ml-auto inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
            {{ $statusLabel }}
          </span>
        </div>

        <a href="{{ route('front.post.show', $post->slug) }}"
           class="block text-lg lg:text-xl font-extrabold text-slate-900 mb-3 line-clamp-2 sm:group-hover:text-blue-700 transition-colors"
           aria-label="Baca {{ $post->title }}">
          {{ preg_replace('/\s*\([^)]+\)\s*$/', '', $post->title) }}
        </a>

        <div class="text-sm text-slate-600 space-y-1">
          @if($eventDateTime)
            <div class="flex items-center gap-2 flex-nowrap">
              <span class="inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
              <span class="min-w-0 flex-1 truncate" title="{{ $eventDateTime->translatedFormat('l, d F Y') }}">
                {{ $eventDateTime->translatedFormat('l, d F Y') }}
              </span>
              @if($eventTime)
                <span class="text-slate-300">|</span>
                <span class="font-semibold text-slate-700 whitespace-nowrap shrink-0">
                  {{ \Carbon\Carbon::parse($eventTime)->format('H:i') . ' WIB' }}
                </span>
              @endif
            </div>
          @endif
          @if(!empty($post->event_location))
            <div class="flex items-center gap-2 text-slate-500">
              <span class="inline-flex h-2 w-2 rounded-full bg-slate-300"></span>
              <span>{{ $post->event_location }}</span>
            </div>
          @endif
        </div>
      </div>

      <div class="mt-5 flex flex-wrap gap-2">
        <a href="{{ route('front.post.show', $post->slug) }}"
           class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100 transition-colors">
          Detail Jadwal
        </a>

        @if($scorePost)
          <a href="{{ route('front.post.show', $scorePost->slug) }}"
             class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors">
            Lihat Nilai
          </a>
        @endif
      </div>
    </div>
  @else
    @unless($compact)
      <a href="{{ route('front.post.show', $post->slug) }}"
         aria-label="Baca {{ $post->title }}"
         class="block relative h-56 overflow-hidden bg-gray-200">
        @if(!empty($post->cover_url))
          <img
            src="{{ $post->cover_url }}"
            alt="{{ $post->title }}"
            class="w-full h-full object-cover sm:group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
            decoding="async"
            fetchpriority="low"
            sizes="(max-width: 1024px) 100vw, 33vw">
        @else
          <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-100" aria-hidden="true"></div>
        @endif

        {{-- Overlay gradient only on hover --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 sm:group-hover:opacity-100 transition-opacity duration-300"></div>
      </a>
    @endunless

    <div class="p-6">
      {{-- CHIP + tanggal --}}
      <div class="flex items-center flex-wrap gap-2 text-xs text-gray-500 mb-3">
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-medium {{ $chipClass }}">
          {{ strtoupper($chipLabel) }}
        </span>

        @if($post->published_at)
          <span class="text-slate-400">•</span>
          <time datetime="{{ $post->published_at->toDateString() }}">
            {{ $post->published_at->translatedFormat('d M Y') }}
          </time>
        @endif
      </div>

      {{-- Title --}}
      <a href="{{ route('front.post.show', $post->slug) }}"
         class="block text-lg lg:text-xl font-bold text-gray-900 mb-3 line-clamp-2 sm:group-hover:text-blue-600 transition-colors duration-300"
         aria-label="Baca {{ $post->title }}">
        {{ $post->title }}
      </a>

      {{-- Excerpt (optional) --}}
      @unless($compact)
        @if(!empty($post->excerpt))
          <p class="text-gray-600 text-sm mb-4 line-clamp-3">
            {{ $post->excerpt }}
          </p>
        @endif
      @endunless

      {{-- Read more --}}
      <a href="{{ route('front.post.show', $post->slug) }}"
         class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm sm:hover:gap-3 transition-all duration-300"
         aria-label="Baca {{ $post->title }}">
        <span>Baca Selengkapnya</span>
        <svg class="w-4 h-4 sm:group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
      </a>
    </div>
  @endif
</article>
