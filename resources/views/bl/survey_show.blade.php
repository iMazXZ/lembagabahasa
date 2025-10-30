{{-- resources/views/bl/survey_show.blade.php --}}
@extends('layouts.front')
@section('title', 'Part 1: Kuesioner ' . ucfirst($survey->category))

@push('styles')
<style>
  /* ==== Hero Gradient Animation ==== */
  .hero-survey {
    background: linear-gradient(-45deg, #4f46e5, #6366f1, #7c3aed, #8b5cf6, #0ea5e9);
    background-size: 400% 400%;
    animation: gradientFlow 15s ease infinite;
    position: relative;
    overflow: hidden;
  }
  @keyframes gradientFlow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  /* ==== Floating Shapes ==== */
  .hero-survey::before {
    content: '';
    position: absolute;
    inset: 0;
    background: 
      radial-gradient(circle at 15% 25%, rgba(255,255,255,.12), transparent 45%),
      radial-gradient(circle at 85% 75%, rgba(255,255,255,.1), transparent 45%);
    animation: pulse 10s ease-in-out infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
  }

  /* ==== Progress Bar ==== */
  .progress-bar {
    height: 6px;
    background: rgba(255,255,255,0.2);
    border-radius: 9999px;
    overflow: hidden;
    position: relative;
  }
  .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #34d399, #10b981);
    border-radius: 9999px;
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
  }

  /* ==== Alert Boxes ==== */
  .alert-box {
    border-radius: 0.875rem;
    padding: 1rem;
    font-size: 0.875rem;
    border: 2px solid;
    display: flex;
    gap: 0.75rem;
    align-items: start;
    animation: slideDown 0.4s ease-out;
  }
  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-15px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  .alert-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border-color: #10b981;
  }
  .alert-info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
    border-color: #3b82f6;
  }
  .alert-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border-color: #f59e0b;
  }
  .alert-error {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    border-color: #ef4444;
  }

  /* ==== Question Card ==== */
  .question-card {
    background: white;
    border: 2px solid #f3f4f6;
    border-radius: 1rem;
    padding: 1.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
  }
  .question-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  .question-card:hover {
    border-color: #e0e7ff;
    box-shadow: 0 8px 24px rgba(99, 102, 241, 0.12);
    transform: translateY(-2px);
  }
  .question-card:hover::before {
    opacity: 1;
  }
  .question-card:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
  }
  .question-card:focus-within::before {
    opacity: 1;
  }

  /* ==== Question Number Badge ==== */
  .question-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
    border-radius: 0.5rem;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
  }

  /* ==== Likert Scale Radio Buttons ==== */
  .likert-option {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    min-width: 70px;
  }
  .likert-option:hover {
    border-color: #c7d2fe;
    background: #f5f3ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
  }
  .likert-option input[type="radio"] {
    appearance: none;
    width: 1.25rem;
    height: 1.25rem;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
  }
  .likert-option input[type="radio"]:checked {
    border-color: #6366f1;
    background: #6366f1;
  }
  .likert-option input[type="radio"]:checked::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
  }
  .likert-option:has(input[type="radio"]:checked) {
    border-color: #6366f1;
    background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
    box-shadow: 0 4px 16px rgba(99, 102, 241, 0.25);
  }
  .likert-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    transition: color 0.3s ease;
  }
  .likert-option:has(input[type="radio"]:checked) .likert-label {
    color: #4f46e5;
  }

  /* ==== Textarea Styling ==== */
  .custom-textarea {
    width: 100%;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 0.875rem;
    color: #111827;
    transition: all 0.3s ease;
    resize: vertical;
    font-family: inherit;
    line-height: 1.6;
  }
  .custom-textarea:hover {
    border-color: #c7d2fe;
  }
  .custom-textarea:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
  }
  .custom-textarea::placeholder {
    color: #9ca3af;
  }

  /* ==== Buttons ==== */
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  .btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
  }
  .btn:hover::before {
    width: 300px;
    height: 300px;
  }
  .btn-primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
  }
  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
  }
  .btn-secondary {
    background: white;
    color: #4b5563;
    border: 2px solid #e5e7eb;
  }
  .btn-secondary:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    transform: translateY(-2px);
  }
  .btn-ghost {
    background: transparent;
    color: #6b7280;
    border: none;
  }
  .btn-ghost:hover {
    color: #4b5563;
    background: rgba(0,0,0,0.03);
  }

  /* ==== Badge ==== */
  .badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
    color: #6b21a8;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    border: 1px solid #c4b5fd;
  }

  /* ==== Icon Wrapper ==== */
  .icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.75rem;
    font-size: 1rem;
    flex-shrink: 0;
  }

  /* ==== Fade In Animation ==== */
  .fade-in {
    animation: fadeInUp 0.6s ease-out forwards;
    opacity: 0;
  }
  .fade-delay-1 { animation-delay: 0.05s; }
  .fade-delay-2 { animation-delay: 0.1s; }
  .fade-delay-3 { animation-delay: 0.15s; }
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* ==== Likert Scale Labels ==== */
  .likert-scale-labels {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding: 0 0.5rem;
  }
  .scale-label {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
  }
