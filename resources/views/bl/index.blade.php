{{-- resources/views/bl/index.blade.php --}}
@extends('layouts.front')
@section('title','Basic Listening')

@section('content')
@php
  $user = auth()->user();
  $groupNumber = $user?->nomor_grup_bl;
  $prodyName = $user?->prody?->name;
@endphp

{{-- HERO --}}
<div class="bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 text-white">
  <div class="max-w-7xl mx-auto px-4 py-20">
    <div class="max-w-3xl">
      <h1 class="text-4xl md:text-5xl font-bold mb-4">Basic Listening</h1>
      <p class="text-blue-100 text-lg">📚 Pilih pertemuan untuk memulai latihan</p>
    </div>
  </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-10">

  {{-- Flash Messages --}}
  @if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
      {{ session('success') }}
    </div>
  @endif
  @if (session('warning'))
    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
      {{ session('warning') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Profile / Greeting --}}
  @auth
    <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <p class="text-sm text-slate-500">Halo,</p>
          <h2 class="text-2xl font-semibold text-gray-900">{{ $user->name }}</h2>
          <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2 text-sm">
            <div class="rounded-md bg-slate-50 px-3 py-2">
              <span class="text-slate-500">NPM (Nomor Pokok Mahasiswa)</span>
              <div class="font-medium text-gray-900">{{ $user->srn ?? 'Silakan Lengkapi di Biodata' }}</div>
            </div>
            <div class="rounded-md bg-slate-50 px-3 py-2">
              <span class="text-slate-500">Program Studi</span>
              <div class="font-medium text-gray-900">{{ $prodyName ?? 'Silakan Lengkapi di Biodata' }}</div>
            </div>
            <div class="rounded-md bg-slate-50 px-3 py-2">
              <span class="text-slate-500">Tahun Angkatan</span>
              <div class="font-medium text-gray-900">{{ $user->year ?? 'Silakan Lengkapi di Biodata' }}</div>
            </div>
          </div>
        </div>

        {{-- Tombol Riwayat (muncul hanya jika nomor grup sudah diisi) --}}
        @if(!is_null($groupNumber))
          <a href="{{ route('bl.history') }}"
             class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Riwayat Skor</span>
          </a>
        @endif
      </div>
    </div>

    {{-- Nomor Grup BL: form isi/ubah --}}
    @if (is_null($groupNumber))
      <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-lg font-semibold text-gray-900">Isi Nomor Grup Basic Listening</h3>
        <p class="text-sm text-slate-600 mt-1">
          Nomor grup Anda diperoleh dari asisten pengajar di kelas. Harap isi sebelum mengakses <em>Riwayat</em>.
        </p>
        <form action="{{ route('bl.groupNumber.update') }}" method="POST" class="mt-4 flex flex-wrap items-end gap-3">
          @csrf
          <div>
            <label class="block text-sm font-medium text-gray-700">Nomor Grup</label>
            <input type="number" name="nomor_grup_bl" min="1" max="3" required
                   class="mt-1 block w-40 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-gray-900"
                   placeholder="1 - " />
            @error('nomor_grup_bl')
              <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
            @enderror
          </div>
          <button type="submit"
                  class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
            Simpan
          </button>
        </form>
        <p class="text-xs text-slate-500 mt-2">
          Catatan: Setelah diisi, tombol <em>Riwayat Skor</em> akan tampil di atas.
        </p>
      </div>
    @else
      <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h3 class="text-lg font-semibold text-emerald-900">Nomor Grup Anda</h3>
            <p class="text-sm text-emerald-800 mt-1">
              Anda terdaftar pada <strong>Grup {{ $groupNumber }}</strong>.
            </p>
          </div>
          <form action="{{ route('bl.groupNumber.update') }}" method="POST" class="flex items-end gap-2">
            @csrf
            <div>
              <label class="block text-xs font-medium text-emerald-900">Ubah Nomor Grup</label>
              <input type="number" name="nomor_grup_bl" min="1" max="50" required
                     value="{{ $groupNumber }}"
                     class="mt-1 block w-24 rounded-md border-emerald-300 bg-white/70 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-gray-900" />
            </div>
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-white hover:bg-emerald-700">
              Update
            </button>
          </form>
        </div>
      </div>
    @endif
  @endauth

  @guest
    <div class="mb-8 rounded-2xl border border-amber-200 bg-amber-50 p-5">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <h3 class="text-lg font-semibold text-amber-900">Silakan Login</h3>
          <p class="text-sm text-amber-800 mt-1">
            Masuk untuk melihat Nomor Grup, mengerjakan kuis, dan melihat riwayat skor.
          </p>
        </div>
        <a href="{{ route('login') }}"
           class="inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-white hover:bg-amber-700">
          Login
        </a>
      </div>
    </div>
  @endguest

  {{-- Grid Sessions --}}
  <div class="flex items-center justify-between mb-8">
    {{-- Jika butuh tombol lain bisa ditambahkan di sini --}}
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($sessions as $index => $s)
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
    @empty
      <div class="col-span-full text-center py-12">
        <div class="text-6xl mb-4">📋</div>
        <p class="text-gray-600 text-lg">Belum ada pertemuan tersedia</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
