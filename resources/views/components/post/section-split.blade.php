@props(['title' => 'Judul', 'items' => collect(), 'moreRoute' => '#', 'emptyText' => 'Belum ada data.'])

@php
  use Illuminate\Support\Str;

  $sectionId = 'sec-' . Str::slug($title ?: 'section');
  $hasItems  = $items && $items->count() > 0;

  $featured  = $hasItems ? $items->first() : null;
  // ambil maksimal 5 selain featured
  $others    = $hasItems ? $items->skip(1)->take(5) : collect();
@endphp

<section class="py-12 lg:py-16 bg-gradient-to-b from-gray-50 to-white" aria-labelledby="{{ $sectionId }}-title">
  <div class="max-w-7xl mx-auto px-4 lg:px-8">

    {{-- Section Header --}}
    <div class="text-center mb-10 lg:mb-12">
      <div class="inline-flex flex-col items-center">
        <span class="text-sm font-semibold text-blue-600 uppercase tracking-wider mb-2">Informasi Terbaru</span>
        <h2 id="{{ $sectionId }}-title" class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3">
          {{ $title }}
        </h2>
        <div class="h-1 w-24 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full"></div>
      </div>
    </div>

    @if($hasItems)
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">

        {{-- Featured --}}
        <div class="lg:col-span-7">
          <article class="group bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-shadow duration-300 border border-gray-100">
            {{-- Featured Image / Placeholder --}}
            <a href="{{ route('front.post.show', $featured->slug) }}"
               class="block relative overflow-hidden aspect-video"
               aria-label="Baca: {{ $featured->title }}">
              @php $cover = $featured->cover_url ?? ''; @endphp

              @if($cover)
                <img
                  src="{{ $cover }}"
                  alt="{{ $featured->title }}"
                  class="w-full h-full object-cover sm:group-hover:scale-105 transition-transform duration-300"
                  loading="lazy"
                  decoding="async"
                  fetchpriority="low"
                  sizes="(max-width: 1024px) 100vw, 58vw">
              @else
                <div class="w-full h-full bg-gradient-to-br from-gray-200 to-gray-100" aria-hidden="true"></div>
              @endif

              {{-- Gradient Overlay on hover (desktop only) --}}
              <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 sm:group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>

            {{-- Content --}}
            <div class="p-6 lg:p-8">
              {{-- Date --}}
              @if($featured->published_at)
                <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <time datetime="{{ $featured->published_at->toDateString() }}" class="font-medium">
                    {{ $featured->published_at->translatedFormat('d M Y') }}
                  </time>
                </div>
              @endif

              {{-- Title --}}
              <a href="{{ route('front.post.show', $featured->slug) }}"
                 class="block text-2xl lg:text-3xl font-bold text-gray-900 hover:text-blue-600 transition-colors mb-4 line-clamp-2">
                {{ $featured->title }}
              </a>

              {{-- Excerpt --}}
              @if(!empty($featured->excerpt))
                <p class="text-gray-600 leading-relaxed mb-6 line-clamp-3">
                  {{ $featured->excerpt }}
                </p>
              @endif

              {{-- Read More --}}
              <a href="{{ route('front.post.show', $featured->slug) }}"
                 class="inline-flex items-center gap-2 text-blue-600 font-semibold sm:hover:gap-3 transition-all duration-200">
                <span>Baca Selengkapnya</span>
                <svg class="w-5 h-5 sm:group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
              </a>
            </div>
          </article>
        </div>

        {{-- Others --}}
        <div class="lg:col-span-5">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 lg:p-8 lg:sticky lg:top-24">
            <h3 class="text-xl font-bold text-gray-900 mb-6 pb-3 border-b-2 border-blue-600 flex items-center gap-2">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
              </svg>
              <span>Berita Lainnya</span>
            </h3>

            <div class="space-y-5">
              @foreach($others as $index => $p)
                <article class="group pb-5 border-b border-gray-100 last:border-b-0 last:pb-0">
                  <div class="flex gap-4">
                    {{-- Number Badge --}}
                    <div class="flex-shrink-0" aria-hidden="true">
                      <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shadow-md">
                        {{ $index + 1 }}
                      </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                      {{-- Date --}}
                      @if($p->published_at)
                        <div class="flex items-center gap-1 text-xs text-gray-500 mb-2">
                          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                          </svg>
                          <time datetime="{{ $p->published_at->toDateString() }}" class="font-medium">
                            {{ $p->published_at->translatedFormat('d M Y') }}
                          </time>
                        </div>
                      @endif

                      {{-- Title --}}
                      <a href="{{ route('front.post.show', $p->slug) }}"
                         class="block font-semibold text-gray-900 sm:group-hover:text-blue-600 transition-colors line-clamp-2 leading-snug">
                        {{ $p->title }}
                      </a>
                    </div>
                  </div>
                </article>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      {{-- View More --}}
      <div class="flex justify-center mt-10 lg:mt-12">
        <a href="{{ $moreRoute }}"
           class="group inline-flex items-center gap-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-7 py-3.5 rounded-full font-semibold shadow-lg hover:shadow-xl sm:hover:scale-105 transition">
          <span>Lihat Semua Berita</span>
          <svg class="w-5 h-5 sm:group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
        </a>
      </div>

    @else
      {{-- Empty State --}}
      <div class="text-center py-16">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-6" aria-hidden="true">
          <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $emptyText }}</h3>
        <p class="text-gray-600">Berita akan segera ditampilkan di sini.</p>
      </div>
    @endif
  </div>
</section>
