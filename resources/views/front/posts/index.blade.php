@extends('layouts.front')
@section('title', $title.' - Lembaga Bahasa')
@section('content')

<!-- Hero Section dengan Gradient -->
<div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white">
  <div class="max-w-7xl mx-auto px-4 py-16">
    <div class="max-w-3xl">
      <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $title }}</h1>
      <p class="text-blue-100 text-lg">Temukan berita dan informasi terbaru dari Lembaga Bahasa</p>
    </div>
  </div>
</div>

<!-- Main Content -->
<section class="max-w-7xl mx-auto px-4 py-12">
  @if($posts->count())
    <!-- Stats Bar -->
    <div class="mb-8 flex items-center justify-between">
      <p class="text-gray-600">
        Menampilkan <span class="font-semibold text-gray-900">{{ $posts->count() }}</span> berita
      </p>
    </div>

    <!-- Grid Layout dengan Card Modern -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
      @foreach($posts as $p)
        <x-post.card :post="$p"/>
      @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-12">
      {{ $posts->links() }}
    </div>
  @else
    <!-- Empty State -->
    <div class="text-center py-20">
      <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
        </svg>
      </div>
      <h3 class="text-2xl font-bold text-gray-900 mb-2">Belum Ada Berita</h3>
      <p class="text-gray-600">Berita akan segera ditampilkan di sini.</p>
    </div>
  @endif
</section>

@endsection