</style>
@endpush

@section('content')
  {{-- HERO --}}
  <div class="hero-survey text-white">
    <div class="max-w-5xl mx-auto px-4 py-8 md:py-10 relative z-10">
      <div class="mb-4">
        <div class="inline-flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-3 py-1.5 border border-white/30">
          <i class="fa-solid fa-clipboard-question text-sm"></i>
          <span class="text-xs font-semibold">Kuesioner {{ ucfirst($survey->category) }}</span>
        </div>
      </div>
      
      <h1 class="text-3xl md:text-4xl font-extrabold mb-3">
        {{ $survey->title ? $survey->title : ('Part 1: Kuesioner ' . ucfirst($survey->category)) }}
      </h1>
      
      <div class="flex flex-wrap items-center gap-3 text-sm md:text-base">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-user-check"></i>
          <span class="text-blue-100">Penilaian untuk</span>
          <span class="font-bold">{{ ucfirst($survey->category) }}</span>
        </div>
        
        @if($survey->category === 'tutor' && $response->tutor_id)
          <div class="flex items-center gap-2 bg-white/20 backdrop-blur-sm rounded-full px-3 py-1.5 border border-white/30">
            <i class="fa-solid fa-chalkboard-user text-sm"></i>
            <span class="font-semibold">{{ optional(\App\Models\User::find($response->tutor_id))->name }}</span>
          </div>
        @endif
      </div>

      {{-- Progress Bar --}}
      @php
        $totalQuestions = $survey->questions->count();
        $answeredCount = isset($answers) ? (is_array($answers) ? count(array_filter($answers)) : $answers->count()) : 0;
        $progressPercent = $totalQuestions > 0 ? ($answeredCount / $totalQuestions) * 100 : 0;
      @endphp
      <div class="mt-6">
        <div class="flex items-center justify-between text-xs font-medium mb-2">
          <span>Progress Pengisian</span>
          <span>{{ $answeredCount }}/{{ $totalQuestions }} Pertanyaan</span>
        </div>
        <div class="progress-bar">
          <div class="progress-fill" style="width: {{ $progressPercent }}%"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="max-w-3xl mx-auto px-4 py-8">
    
    {{-- Flash Messages --}}
    @foreach (['success','info','warning','error'] as $f)
      @if (session($f))
        <div class="alert-box alert-{{ $f }} mb-6 fade-in">
          <div class="icon-wrapper" style="background: {{ $f==='success'?'linear-gradient(135deg, #d1fae5, #a7f3d0)':($f==='info'?'linear-gradient(135deg, #dbeafe, #bfdbfe)':($f==='warning'?'linear-gradient(135deg, #fef3c7, #fde68a)':'linear-gradient(135deg, #fee2e2, #fecaca)')) }}; color: {{ $f==='success'?'#065f46':($f==='info'?'#1e40af':($f==='warning'?'#92400e':'#991b1b')) }}">
            <i class="fa-solid fa-{{ $f==='success'?'circle-check':($f==='info'?'info-circle':($f==='warning'?'triangle-exclamation':'circle-xmark')) }}"></i>
          </div>
          <div class="flex-1">
            <div class="font-semibold mb-0.5">
              {{ $f==='success'?'Berhasil!':($f==='info'?'Informasi':($f==='warning'?'Perhatian':'Error!')) }}
            </div>
            <div>{{ session($f) }}</div>
          </div>
        </div>
      @endif
    @endforeach

    {{-- Validation Errors --}}
    @if($errors->any())
      <div class="alert-box alert-error mb-6 fade-in">
        <div class="icon-wrapper" style="background: linear-gradient(135deg, #fee2e2, #fecaca); color: #991b1b;">
          <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div class="flex-1">
          <div class="font-bold mb-2">Periksa kembali isian:</div>
          <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    {{-- Survey Form --}}
    <form method="POST" action="{{ route('bl.survey.submit', $survey) }}" class="space-y-5">
      @csrf

      {{-- Hidden Fields --}}
      @if($survey->target === 'session')
        <input type="hidden" name="session_id" value="{{ request('session_id', $survey->session_id) }}">
      @endif

      @if($survey->category === 'tutor' && $response->tutor_id)
        <input type="hidden" name="tutor_id" value="{{ (int) $response->tutor_id }}">
      @endif

      {{-- Questions List --}}
      @foreach($survey->questions as $idx => $q)
        @php
          $qText = $q->question ?? $q->text ?? $q->prompt ?? $q->title ?? '— (Pertanyaan belum diisi) —';
          $type = $q->type ?? 'text';
          $required = (bool) ($q->is_required ?? false);
          $key = "q.{$q->id}";
          $ans = $answers[$q->id] ?? null;
        @endphp

        <div class="question-card fade-in fade-delay-{{ min($idx, 3) }}">
          <div class="flex items-start gap-3 mb-4">
            <div class="question-number">{{ $idx + 1 }}</div>
            <div class="flex-1">
              <label class="block text-base font-semibold text-gray-900 leading-relaxed">
                {{ $qText }}
                @if($required)
                  <span class="text-rose-500 ml-1">*</span>
                @endif
              </label>
            </div>
          </div>

          @if($type === 'likert')
            {{-- Likert Scale --}}
            <div class="mt-4">
              <div class="likert-scale-labels">
                <span class="scale-label">Sangat Tidak Setuju</span>
                <span class="scale-label">Sangat Setuju</span>
              </div>
              <div class="flex flex-wrap gap-3 justify-center md:justify-start">
                @for($i=1; $i<=5; $i++)
                  <label class="likert-option">
                    <input
                      type="radio"
                      name="q[{{ $q->id }}]"
                      value="{{ $i }}"
                      @checked( (int) old($key, (int) ($ans?->likert_value)) === $i )
                      {{ $required ? 'required' : '' }}
                    >
                    <span class="likert-label">{{ $i }}</span>
                  </label>
                @endfor
              </div>
            </div>
          @else
            {{-- Text Answer --}}
            <div class="mt-4">
              <textarea
                name="q[{{ $q->id }}]"
                rows="4"
                class="custom-textarea"
                placeholder="Tulis jawaban Anda di sini..."
                {{ $required ? 'required' : '' }}
              >{{ old($key, $ans?->text_value) }}</textarea>
            </div>
          @endif

          @error($key)
            <p class="mt-3 text-xs text-rose-600 flex items-center gap-1">
              <i class="fa-solid fa-circle-exclamation"></i>
              {{ $message }}
            </p>
          @enderror
        </div>
      @endforeach

      {{-- Action Buttons --}}
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t-2 border-gray-100 fade-in">
        <div class="flex items-center gap-3">
          <a href="{{ route('bl.survey.required') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            <span class="relative z-10">Kembali</span>
          </a>
          <a href="{{ route('bl.survey.reset-choice') }}" class="btn btn-ghost">
            <i class="fa-solid fa-rotate-left"></i>
            <span class="relative z-10">Reset Pilihan</span>
          </a>
        </div>
        
        <button type="submit" class="btn btn-primary">
          <i class="fa-solid fa-paper-plane relative z-10"></i>
          <span class="relative z-10">Kirim Jawaban</span>
          <i class="fa-solid fa-arrow-right relative z-10"></i>
        </button>
      </div>
    </form>

    {{-- Info Footer --}}
    <div class="mt-8 text-center text-sm text-gray-500 fade-in">
      <i class="fa-solid fa-info-circle mr-1"></i>
      Pastikan semua jawaban telah terisi dengan benar sebelum mengirim
    </div>

  </div>
@endsection