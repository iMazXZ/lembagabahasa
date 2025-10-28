{{-- resources/views/bl/index.blade.php --}}
@extends('layouts.front')
@section('title','Basic Listening')

@section('content')
@php
  $user = auth()->user();
  $groupNumber = $user?->nomor_grup_bl;
  $prodyName = $user?->prody?->name;
@endphp

{{-- HERO with integrated profile --}}
<div class="bg-gradient-to-br from-blue-600 to-indigo-800 text-white">
  <div class="max-w-7xl mx-auto px-4 py-12 md:py-16">
    
    {{-- Header --}}
    <div class="mb-8">
      <div class="inline-block px-3 py-1 rounded-full bg-white/20 text-sm mb-3">
        📚 Platform Pembelajaran
      </div>
      <h1 class="text-4xl md:text-5xl font-bold mb-2">Basic Listening</h1>
      <p class="text-blue-100 text-lg">Kegiatan Wajib Bagi Mahasiswa Semester 1, Universitas Muhammdiyah Metro</p>
    </div>

    {{-- Profile Card Integrated --}}
    @auth
      <div class="bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          
          {{-- User Info --}}
          <div class="flex-1">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 rounded-full bg-white/20 border-2 border-white/30 flex items-center justify-center text-white font-bold text-xl">
                {{ substr($user->name, 0, 1) }}
              </div>
              <div>
                <p class="text-xs text-blue-200">Halo,</p>
                <h2 class="text-xl font-bold">{{ $user->name }}</h2>
              </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm">
              <div class="bg-white/10 rounded-lg px-3 py-2 border border-white/20">
                <span class="text-blue-200 text-xs block mb-0.5">NPM</span>
                <div class="font-semibold">{{ $user->srn ?? '-' }}</div>
              </div>
              <div class="bg-white/10 rounded-lg px-3 py-2 border border-white/20">
                <span class="text-blue-200 text-xs block mb-0.5">Program Studi</span>
                <div class="font-semibold text-sm">{{ $prodyName ?? '-' }}</div>
              </div>
              <div class="bg-white/10 rounded-lg px-3 py-2 border border-white/20">
                <span class="text-blue-200 text-xs block mb-0.5">Tahun Angkatan</span>
                <div class="font-semibold">{{ $user->year ?? '-' }}</div>
              </div>
            </div>
          </div>

          {{-- Group & Action Section --}}
          <div class="lg:border-l lg:border-white/20 lg:pl-6 flex flex-col gap-3">
            
            {{-- Nomor Grup Display/Form --}}
            @if (is_null($groupNumber))
              <div class="bg-white/10 rounded-lg p-4 border border-white/20">
                <h3 class="font-semibold mb-2">Isi Nomor Grup</h3>
                <p class="text-xs text-blue-200 mb-3">Pilih grup Anda dari asisten pengajar</p>
                <form action="{{ route('bl.groupNumber.update') }}" method="POST" class="flex gap-2">
                  @csrf
                  <select name="nomor_grup_bl" required
                          class="flex-1 rounded-lg border-white/30 bg-white/20 text-white shadow-sm focus:border-white/50 focus:ring-white/30 backdrop-blur-sm">
                    <option value="" class="text-gray-900">Pilih Grup</option>
                    <option value="1" class="text-gray-900">Grup 1</option>
                    <option value="2" class="text-gray-900">Grup 2</option>
                    <option value="3" class="text-gray-900">Grup 3</option>
                    <option value="3" class="text-gray-900">Grup 4</option>
                  </select>
                  <button type="submit"
                          class="px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium whitespace-nowrap">
                    Simpan
                  </button>
                </form>
                @error('nomor_grup_bl')
                  <div class="text-xs text-red-200 mt-2">{{ $message }}</div>
                @enderror
              </div>
            @else
              <div class="bg-white/10 rounded-lg p-4 border border-white/20">
                <div class="flex items-center justify-between mb-3">
                  <div>
                    <h3 class="text-sm font-semibold">Nomor Grup</h3>
                    <p class="text-xl font-bold">Grup {{ $groupNumber }}</p>
                  </div>
                  <form action="{{ route('bl.groupNumber.update') }}" method="POST" class="flex gap-2">
                    @csrf
                    <select name="nomor_grup_bl" required
                            class="rounded-lg border-white/30 bg-white/20 text-white shadow-sm focus:border-white/50 focus:ring-white/30 backdrop-blur-sm text-sm">
                      <option value="1" {{ $groupNumber == 1 ? 'selected' : '' }} class="text-gray-900">Grup 1</option>
                      <option value="2" {{ $groupNumber == 2 ? 'selected' : '' }} class="text-gray-900">Grup 2</option>
                      <option value="3" {{ $groupNumber == 3 ? 'selected' : '' }} class="text-gray-900">Grup 3</option>
                      <option value="4" {{ $groupNumber == 4 ? 'selected' : '' }} class="text-gray-900">Grup 4</option>
                    </select>
                    <button type="submit"
                            class="px-3 py-1.5 bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors text-sm font-medium">
                      Ubah
                    </button>
                  </form>
                </div>
              </div>
              
              <a href="{{ route('bl.history') }}"
                 class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-semibold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Riwayat Skor
              </a>
            @endif
          </div>
        </div>
      </div>
    @endauth

    @guest
      <div class="bg-white/10 backdrop-blur-sm rounded-xl border border-white/20 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h3 class="text-xl font-semibold mb-1">Silakan Login</h3>
            <p class="text-blue-200">
              Login untuk mengerjakan kuis, dan melihat riwayat skor.
            </p>
          </div>
          <a href="{{ route('login') }}"
             class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-semibold whitespace-nowrap">
             Login
          </a>
        </div>
      </div>
    @endguest
  </div>
