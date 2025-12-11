{{-- resources/views/toefl/index.blade.php --}}
@extends('layouts.front')
@section('title', 'TOEFL Online')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-gray-50 to-zinc-100 py-12 px-4">
  <div class="max-w-4xl mx-auto">
    
    {{-- Header --}}
    <div class="text-center mb-10">
      <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-gray-900 mb-2">TOEFL Online Test</h1>
      <p class="text-gray-600">Pilih ujian yang tersedia untuk memulai</p>
    </div>

    {{-- Exam List --}}
    @if($exams->isEmpty())
      <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
        <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak Ada Ujian Tersedia</h3>
        <p class="text-gray-500">Saat ini tidak ada jadwal ujian TOEFL yang aktif.</p>
      </div>
    @else
      <div class="grid gap-6">
        @foreach($exams as $exam)
          <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $exam->name }}</h3>
                <p class="text-sm text-gray-500 mb-3">
                  <span class="inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ $exam->scheduled_at->translatedFormat('l, d F Y - H:i') }} WIB
                  </span>
                </p>
                <div class="flex gap-2 text-xs">
                  <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full">Listening {{ $exam->package->listening_duration }}m</span>
                  <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full">Structure {{ $exam->package->structure_duration }}m</span>
                  <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full">Reading {{ $exam->package->reading_duration }}m</span>
                </div>
              </div>
              <a href="{{ route('toefl.exam', $exam) }}" class="flex-shrink-0 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition-colors">
                Masuk
              </a>
            </div>
          </div>
        @endforeach
      </div>
    @endif

  </div>
</div>
@endsection
