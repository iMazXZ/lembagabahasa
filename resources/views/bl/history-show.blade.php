@extends('layouts.front')
@section('title', 'Detail Hasil')

@section('content')
{{-- HERO Section --}}
<div class="bg-gradient-to-br from-blue-600 to-indigo-800 text-white">
  <div class="max-w-6xl mx-auto px-4 py-12 md:py-16">
    <div class="text-center">
      @php $isUAS = (int)$attempt->session->number > 5; @endphp
      <div class="inline-block px-3 py-1 rounded-full mb-4 {{ $isUAS ? 'bg-pink-500/30' : 'bg-blue-500/30' }} backdrop-blur-sm border border-white/20">
        <span class="font-semibold text-sm">{{ $isUAS ? 'Final Exam' : 'Meeting '.$attempt->session->number }}</span>
      </div>

      <h1 class="text-3xl md:text-4xl font-bold mb-3">{{ $attempt->session->title }}</h1>

      @if($attempt->submitted_at)
        <div class="flex items-center justify-center gap-1 text-blue-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>Dikumpulkan {{ $attempt->submitted_at->format('d M Y, H:i') }} WIB</span>
        </div>
      @else
        <div class="flex items-center justify-center gap-2 text-amber-200">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span class="font-semibold">Belum Dikumpulkan</span>
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Content Section --}}
<div class="bg-gray-50 min-h-screen">
  <div class="max-w-6xl mx-auto px-4 py-8">
    @if($attempt->submitted_at)
      @php
        // Hitung statistik
        $totalUnits   = 0;
        $correctUnits = 0;

        foreach ($questions as $q) {
            $type = $q->type ?? 'multiple_choice';

            if ($type === 'fib_paragraph') {
                $keys         = (array)($q->fib_answer_key ?? []);
                $placeholders = (array)($q->fib_placeholders ?? array_keys($keys));
                $placeholders = array_values(array_unique(array_map('strval', $placeholders)));

                $unitCount = count($placeholders);
                if ($unitCount <= 0) {
                    $unitCount = $answers->where('question_id', $q->id)->count();
                }
                $unitCount    = max(1, (int)$unitCount);
                $totalUnits  += $unitCount;

                $ansCorrect   = $answers->where('question_id', $q->id)->where('is_correct', true)->count();
                $correctUnits += min($unitCount, $ansCorrect);
            } else {
                $totalUnits += 1;
                $ans = $answers->firstWhere('question_id', $q->id);
                if ($ans && $ans->is_correct) $correctUnits += 1;
            }
        }

        $incorrectUnits = max(0, $totalUnits - $correctUnits);
        $accuracy       = $totalUnits > 0 ? round(($correctUnits / $totalUnits) * 100, 1) : 0;
      @endphp

      {{-- Summary Stats --}}
      <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">Ringkasan Hasil</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div class="text-center bg-emerald-50 border border-emerald-200 rounded-lg p-4">
            <div class="text-2xl font-bold text-emerald-600">{{ $correctUnits }}</div>
            <div class="text-xs text-slate-700 mt-1">Benar</div>
          </div>
          <div class="text-center bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="text-2xl font-bold text-red-600">{{ $incorrectUnits }}</div>
            <div class="text-xs text-slate-700 mt-1">Salah</div>
          </div>
        </div>
      </div>

      {{-- Questions Review --}}
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Pembahasan</h2>

        <div class="space-y-4">
          @foreach($questions as $idx => $q)
            @php $qType = $q->type ?? 'multiple_choice'; @endphp

            {{-- Multiple Choice --}}
            @if($qType !== 'fib_paragraph')
              @php
                $ans       = $answers->firstWhere('question_id', $q->id);
                $chosen    = $ans->answer ?? null;
                $isCorrect = (bool)($ans->is_correct ?? false);
              @endphp

              <div class="bg-white rounded-lg border-l-4 {{ $isCorrect ? 'border-emerald-500' : 'border-red-500' }} border-t border-r border-b border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start gap-3 mb-4">
                  <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center font-bold text-slate-700">
                    {{ $idx + 1 }}
                  </div>
                  <div class="flex-1 font-medium text-gray-900">{{ $q->question }}</div>
                  @if($isCorrect)
                    <span class="flex-shrink-0 px-2 py-1 rounded bg-emerald-100 text-emerald-700 text-xs font-semibold">✓ Benar</span>
                  @else
                    <span class="flex-shrink-0 px-2 py-1 rounded bg-red-100 text-red-700 text-xs font-semibold">✗ Salah</span>
                  @endif
                </div>

                <div class="space-y-2 ml-11">
                  @foreach(['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d] as $key => $text)
                    @php
                      $optClass = '';
                      $showBadge = false;
                      if ($key === $chosen) {
                        $optClass = $isCorrect ? 'bg-emerald-50 border-emerald-300' : 'bg-red-50 border-red-300';
                        $showBadge = true;
                      } else {
                        $optClass = 'bg-slate-50 border-slate-200';
                      }
                    @endphp
                    <div class="flex items-start gap-3 p-3 rounded-lg border {{ $optClass }}">
                      <div class="flex-shrink-0 w-6 h-6 rounded bg-white border border-slate-300 flex items-center justify-center text-sm font-bold">{{ $key }}</div>
                      <div class="flex-1 text-gray-700 text-sm">{{ $text }}</div>
                      @if($showBadge)
                        <span class="flex-shrink-0 px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">Jawaban Anda</span>
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>

            {{-- Fill in the Blank --}}
            @else
              @php
                $ansRows   = $answers->where('question_id', $q->id)->keyBy('blank_index');
                $paragraph = $q->paragraph_text ?? '';

                $pos = -1;
                $rendered = preg_replace_callback(
                  '/\[\[(\d+)\]\]|\[blank\]/',
                  function($m) use (&$pos, $ansRows) {
                    $pos++;
                    $row   = $ansRows->get((string)$pos);
                    $val   = trim((string)($row->answer ?? ''));
                    $ok    = (bool)($row->is_correct ?? false);
                    $label = $val !== '' ? e($val) : '<em class="text-slate-400">—</em>';
                    $cls   = $ok ? 'bg-emerald-50 border-emerald-300 text-emerald-700' : 'bg-red-50 border-red-300 text-red-700';
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded border '.$cls.' font-medium text-sm mx-0.5">'.$label.'</span>';
                  },
                  e($paragraph)
                );

                $rendered = str_replace(['&lt;span','&lt;/span&gt;','&lt;em','&lt;/em&gt;'], ['<span','</span>','<em','</em>'], $rendered);

                $allCorrect = $ansRows->where('is_correct', false)->isEmpty();
              @endphp

              <div class="bg-white rounded-lg border-l-4 {{ $allCorrect ? 'border-emerald-500' : 'border-red-500' }} border-t border-r border-b border-slate-200 p-5 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-start gap-3 mb-4">
                  <!-- <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center font-bold text-slate-700">
                    {{ $idx + 1 }}
                  </div> -->
                  <div class="flex-1">
                    <div class="font-semibold text-gray-900 mb-1">Fill in the Blank</div>
                    <div class="text-gray-700 leading-relaxed whitespace-pre-line">{!! $rendered !!}</div>
                  </div>
                  @if($allCorrect)
                    <span class="flex-shrink-0 px-2 py-1 rounded bg-emerald-100 text-emerald-700 text-xs font-semibold">✓ Benar</span>
                  @else
                    <span class="flex-shrink-0 px-2 py-1 rounded bg-red-100 text-red-700 text-xs font-semibold">✗ Ada Salah</span>
                  @endif
                </div>
              </div>
            @endif
          @endforeach
        </div>
      </div>

      {{-- Clear localStorage --}}
      <script>
        (function(){
          const prefix = 'BL_FIB_ATTEMPT_{{ (int) $attempt->id }}_Q';
          for (let i = 0; i < localStorage.length; i++) {
            const k = localStorage.key(i);
            if (k && k.startsWith(prefix)) { localStorage.removeItem(k); i--; }
          }
        })();
      </script>

    @else
      {{-- Belum Submit State --}}
      @php
        $isFib = $attempt->quiz
            ? $attempt->quiz->questions()->where('type', 'fib_paragraph')->exists()
            : false;
      @endphp

      <div class="bg-white rounded-lg shadow-sm border border-slate-200 p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Dikumpulkan</h3>
        <p class="text-slate-600 mb-6">
          Attempt ini belum disubmit. Anda dapat melanjutkan pengerjaan quiz dari posisi terakhir.
        </p>
        <a href="{{ $isFib ? route('bl.quiz', $attempt->quiz_id) : route('bl.quiz.show', $attempt->id) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
          </svg>
          Lanjutkan Quiz
        </a>
      </div>
    @endif

    {{-- Back Button --}}
    <div class="flex justify-center mt-8">
      <a href="{{ route('bl.history') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-300 rounded-lg font-medium text-gray-700 hover:bg-slate-50 transition-colors shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Riwayat
      </a>
    </div>
  </div>
</div>

@endsection