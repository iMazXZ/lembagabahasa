{{-- resources/views/bl/survey_success.blade.php --}}
@extends('layouts.front')
@section('title','Kuesioner Selesai')

@push('styles')
<style>
  .hero-gradient{
    background: radial-gradient(1200px 600px at -10% -10%, rgba(255,255,255,.15) 0, transparent 60%),
                radial-gradient(1000px 600px at 110% -10%, rgba(255,255,255,.15) 0, transparent 60%),
                linear-gradient(135deg, #4f46e5 0%, #1e40af 100%);
  }
</style>
@endpush

@section('content')
  {{-- HERO --}}
  <div class="hero-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 py-10 md:py-14">
      <h1 class="text-3xl md:text-4xl font-bold tracking-tight">Terima Kasih 🎉</h1>
      <p class="mt-2 text-blue-100">Kamu telah menyelesaikan seluruh rangkaian kuesioner Basic Listening.</p>
    </div>
  </div>

  {{-- MAIN --}}
  <div class="max-w-3xl mx-auto px-4 py-8">

    {{-- Flash messages --}}
    @if(session('success'))
      <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3">
        {{ session('success') }}
      </div>
    @endif
    @if(session('info'))
      <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3">
        {{ session('info') }}
      </div>
    @endif
    @if(session('danger'))
      <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3">
        {{ session('danger') }}
      </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-5 py-6 md:px-8 md:py-8">

        {{-- CHECK ICON + TEXT --}}
        <div class="flex items-start gap-4">
          <div class="shrink-0 mt-1 inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <h2 class="text-xl font-semibold text-gray-900">Kuesioner Terkirim</h2>
            <p class="mt-1 text-sm text-gray-600">
              Jawabanmu sudah tersimpan. Terima kasih atas partisipasimu untuk membantu peningkatan kualitas pembelajaran.
            </p>

            {{-- RINGKASAN (opsional / statis) --}}
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Tahap 1</div>
                <div class="text-sm font-medium text-gray-900">Kuesioner Tutor</div>
              </div>
              <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Tahap 2</div>
                <div class="text-sm font-medium text-gray-900">Kuesioner Supervisor</div>
              </div>
              <div class="rounded-xl border border-gray-200 p-4">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Tahap 3</div>
                <div class="text-sm font-medium text-gray-900">Kuesioner Lembaga</div>
              </div>
            </div>
          </div>
        </div>

        {{-- ACTIONS --}}
        @php
          $historyUrl   = \Illuminate\Support\Facades\Route::has('bl.history') ? route('bl.history') : route('bl.index');
          $downloadUrl  = \Illuminate\Support\Facades\Route::has('bl.certificate.download') ? route('bl.certificate.download') : null;
          $previewUrl   = $downloadUrl ? route('bl.certificate.download', ['inline' => 1]) : null;
        @endphp

        <div class="mt-8 flex flex-wrap items-center gap-3">
          @if($previewUrl)
            <a href="{{ $previewUrl }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Preview Sertifikat
            </a>
          @endif

          @if($downloadUrl)
            <a href="{{ $downloadUrl }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
              Unduh Sertifikat (PDF)
            </a>
          @endif

          <a href="{{ $historyUrl }}" class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
            Kembali ke Riwayat
          </a>
        </div>

        <p class="mt-4 text-[12px] text-gray-500">
          Jika tombol unduh tidak aktif, pastikan nilai Attendance & Final Test sudah tersedia pada sistem.
        </p>
      </div>
    </div>
  </div>
@endsection
