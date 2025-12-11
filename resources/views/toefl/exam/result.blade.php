{{-- resources/views/toefl/exam/result.blade.php --}}
@extends('layouts.front')
@section('title', 'Hasil Ujian TOEFL')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 py-12 px-4">
  <div class="max-w-2xl mx-auto">
    
    {{-- Success Icon --}}
    <div class="text-center mb-8">
      <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
        <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Ujian Selesai!</h1>
      <p class="text-gray-600">{{ $attempt->exam->name }}</p>
    </div>

    {{-- Score Card --}}
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
      {{-- Total Score --}}
      <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-8 text-center">
        <div class="text-sm uppercase tracking-wide opacity-80 mb-2">Total Skor TOEFL</div>
        <div class="text-6xl font-bold mb-2">{{ $attempt->total_score ?? '-' }}</div>
        <div class="text-sm opacity-80">dari 677</div>
      </div>

      {{-- Section Scores --}}
      <div class="p-6">
        <h3 class="font-semibold text-gray-700 mb-4 text-center">Skor per Section</h3>
        
        <div class="space-y-4">
          {{-- Listening --}}
          <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.414a5 5 0 001.414 1.414m2.828-9.9a9 9 0 0112.728 0"/>
                </svg>
              </div>
              <div>
                <div class="font-semibold text-gray-900">Listening</div>
                <div class="text-xs text-gray-500">{{ $attempt->listening_correct ?? 0 }} benar</div>
              </div>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $attempt->listening_score ?? '-' }}</div>
          </div>

          {{-- Structure --}}
          <div class="flex items-center justify-between p-4 bg-purple-50 rounded-xl">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div>
                <div class="font-semibold text-gray-900">Structure</div>
                <div class="text-xs text-gray-500">{{ $attempt->structure_correct ?? 0 }} benar</div>
              </div>
            </div>
            <div class="text-2xl font-bold text-purple-600">{{ $attempt->structure_score ?? '-' }}</div>
          </div>

          {{-- Reading --}}
          <div class="flex items-center justify-between p-4 bg-orange-50 rounded-xl">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
              </div>
              <div>
                <div class="font-semibold text-gray-900">Reading</div>
                <div class="text-xs text-gray-500">{{ $attempt->reading_correct ?? 0 }} benar</div>
              </div>
            </div>
            <div class="text-2xl font-bold text-orange-600">{{ $attempt->reading_score ?? '-' }}</div>
          </div>
        </div>

        {{-- Timestamp --}}
        <div class="mt-6 text-center text-sm text-gray-500">
          Diselesaikan pada: {{ $attempt->submitted_at?->translatedFormat('l, d F Y - H:i') }} WIB
        </div>
      </div>
    </div>

    {{-- Info --}}
    <div class="mt-6 bg-white rounded-xl shadow-md p-4">
      <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <p class="text-sm text-gray-600">
          Sertifikat akan dikirimkan oleh panitia secara terpisah. Silakan hubungi Lembaga Bahasa untuk informasi lebih lanjut.
        </p>
      </div>
    </div>

    {{-- Back Button --}}
    <div class="mt-8 text-center">
      <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Beranda
      </a>
    </div>

  </div>
</div>
@endsection