</div>

{{-- Content Section --}}
<div class="max-w-7xl mx-auto px-4 py-8">
  {{-- Flash Messages --}}
  @if (session('success'))
    <div class="mb-6 rounded-lg border-l-4 border-emerald-500 bg-emerald-50 px-4 py-3 text-emerald-800">
      {{ session('success') }}
    </div>
  @endif
  @if (session('warning'))
    <div class="mb-6 rounded-lg border-l-4 border-amber-500 bg-amber-50 px-4 py-3 text-amber-800">
      {{ session('warning') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Sessions Grid --}}
  @if(isset($sessions) && count($sessions) > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($sessions as $index => $s)
        @php
          $colors = [
            ['bg' => 'bg-yellow-100', 'border' => 'border-yellow-200', 'shadow' => 'shadow-yellow-200/50'],
            ['bg' => 'bg-pink-100', 'border' => 'border-pink-200', 'shadow' => 'shadow-pink-200/50'],
            ['bg' => 'bg-blue-100', 'border' => 'border-blue-200', 'shadow' => 'shadow-blue-200/50'],
            ['bg' => 'bg-green-100', 'border' => 'border-green-200', 'shadow' => 'shadow-green-200/50'],
            ['bg' => 'bg-purple-100', 'border' => 'border-purple-200', 'shadow' => 'shadow-purple-200/50'],
            ['bg' => 'bg-orange-100', 'border' => 'border-orange-200', 'shadow' => 'shadow-orange-200/50'],
          ];
          $color = $colors[$index % count($colors)];
          $rotation = ['rotate-1', '-rotate-1', 'rotate-2', '-rotate-2'];
          $rotate = $rotation[$index % count($rotation)];
        @endphp

        <a href="{{ route('bl.session.show', $s) }}"
           class="group relative block {{ $color['bg'] }} {{ $color['border'] }} border-2 rounded-lg p-6 {{ $rotate }} hover:rotate-0 transition-all duration-300 hover:scale-105 shadow-md hover:shadow-xl {{ $color['shadow'] }} hover:z-10">

          {{-- Pin/Thumbtack Effect --}}
          <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
            <div class="w-6 h-6 bg-red-500 rounded-full shadow-md relative">
              <div class="absolute inset-1 bg-red-400 rounded-full"></div>
            </div>
          </div>

          {{-- Status Badge --}}
          <div class="absolute top-4 right-4">
            @if($s->isOpen())
              <span class="px-3 py-1 rounded-full bg-green-500 text-white text-xs font-bold shadow-sm">
                OPEN
              </span>
            @else
              <span class="px-3 py-1 rounded-full bg-gray-400 text-white text-xs font-bold shadow-sm">
                CLOSED
              </span>
            @endif
          </div>

          {{-- Session Number --}}
          <div class="mb-3">
            <span class="inline-block px-3 py-1 bg-white/70 rounded-md text-xs font-bold text-gray-700 uppercase tracking-wider shadow-sm">
              📝 Pertemuan {{ $s->number <= 5 ? $s->number : 'UAS' }}
            </span>
          </div>

          {{-- Title --}}
          <h3 class="text-lg font-bold text-gray-800 mb-4 line-clamp-2 min-h-[3.5rem] group-hover:text-gray-900">
            {{ $s->title }}
          </h3>

          {{-- Duration --}}
          <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700">{{ $s->duration_minutes }} menit</span>
          </div>

          {{-- Schedule Info --}}
          @if($s->opens_at || $s->closes_at)
            <div class="mt-4 pt-3 border-t-2 border-dashed border-gray-300/50 space-y-1">
              @if($s->opens_at)
                <div class="flex items-start gap-2 text-xs text-gray-600">
                  <span class="font-semibold">🔓</span>
                  <span>{{ $s->opens_at->format('d M Y, H:i') }}</span>
                </div>
              @endif
              @if($s->closes_at)
                <div class="flex items-start gap-2 text-xs text-gray-600">
                  <span class="font-semibold">🔒</span>
                  <span>{{ $s->closes_at->format('d M Y, H:i') }}</span>
                </div>
              @endif
            </div>
          @endif

          {{-- Hover Effect Indicator --}}
          <div class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </div>
        </a>
      @endforeach
    </div>
  @else
    <div class="text-center py-16">
      <div class="text-6xl mb-4">📋</div>
      <p class="text-gray-600 text-lg font-medium">Belum ada pertemuan tersedia</p>
      <p class="text-gray-500 text-sm mt-2">Pertemuan akan ditampilkan setelah ditambahkan oleh admin</p>
    </div>
  @endif
</div>

@endsection