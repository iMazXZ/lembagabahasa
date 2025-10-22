@extends('layouts.front')
@section('title', 'Basic Listening Quiz')

@push('styles')
<style>
  .quiz-wrap{
    max-width: 960px; margin: 2rem auto; background:#fff; border-radius:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.06); padding:2rem; position:relative; overflow:hidden;
  }
  .progress-bar{height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-bottom:1.25rem}
  .progress-fill{height:100%;background:linear-gradient(90deg,#6366f1,#8b5cf6,#d946ef);width:0%;transition:width .4s ease}
  .timer{position:absolute;top:1rem;right:1rem;background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff;
    padding:.5rem 1rem;border-radius:999px;font-weight:700;font-size:.9rem}
  .question-card{animation:fadeIn .45s ease-out}
  @keyframes fadeIn{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
  .answer-option{
    width:100%; text-align:left; background:#f9fafb; border:2px solid #e5e7eb; border-radius:12px;
    padding:1rem; font-size:1rem; font-weight:500; margin-bottom:1rem; transition:all .2s ease;
  }
  .answer-option:hover{background:#f3f4f6;border-color:#a5b4fc}
  .answer-option.is-selected{border-color:#6366f1;background:#eef2ff}
  .answer-option:disabled{opacity:.75;cursor:not-allowed}
  .btn-nav{
    background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff; border:none; padding:.75rem 1.25rem;
    border-radius:10px; font-weight:600; transition:filter .2s ease;
  }
  .btn-nav:hover{filter:brightness(.95)}
  .btn-submit{background:linear-gradient(135deg,#059669,#10b981)}
  .qgrid-wrap{ margin-bottom:1rem }
  .qgrid{
    display:grid; gap:.5rem;
    grid-template-columns: repeat(auto-fill, minmax(42px, 1fr));
  }
  .qbox{
    display:flex; align-items:center; justify-content:center;
    height:42px; border-radius:10px; font-weight:800;
    border:2px solid #e5e7eb; user-select:none; transition:all .2s ease;
    text-decoration:none;
  }
  .qbox.unanswered{ background:#fee2e2; border-color:#fecaca; color:#991b1b; }
  .qbox.answered{ background:#d1fae5; border-color:#6ee7b7; color:#065f46; }
  .qbox.current{ outline:3px solid #818cf8; }
  .qbox:hover{ filter:brightness(.98); transform:translateY(-1px); }
  .qlegend{ display:flex; gap:.75rem; align-items:center; margin-top:.5rem }
  .qlegend .pill{
    width:14px; height:14px; border-radius:4px; display:inline-block; margin-right:.35rem;
    border:1px solid rgba(0,0,0,.08);
  }
  .pill-ans{ background:#d1fae5 }
  .pill-unans{ background:#fee2e2 }
  .pill-cur{ background:#eef2ff; outline:2px solid #818cf8 }
</style>
@endpush

@section('content')

@php
  use Illuminate\Support\Facades\Storage;
  use Illuminate\Support\Str;

  // Handle answeredIds
  $answeredIds = $answeredIds ?? [];
  
  // Progress calculation
  $progressPercentage = round((($currentIndex + 1) / max(1, $questions->count())) * 100, 2);
  
  // Audio handling with error prevention
  $qAudioSrc = null;
  if (!empty($question->audio_url ?? null)) {
      try {
          $qAudioSrc = Str::startsWith($question->audio_url, ['http://','https://'])
              ? $question->audio_url
              : (Storage::exists($question->audio_url) ? Storage::url($question->audio_url) : null);
      } catch (Exception $e) {
          $qAudioSrc = null;
      }
  }
@endphp

<div class="quiz-wrap">
  {{-- Timer --}}
  @if(!empty($remainingSeconds))
    <div class="timer" id="timer">--:--</div>
  @endif

  {{-- Progress Bar --}}
  <div class="progress-bar">
    <div class="progress-fill" id="progressFill"></div>
  </div>

  {{-- Judul & Posisi --}}
  <div class="mb-4">
    <h2 class="text-2xl font-extrabold text-gray-900">Soal {{ $currentIndex + 1 }} dari {{ $questions->count() }}</h2>
    @if($attempt->session?->title)
      <p class="text-sm text-gray-500 mt-1">{{ $attempt->session->title }}</p>
    @endif
  </div>

  {{-- Navigator Soal --}}
  <div class="qgrid-wrap">
    <div class="qgrid">
      @foreach($questions as $i => $q)
        @php
          $isAnswered = in_array($q->id, $answeredIds);
          $classes = 'qbox ' . ($isAnswered ? 'answered' : 'unanswered') . ' ' . ($i === $currentIndex ? 'current' : '');
        @endphp
        <a href="{{ route('bl.quiz.show', [$attempt, 'q' => $i]) }}"
          class="{{ $classes }} qbox-link"
          title="Soal {{ $i + 1 }}">{{ $i + 1 }}</a>
      @endforeach
    </div>
  </div>

  {{-- Audio --}}
  @if(!empty($qAudioSrc))
    <div class="mb-4">
      <audio controls class="w-full">
        <source src="{{ $qAudioSrc }}" type="audio/mpeg">
        Browser Anda tidak mendukung pemutar audio.
      </audio>
      <div class="text-xs text-gray-500 mt-1">Audio pertanyaan</div>
    </div>
  @endif

  {{-- Question Card --}}
  <div class="question-card">
    <p class="text-gray-800 text-lg leading-relaxed mb-6">{{ $question->question }}</p>

    <form method="POST" action="{{ route('bl.quiz.answer', $attempt) }}">
      @csrf
      <input type="hidden" name="question_id" value="{{ $question->id }}">
      <input type="hidden" name="q" value="{{ $currentIndex }}">

      @foreach(['A','B','C','D'] as $opt)
        @php $val = $question->{'option_'.strtolower($opt)}; @endphp
        @if($val)
          <button type="submit" name="answer" value="{{ $opt }}"
                  class="answer-option {{ ($answer->answer ?? null) === $opt ? 'is-selected' : '' }}">
            <strong class="mr-1">{{ $opt }}.</strong> {{ $val }}
          </button>
        @endif
      @endforeach
    </form>
  </div>

  {{-- 🔀 Navigasi --}}
  <div class="flex justify-between items-center mt-6">
      @if($currentIndex > 0)
          <a href="{{ route('bl.quiz.show', [$attempt, 'q' => $currentIndex - 1]) }}" class="btn-nav">← Sebelumnya</a>
      @else
          <span></span>
      @endif

      @if($currentIndex < $questions->count() - 1)
          <a href="{{ route('bl.quiz.show', [$attempt, 'q' => $currentIndex + 1]) }}" class="btn-nav">Berikutnya →</a>
      @else
          {{-- 🆕 Tampilkan warning jika ada soal belum terjawab --}}
          @if($unansweredCount > 0)
              <div class="text-right">
                  <div class="mb-2 text-sm text-amber-600 font-medium">
                      ⚠️ Masih ada <strong>{{ $unansweredCount }} soal</strong> belum terjawab
                  </div>
                  <button type="button" 
                          onclick="showSubmitConfirm()"
                          class="btn-nav bg-amber-500 hover:bg-amber-600">
                      Kumpulkan Jawaban
                  </button>
              </div>
          @else
              <form method="POST" action="{{ route('bl.quiz.submit', $attempt) }}" id="submitForm">
                  @csrf
                  <button type="submit" class="btn-nav btn-submit">Kumpulkan Jawaban</button>
              </form>
          @endif
      @endif
  </div>

  {{-- 🆕 Modal Konfirmasi Submit --}}
  @if(session('showSubmitConfirm') || $unansweredCount > 0)
  <div id="submitConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: {{ session('showSubmitConfirm') ? 'flex' : 'none' }};">
      <div class="bg-white rounded-xl p-6 max-w-md mx-4">
          <div class="text-center">
              <div class="w-16 h-16 mx-auto mb-4 bg-amber-100 rounded-full flex items-center justify-center">
                  <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                  </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2">Konfirmasi Submit</h3>
              <p class="text-gray-600 mb-4">
                  Masih ada <strong class="text-amber-600">{{ $unansweredCount ?? session('unansweredCount', 0) }} soal</strong> yang belum terjawab. 
                  Yakin ingin mengumpulkan jawaban?
              </p>
              <div class="flex gap-3 justify-center">
                  <button type="button" 
                          onclick="hideSubmitConfirm()"
                          class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                      Lanjutkan Mengerjakan
                  </button>
                  <form method="POST" action="{{ route('bl.quiz.force-submit', $attempt) }}">
                      @csrf
                      <button type="submit" 
                              class="px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-colors">
                          Ya, Kumpulkan
                      </button>
                  </form>
              </div>
          </div>
      </div>
  </div>
  @endif
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Prevent leave protection
  let isSubmitting = false;
  function beforeUnloadHandler(e){
    if(isSubmitting) return;
    e.preventDefault();
    e.returnValue = '';
    return e.returnValue;
  }
  window.addEventListener('beforeunload', beforeUnloadHandler);
  
  function allowSubmitAndLeave(){
    isSubmitting = true;
    window.removeEventListener('beforeunload', beforeUnloadHandler);
  }

  // Answer selection
  document.querySelectorAll('form[action*="answer"] button.answer-option').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const all = e.currentTarget.closest('form').querySelectorAll('button.answer-option');
      all.forEach(b => b.classList.remove('is-selected'));
      e.currentTarget.classList.add('is-selected');
      allowSubmitAndLeave();
    }, {passive:true});
  });

  // Navigation and submit handlers
  document.querySelectorAll('a.btn-nav').forEach(el => {
    el.addEventListener('click', allowSubmitAndLeave);
  });

  document.querySelectorAll('form[action*="submit"]').forEach(form => {
    form.addEventListener('submit', function(e) {
      allowSubmitAndLeave();
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Mengumpulkan...';
      }
    });
  });

  // 🆕 Modal konfirmasi submit
  function showSubmitConfirm() {
      const modal = document.getElementById('submitConfirmModal');
      if (modal) modal.style.display = 'flex';
  }

  function hideSubmitConfirm() {
      const modal = document.getElementById('submitConfirmModal');
      if (modal) modal.style.display = 'none';
  }

  // Tampilkan modal jika ada session warning
  @if(session('showSubmitConfirm'))
      document.addEventListener('DOMContentLoaded', function() {
          showSubmitConfirm();
      });
  @endif

  // Close modal ketika klik di luar
  document.addEventListener('click', function(e) {
      const modal = document.getElementById('submitConfirmModal');
      if (e.target === modal) {
          hideSubmitConfirm();
      }
  });

  // Timer with improved handling
  const total = {{ (int) ($remainingSeconds ?? 0) }};
  if (total > 0) {
    let secondsLeft = total;
    const timerEl = document.getElementById('timer');
    
    if (timerEl) {
        const tick = () => {
            const m = Math.floor(secondsLeft/60), s = secondsLeft%60;
            timerEl.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            
            if (secondsLeft <= 0) {
                allowSubmitAndLeave();
                const submitForm = document.querySelector('form[action*="submit"]');
                if (submitForm) submitForm.submit();
                return;
            }
            secondsLeft--;
        };
        
        tick();
        const intervalId = setInterval(tick, 1000);
        
        window.addEventListener('beforeunload', () => {
            clearInterval(intervalId);
        });
    }
  }

  // Question box links
  document.querySelectorAll('.qbox-link').forEach(a => {
    a.addEventListener('click', allowSubmitAndLeave);
  });

  // Progress bar
  const progress = {{ $progressPercentage }};
  const pf = document.getElementById('progressFill');
  if (pf) pf.style.width = progress + '%';
</script>
@endpush