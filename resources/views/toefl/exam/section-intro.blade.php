{{-- resources/views/toefl/exam/section-intro.blade.php --}}
@extends('layouts.front')
@section('title', 'Persiapan Section - TOEFL')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-100 via-gray-100 to-zinc-100 flex items-center justify-center px-4 py-12">
  <div class="w-full max-w-2xl">
    
    {{-- Section Card --}}
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
      
      {{-- Header --}}
      @php
        $colors = [
          'listening' => ['bg' => 'bg-blue-600', 'light' => 'bg-blue-100', 'text' => 'text-blue-600'],
          'structure' => ['bg' => 'bg-purple-600', 'light' => 'bg-purple-100', 'text' => 'text-purple-600'],
          'reading' => ['bg' => 'bg-orange-600', 'light' => 'bg-orange-100', 'text' => 'text-orange-600'],
        ];
        $color = $colors[$section] ?? $colors['listening'];
        $sectionNumber = match($section) {
          'listening' => 1,
          'structure' => 2,
          'reading' => 3,
          default => 1,
        };
        $sectionTitle = match($section) {
          'listening' => 'Listening Comprehension',
          'structure' => 'Structure & Written Expression',
          'reading' => 'Reading Comprehension',
          default => $section,
        };
      @endphp
      
      <div class="{{ $color['bg'] }} text-white p-8 text-center">
        <div class="text-sm uppercase tracking-wide opacity-80 mb-2">Section {{ $sectionNumber }}</div>
        <h1 class="text-3xl font-bold mb-2">{{ $sectionTitle }}</h1>
        <div class="flex justify-center gap-4 mt-4">
          <div class="bg-white/20 rounded-lg px-4 py-2">
            <div class="text-2xl font-bold">{{ $questionCount }}</div>
            <div class="text-xs opacity-80">Soal</div>
          </div>
          <div class="bg-white/20 rounded-lg px-4 py-2">
            <div class="text-2xl font-bold">{{ $duration }}</div>
            <div class="text-xs opacity-80">Menit</div>
          </div>
        </div>
      </div>

      {{-- Directions --}}
      <div class="p-8">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 {{ $color['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Directions
        </h2>
        
        @if($section === 'listening')
          <div class="prose prose-sm max-w-none text-gray-600 space-y-3">
            <p>In this section of the test, you will have an opportunity to demonstrate your ability to understand conversations and talks in English.</p>
            <p>There are three parts to this section:</p>
            <ul class="list-disc pl-5 space-y-1">
              <li><strong>Part A:</strong> Short Conversations (30 questions)</li>
              <li><strong>Part B:</strong> Longer Conversations (8 questions)</li>
              <li><strong>Part C:</strong> Talks (12 questions)</li>
            </ul>
            <p>You will hear each conversation or talk only once. After each conversation or talk, you will hear several questions. The questions will not be printed; you will hear them on the recording.</p>
            <p class="bg-yellow-50 border-l-4 border-yellow-500 p-3 rounded-r">
              <strong>⚠️ Penting:</strong> Audio hanya diputar <u>satu kali</u>. Pastikan audio dan koneksi Anda stabil sebelum memulai.
            </p>
          </div>
        @elseif($section === 'structure')
          <div class="prose prose-sm max-w-none text-gray-600 space-y-3">
            <p>This section tests your ability to recognize language that is appropriate for standard written English.</p>
            <p>There are two types of questions:</p>
            <ul class="list-disc pl-5 space-y-1">
              <li><strong>Sentence Completion (1-15):</strong> Choose the best answer to complete the sentence.</li>
              <li><strong>Error Identification (16-40):</strong> Identify the underlined word or phrase that should be corrected.</li>
            </ul>
          </div>
        @else
          <div class="prose prose-sm max-w-none text-gray-600 space-y-3">
            <p>In this section you will read several passages. Each one is followed by a number of questions about it.</p>
            <p>You are to choose the one best answer (A), (B), (C), or (D) to each question. Then, mark your answer.</p>
            <p>Answer all questions about the information in a passage on the basis of what is <strong>stated</strong> or <strong>implied</strong> in that passage.</p>
          </div>
        @endif

        {{-- Timer Warning --}}
        <div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-4">
          <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-red-800">
              <p class="font-semibold">Timer akan dimulai setelah Anda menekan tombol "Mulai Section"</p>
              <p class="mt-1 text-red-600">Pastikan Anda sudah siap sebelum melanjutkan.</p>
            </div>
          </div>
        </div>

        {{-- Start Button --}}
        <div class="mt-8">
          <form method="POST" action="{{ route('toefl.start-section', $attempt) }}">
            @csrf
            <input type="hidden" name="section" value="{{ $section }}">
            <button type="submit" class="w-full flex items-center justify-center gap-3 px-8 py-4 {{ $color['bg'] }} hover:opacity-90 text-white font-bold text-lg rounded-xl shadow-lg transition-all transform hover:scale-[1.02] active:scale-[0.98]">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Mulai Section {{ $sectionNumber }}
            </button>
          </form>
        </div>

        {{-- Back to Section Selection --}}
        @if($section !== 'listening')
        <div class="mt-4 text-center">
          <p class="text-xs text-gray-400">Section sebelumnya sudah selesai dan tidak bisa diulang</p>
        </div>
        @endif
      </div>
    </div>

    {{-- Exam Info --}}
    <div class="mt-6 text-center text-sm text-gray-500">
      {{ $attempt->exam->name }}
    </div>

  </div>
</div>
@endsection
