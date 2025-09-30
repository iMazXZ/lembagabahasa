{{-- resources/views/components/post/section-split.blade.php --}}
@props(['title'=>'Judul','items'=>collect(),'moreRoute'=>'#','emptyText'=>'Belum ada data.'])
@php
  $featured = $items->first();
  $others   = $items->skip(1)->take(5);
@endphp

<section class="py-8">
  <div class="max-w-7xl mx-auto px-4 lg:px-8">
    <div class="relative text-center mb-6">
      <div class="h-px bg-gray-200"></div>
      <div class="absolute inset-0 flex flex-col items-center">
        <h2 class="px-4 bg-white tracking-widest text-2xl lg:text-3xl font-extrabold text-um-blue">
          {{ strtoupper($title) }}
        </h2>
        <span class="mt-2 h-1 w-40 bg-um-gold"></span>
      </div>
    </div>

    @if($items->count())
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7">
          <article class="bg-white border rounded-xl overflow-hidden">
            <a href="{{ route('front.post.show', $featured->slug) }}">
              <img src="{{ $featured->cover_url }}" alt="{{ $featured->title }}"
                   class="w-full h-64 lg:h-80 object-cover">
            </a>
            <div class="p-4 lg:p-5">
              <div class="text-xs uppercase text-gray-500">
                {{ strtoupper(optional($featured->published_at)->translatedFormat('d M Y')) }}
              </div>
              <a href="{{ route('front.post.show', $featured->slug) }}"
                 class="block text-lg lg:text-xl font-semibold text-um-blue hover:underline mt-1">
                {{ $featured->title }}
              </a>
              @if($featured->excerpt)
                <p class="mt-3 text-gray-700 line-clamp-3">{{ $featured->excerpt }}</p>
              @endif
            </div>
          </article>
        </div>

        <div class="lg:col-span-5 lg:border-l lg:pl-6">
          @foreach($others as $p)
            <div class="py-3 border-b last:border-b-0">
              <div class="text-[11px] uppercase text-gray-500">
                {{ strtoupper(optional($p->published_at)->translatedFormat('d M Y')) }}
              </div>
              <a href="{{ route('front.post.show', $p->slug) }}"
                 class="font-semibold text-um-blue hover:underline">
                {{ $p->title }}
              </a>
            </div>
          @endforeach
        </div>
      </div>

      <div class="flex justify-center mt-8">
        <a href="{{ $moreRoute }}"
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-full">
          Lihat Lainnya <i class="fa-solid fa-paper-plane"></i>
        </a>
      </div>
    @else
      <p class="text-gray-500">{{ $emptyText }}</p>
    @endif
  </div>
</section>
