@props(['post'])

<article class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100">
  <!-- Image Container dengan Overlay Effect -->
  <a href="{{ route('front.post.show', $post->slug) }}" class="block relative h-56 overflow-hidden bg-gray-200">
    <img
      src="{{ $post->cover_url }}"
      alt="{{ $post->title }}"
      class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
    
    <!-- Gradient Overlay saat Hover -->
    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
  </a>

  <!-- Content -->
  <div class="p-6">
    <!-- Date dengan Icon -->
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
      </svg>
      <span>{{ optional($post->published_at)->translatedFormat('d M Y') }}</span>
    </div>

    <!-- Title dengan Hover Effect -->
    <a href="{{ route('front.post.show', $post->slug) }}"
       class="block text-xl font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors duration-300">
      {{ $post->title }}
    </a>

    <!-- Excerpt -->
    @if($post->excerpt)
      <p class="text-gray-600 text-sm mb-4 line-clamp-3">
        {{ $post->excerpt }}
      </p>
    @endif

    <!-- Read More Link dengan Arrow Animation -->
    <a href="{{ route('front.post.show', $post->slug) }}"
       class="inline-flex items-center gap-2 text-blue-600 font-semibold text-sm hover:gap-3 transition-all duration-300">
      <span>Baca Selengkapnya</span>
      <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
      </svg>
    </a>
  </div>
</article>