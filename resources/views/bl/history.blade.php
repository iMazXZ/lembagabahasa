@extends('layouts.front')
@section('title', 'Riwayat Skor')

@section('content')
{{-- HERO Section (simple only) --}}
<div class="bg-gradient-to-br from-blue-600 to-indigo-800 text-white">
  <div class="max-w-7xl mx-auto px-4 py-10 md:py-12">
    <div class="text-center">
      <h1 class="text-3xl md:text-4xl font-bold mb-2">Riwayat Skor</h1>
      <p class="text-blue-100 text-sm md:text-base">
        Lihat perkembangan Basic Listening dan detail jawaban setiap pertemuan.
      </p>
    </div>
  </div>
</div>

{{-- Content Section --}}
<div class="bg-gray-50 min-h-screen">
  <div class="max-w-4xl mx-auto px-4 py-8">

    @php
      $totalAttempts    = $attempts->count();
      $completedAttempts = $attempts->whereNotNull('submitted_at')->count();
      $avgScore         = $attempts->whereNotNull('score')->avg('score');
    @endphp

    {{-- Tips Box --}}
    <div class="mb-4 bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm text-slate-700 flex gap-3">
      <div class="mt-0.5">
        <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
          </svg>
        </div>
      </div>
      <div>
        <p class="font-semibold text-slate-900 mb-1">Cara melihat detail</p>
        <p class="text-sm leading-snug">
          Ketuk salah satu <span class="font-semibold">pertemuan</span> di bawah
          (yang ada panah <span class="font-semibold">➜</span>) untuk melihat jawaban lengkap, skor,
          dan rincian hasil.
        </p>
      </div>
    </div>

    {{-- Attempts List --}}
    <div class="space-y-3">
      @forelse($attempts as $attempt)
        @php
          $isUAS    = (int)($attempt->session->number) > 5;
          $hasScore = !is_null($attempt->score);
        @endphp

        <a href="{{ route('bl.history.show', $attempt) }}"
          class="attempt-card block bg-white rounded-xl border border-slate-200 px-4 py-3 hover:border-blue-300 hover:shadow-md transition-all">
          <div class="flex items-center gap-3">
            {{-- Badge kolom kiri --}}
            <div class="flex flex-col items-start gap-1 min-w-[90px]">
              @if($isUAS)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-pink-100 text-pink-700 text-[11px] font-semibold">
                  UAS
                </span>
              @else
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[11px] font-semibold">
                  Meeting {{ $attempt->session->number }}
                </span>
              @endif
            </div>

            {{-- Info utama --}}
            <div class="flex-1 min-w-0">
              <h3 class="font-semibold text-sm md:text-base text-gray-900 truncate">
                {{ $attempt->session->title }}
              </h3>
            </div>

            {{-- Score + Arrow --}}
            <div class="flex items-center gap-2">
              @if($hasScore)
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200">
                  <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  <span class="font-bold text-emerald-700 text-sm">{{ (int) $attempt->score }}</span>
                </div>
              @else
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200">
                  <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                  <span class="font-semibold text-amber-700 text-xs">Belum submit</span>
                </div>
              @endif

              <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-50 text-slate-400 border border-slate-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </span>
            </div>
          </div>
        </a>
      @empty
        <div class="text-center bg-white rounded-lg p-12 border border-slate-200">
          <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 text-lg mb-2">Belum Ada Riwayat</h3>
          <p class="text-slate-600 mb-4">
            Mulai ikuti pertemuan untuk melihat riwayat skor Anda di sini.
          </p>
          <a href="{{ route('bl.index') }}"
             class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            Lihat Pertemuan
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </a>
        </div>
      @endforelse
    </div>

    {{-- Stats Cards dipindah ke bawah --}}
    @if($totalAttempts > 0)
      <div class="mt-8 border-t border-slate-200 pt-6">
        <h2 class="text-sm font-semibold text-slate-700 mb-3">
          Ringkasan Skor
        </h2>
        <div class="grid grid-cols-3 gap-3 md:gap-4">
          <div class="bg-white rounded-lg p-3 border border-slate-200 text-center">
            <div class="text-slate-500 text-[11px] mb-0.5">Total</div>
            <div class="text-xl font-bold mb-0.5">{{ $totalAttempts }}</div>
            <div class="text-slate-500 text-[11px]">Pertemuan</div>
          </div>

          <div class="bg-white rounded-lg p-3 border border-slate-200 text-center">
            <div class="text-slate-500 text-[11px] mb-0.5">Selesai</div>
            <div class="text-xl font-bold mb-0.5">{{ $completedAttempts }}</div>
            <div class="text-slate-500 text-[11px]">Tersubmit</div>
          </div>

          <div class="bg-white rounded-lg p-3 border border-slate-200 text-center">
            <div class="text-slate-500 text-[11px] mb-0.5">Rata-rata</div>
            <div class="text-xl font-bold mb-0.5">{{ $avgScore ? number_format($avgScore, 1) : '0' }}</div>
            <div class="text-slate-500 text-[11px]">Skor</div>
          </div>
        </div>
      </div>
    @endif

    {{-- Back Button --}}
    <div class="flex justify-center mt-8 mb-4">
      <a href="{{ route('bl.index') }}"
         class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-300 rounded-lg font-medium text-gray-700 hover:bg-slate-50 transition-colors shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Basic Listening
      </a>
    </div>

  </div>
</div>

@endsection
