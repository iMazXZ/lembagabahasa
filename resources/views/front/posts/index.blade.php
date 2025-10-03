@extends('layouts.front')

@section('title', $title.' - Lembaga Bahasa')

@section('content')

  <!-- Hero -->
  <div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white">
    <div class="max-w-7xl mx-auto px-4 py-16">
      <div class="max-w-3xl">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ $title }}</h1>
        <p class="text-blue-100 text-lg">Temukan berita dan informasi terbaru dari Lembaga Bahasa</p>
      </div>
    </div>
  </div>

  <section class="max-w-7xl mx-auto px-4 py-10">
    {{-- FILTER BAR (q + sort). Tipe mengikuti route, jadi tidak dipilih ulang di sini --}}
    <form method="GET" class="mb-8">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        {{-- Cari --}}
        <div class="flex-1">
          <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
          <div class="relative">
            <span class="pointer-events-none absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
              </svg>
            </span>
            <input
              type="text"
              name="q"
              value="{{ request('q') }}"
              placeholder="Cari judul / isi…"
              class="w-full rounded-xl border-gray-300 pl-9 pr-3 py-2 focus:border-blue-500 focus:ring-blue-500"
            />
            @if(request('q'))
              {{-- tombol clear --}}
              <a href="{{ request()->fullUrlWithQuery(['q' => null, 'page' => null]) }}"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                title="Bersihkan">
                ✕
              </a>
            @endif
          </div>
        </div>

        {{-- Urutkan --}}
        <div class="sm:w-56">
          <label class="block text-xs font-medium text-gray-500 mb-1">Urutkan</label>
          @php $sort = request('sort', 'new'); @endphp
          <select name="sort"
                  class="w-full rounded-xl border-gray-300 py-2 focus:border-blue-500 focus:ring-blue-500">
            <option value="new" {{ $sort === 'new' ? 'selected' : '' }}>Terbaru</option>
            <option value="old" {{ $sort === 'old' ? 'selected' : '' }}>Terlama</option>
            <option value="az"  {{ $sort === 'az'  ? 'selected' : '' }}>Judul A–Z</option>
          </select>
        </div>

        {{-- Tombol --}}
        <div class="sm:w-auto">
          <label class="block text-xs font-medium text-transparent mb-1">.&nbsp;</label>
          <button
            class="w-full sm:w-auto inline-flex justify-center rounded-xl bg-blue-600 px-5 py-2 font-semibold text-white hover:bg-blue-700">
            Terapkan
          </button>
        </div>
      </div>

      {{-- Chip info filter aktif (opsional) --}}
      @if(request('q') || request('sort'))
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
          @if(request('q'))
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-50 text-blue-700">
              Query: “{{ request('q') }}”
            </span>
          @endif
          @if(request('sort'))
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-100 text-gray-700">
              Sort: {{ strtoupper(request('sort')) }}
            </span>
          @endif
        </div>
      @endif
    </form>


    @if ($posts->count())
      {{-- Stats --}}
      <div class="mb-6 text-sm text-gray-600">
        Menampilkan
        <span class="font-semibold text-gray-900">{{ $posts->firstItem() }}</span>
        –
        <span class="font-semibold text-gray-900">{{ $posts->lastItem() }}</span>
        dari
        <span class="font-semibold text-gray-900">{{ $posts->total() }}</span>
        hasil
      </div>

      {{-- Grid --}}
      @php
        // tampilkan versi compact (tanpa gambar & excerpt) untuk jadwal & nilai
        $compactList = in_array($category ?? '', ['schedule','scores'], true);
      @endphp

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach ($posts as $p)
          <x-post.card :post="$p" :compact="$compactList" />
        @endforeach
      </div>

      {{-- Pagination --}}
      <div class="mt-10">
        {{ $posts->onEachSide(1)->links() }}
      </div>
    @else
      {{-- Empty State --}}
      <div class="text-center py-20">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
          <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Belum Ada Data</h3>
        <p class="text-gray-600">Konten akan segera ditampilkan di sini.</p>
      </div>
    @endif
  </section>
@endsection
