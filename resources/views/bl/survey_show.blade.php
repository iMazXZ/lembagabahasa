{{-- resources/views/bl/survey_show.blade.php --}}
@extends('layouts.front')
@section('title', $survey->title ?? 'Kuesioner Basic Listening')

@push('styles')
<style>
  .hero-gradient{
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
  
  /* Likert Scale Responsive */
  .likert-container {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .likert-options {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
  }
  
  .likert-option {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
  }
  
  .likert-option input[type="radio"] {
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
  }
  
  .likert-option label {
    font-size: 0.7rem;
    color: #6b7280;
    font-weight: 500;
    text-align: center;
    cursor: pointer;
  }
  
  .likert-legend {
    display: flex;
    justify-content: space-between;
    font-size: 0.65rem;
    color: #9ca3af;
    padding: 0 0.5rem;
  }
  
  @media (max-width: 640px) {
    .likert-options {
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .likert-option {
      flex-direction: row;
      justify-content: flex-start;
      padding: 0.5rem;
      background: white;
      border-radius: 0.5rem;
      border: 1px solid #e5e7eb;
    }
    
    .likert-option input[type="radio"] {
      margin-right: 0.75rem;
    }
    
    .likert-option label {
      text-align: left;
      font-size: 0.75rem;
    }
    
    .likert-legend {
      display: none;
    }
  }
</style>
@endpush

@section('content')
  {{-- HERO --}}
  <div class="hero-gradient text-white">
    <div class="max-w-4xl mx-auto px-4 py-6 md:py-8">
      {{-- Badge kategori --}}
      @php
        $cat = $survey->category ?? 'institute';
        $catMap = [
          'tutor' => ['label'=>'📚 Tutor','bg'=>'bg-violet-500/20', 'border'=>'border-violet-300/30'],
          'supervisor' => ['label'=>'👥 Supervisor','bg'=>'bg-emerald-500/20', 'border'=>'border-emerald-300/30'],
          'institute' => ['label'=>'🏛️ Lembaga','bg'=>'bg-blue-500/20', 'border'=>'border-blue-300/30'],
        ];
        $badge = $catMap[$cat] ?? $catMap['institute'];
      @endphp
      
      <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full {{ $badge['bg'] }} border {{ $badge['border'] }} backdrop-blur-sm text-xs font-medium mb-3">
        {{ $badge['label'] }}
      </div>

      <h1 class="text-xl md:text-2xl font-bold mb-2">
        {{ $survey->title ?? 'Kuesioner Basic Listening' }}
      </h1>

      {{-- Meta info --}}
      <div class="flex flex-wrap items-center gap-2 text-xs text-white/80">
        @if(isset($response) && $response->tutor?->name)
          <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
            </svg>
            Tutor: <strong class="text-white">{{ $response->tutor->name }}</strong>
          </span>
        @endif
        @if(isset($response) && $response->supervisor?->name)
          <span class="flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
            </svg>
            Supervisor: <strong class="text-white">{{ $response->supervisor->name }}</strong>
          </span>
        @endif

        @if(method_exists($survey,'isOpen') && !$survey->isOpen())
          <span class="ml-auto flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-500/30 text-rose-100">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            Ditutup
          </span>
        @endif
      </div>
    </div>
  </div>

  {{-- CONTENT --}}
  <div class="max-w-4xl mx-auto px-4 py-6 -mt-4">
    {{-- Flash messages --}}
    @if(session('success'))
      <div class="mb-4 flex items-start gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2.5">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-sm text-emerald-800">{{ session('success') }}</span>
      </div>
    @endif
    @if(session('info'))
      <div class="mb-4 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <span class="text-sm text-amber-800">{{ session('info') }}</span>
      </div>
    @endif
    @if($errors->any())
      <div class="mb-4 flex items-start gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5">
        <svg class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span class="text-sm text-rose-800">Terdapat isian yang belum benar. Silakan periksa kembali.</span>
      </div>
    @endif

    {{-- Card --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
      @if(!empty($survey->description))
        <div class="px-4 py-3.5 md:px-6 md:py-4 bg-gradient-to-br from-gray-50 to-white border-b border-gray-100">
          <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-indigo-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <p class="text-xs md:text-sm text-gray-700">{{ $survey->description }}</p>
          </div>
        </div>
      @endif

      {{-- Tutor & Supervisor Info Banner --}}
      @if(isset($response))
        @php
          $tutorName = $response->tutor?->name;
          $supervisorName = $response->supervisor?->name;
          $hasSelection = $tutorName || $supervisorName;
        @endphp
        
        @if($hasSelection)
          <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                  <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                  </svg>
                  Pembimbing Anda
                </h3>
                <div class="flex flex-wrap items-center gap-2">
                  @if($tutorName)
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white rounded-lg border border-violet-200 text-xs">
                      <span class="font-medium text-violet-600">Tutor:</span>
                      <span class="text-violet-900 font-semibold">{{ $tutorName }}</span>
                    </div>
                  @endif
                  @if($supervisorName)
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white rounded-lg border border-blue-200 text-xs">
                      <span class="font-medium text-blue-600">Supervisor:</span>
                      <span class="text-blue-900 font-semibold">{{ $supervisorName }}</span>
                    </div>
                  @endif
                </div>
              </div>
              <a href="{{ route('bl.survey.edit-choice', ['return' => request()->fullUrl()]) }}" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-indigo-200 text-indigo-700 rounded-lg hover:bg-indigo-50 transition-colors text-xs font-medium whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Ubah Pilihan
              </a>
            </div>
          </div>
        @endif
      @endif
      <form method="POST" action="{{ route('bl.survey.submit', $survey) }}" class="px-4 py-5 md:px-6 md:py-6">
        @csrf

        {{-- Hidden if target=session --}}
        @if(($survey->target ?? 'final') === 'session')
          <input type="hidden" name="session_id" value="{{ old('session_id', $response->session_id ?? $survey->session_id) }}">
        @endif

        @php
          $questions = $survey->questions ?? collect();
        @endphp

        <div class="space-y-6">
          @forelse($questions as $idx => $q)
            <div class="pb-6 border-b border-gray-100 last:border-0 last:pb-0">
              {{-- Question Header --}}
              <div class="flex items-start gap-2.5 mb-3">
                <div class="flex-shrink-0 w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                  {{ $loop->iteration }}
                </div>
                <div class="flex-1">
                  <label class="block text-sm md:text-base font-medium text-gray-900 leading-snug">
                    {!! nl2br(e($q->question)) !!}
                    @if($q->is_required)
                      <span class="text-rose-500 ml-0.5" title="Wajib diisi">*</span>
                    @endif
                  </label>
                  @if(!empty($q->hint))
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">💡 {{ $q->hint }}</p>
                  @endif
                </div>
              </div>

              {{-- Field --}}
              @php
                $name = "q.{$q->id}";
                $err  = $errors->first($name);
              @endphp

              @if($q->type === 'likert')
                {{-- Likert Scale --}}
                <div class="likert-container mt-3">
                  <div class="likert-options">
                    <div class="likert-option">
                      <input id="q-{{ $q->id }}-1" type="radio" name="q[{{ $q->id }}]" value="1"
                             class="text-indigo-600 focus:ring-indigo-500"
                             @checked(old("q.{$q->id}") == '1')>
                      <label for="q-{{ $q->id }}-1">Sangat Tidak Setuju</label>
                    </div>
                    <div class="likert-option">
                      <input id="q-{{ $q->id }}-2" type="radio" name="q[{{ $q->id }}]" value="2"
                             class="text-indigo-600 focus:ring-indigo-500"
                             @checked(old("q.{$q->id}") == '2')>
                      <label for="q-{{ $q->id }}-2">Tidak Setuju</label>
                    </div>
                    <div class="likert-option">
                      <input id="q-{{ $q->id }}-3" type="radio" name="q[{{ $q->id }}]" value="3"
                             class="text-indigo-600 focus:ring-indigo-500"
                             @checked(old("q.{$q->id}") == '3')>
                      <label for="q-{{ $q->id }}-3">Netral</label>
                    </div>
                    <div class="likert-option">
                      <input id="q-{{ $q->id }}-4" type="radio" name="q[{{ $q->id }}]" value="4"
                             class="text-indigo-600 focus:ring-indigo-500"
                             @checked(old("q.{$q->id}") == '4')>
                      <label for="q-{{ $q->id }}-4">Setuju</label>
                    </div>
                    <div class="likert-option">
                      <input id="q-{{ $q->id }}-5" type="radio" name="q[{{ $q->id }}]" value="5"
                             class="text-indigo-600 focus:ring-indigo-500"
                             @checked(old("q.{$q->id}") == '5')>
                      <label for="q-{{ $q->id }}-5">Sangat Setuju</label>
                    </div>
                  </div>
                  <div class="likert-legend">
                    <span>← Sangat Tidak Setuju</span>
                    <span>Sangat Setuju →</span>
                  </div>
                </div>
              @else
                {{-- Textarea --}}
                <div class="mt-3">
                  <textarea
                    name="q[{{ $q->id }}]"
                    rows="3"
                    maxlength="2000"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm placeholder-gray-400"
                    placeholder="Tulis jawaban Anda di sini... (maksimal 2000 karakter)"
                  >{{ old("q.{$q->id}") }}</textarea>
                  <div class="flex justify-between items-center mt-1">
                    <p class="text-xs text-gray-400">Opsional - jawaban akan sangat membantu kami</p>
                  </div>
                </div>
              @endif

              {{-- Error --}}
              @if($err)
                <div class="mt-2 flex items-center gap-1.5 text-rose-600">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                  </svg>
                  <p class="text-xs">{{ $err }}</p>
                </div>
              @endif
            </div>
          @empty
            <div class="text-center py-12">
              <div class="text-5xl mb-3">📝</div>
              <p class="text-sm text-gray-600">Belum ada pertanyaan pada kuesioner ini.</p>
            </div>
          @endforelse
        </div>

        {{-- Actions --}}
        <div class="mt-8 pt-6 border-t border-gray-100">
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <button
              type="submit"
              class="flex-1 sm:flex-initial inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-2.5 text-sm font-semibold text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 shadow-lg shadow-indigo-500/30 transition-all"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              Kirim Jawaban
            </button>
          </div>

          <div class="mt-4 flex items-start gap-2 p-3 bg-blue-50 rounded-lg border border-blue-100">
            <svg class="w-4 h-4 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <p class="text-xs text-blue-800 leading-relaxed">
              Dengan mengirim jawaban, Anda menyetujui data evaluasi digunakan untuk peningkatan kualitas pembelajaran Basic Listening.
            </p>
          </div>
        </div>
      </form>
    </div>
  </div>

  @push('scripts')
  <script>
    // Auto-scroll ke field error pertama
    document.addEventListener('DOMContentLoaded', () => {
      const err = document.querySelector('.text-rose-600');
      if (err) {
        setTimeout(() => {
          err.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 100);
      }
    });
  </script>
  @endpush
@endsection