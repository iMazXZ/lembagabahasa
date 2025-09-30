@props(['post'])

<article class="border rounded-xl overflow-hidden bg-white shadow-sm hover:shadow transition">
  <a href="{{ route('front.post.show', $post->slug) }}">
    <img
      src="{{ $post->cover_url }}"   {{-- << gunakan accessor --}}
      alt="{{ $post->title }}"
      class="w-full h-44 object-cover">
  </a>
  <div class="p-4">
    <a href="{{ route('front.post.show', $post->slug) }}"
       class="text-lg font-semibold hover:underline">
      {{ $post->title }}
    </a>
    <div class="text-sm text-gray-500 mt-1">
      {{ optional($post->published_at)->translatedFormat('d M Y') }}
    </div>
    @if($post->excerpt)
      <p class="mt-2 text-gray-700 line-clamp-3">{{ $post->excerpt }}</p>
    @endif
  </div>
</article>
