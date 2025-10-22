@extends('layouts.front')
@section('title', 'Detail Hasil')

@push('styles')
<style>
.detail-hero {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    position: relative;
    overflow: hidden;
}

.stats-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    border: 1px solid #e5e7eb;
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.question-card {
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    position: relative;
}

.question-card:hover {
    border-color: #8b5cf6;
    box-shadow: 0 8px 25px rgba(139,92,246,.1);
}

.question-card.correct {
    border-left: 4px solid #10b981;
}

.question-card.incorrect {
    border-left: 4px solid #ef4444;
}

.session-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 999px;
    font-size: 0.875rem;
    font-weight: 600;
}

.session-regular {
    background: #dbeafe;
    color: #1e40af;
}

.session-uas {
    background: #fce7f3;
    color: #be185d;
}

.option-item {
    display: flex;
    gap: 0.75rem;
    padding: 0.75rem;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fafafa;
}

.option-item.correct {
    background: #d1fae5;
    border-color: #6ee7b7;
}

.option-item.incorrect {
    background: #fee2e2;
    border-color: #fca5a5;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-your {
    background: #dbeafe;
    color: #1e40af;
}

.badge-correct {
    background: #d1fae5;
    color: #065f46;
}

.fib-token {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin: 0 0.125rem;
    background: #fff;
}

.fib-token.ok {
    border-color: #6ee7b7;
    background: #ecfdf5;
}

.fib-token.no {
    border-color: #fca5a5;
    background: #fef2f2;
}

.fib-correct {
    font-size: 0.75rem;
    color: #065f46;
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    border-radius: 4px;
    padding: 0.125rem 0.375rem;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeInUp 0.4s ease-out forwards;
}
</style>
@endpush

@section('content')
<section class="detail-hero py-12 md:py-16">
    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-6">
            <a href="{{ route('bl.history') }}" class="inline-flex items-center gap-2 text-white/90 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span class="font-semibold">Kembali ke Riwayat</span>
            </a>
        </div>

        <div class="text-center">
            @php $isUAS = (int)$attempt->session->number > 5; @endphp
            
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full mb-4 {{ $isUAS ? 'bg-pink-500/20' : 'bg-blue-500/20' }} backdrop-blur-sm text-white font-semibold text-sm">
                {{ $isUAS ? 'UAS' : 'Pertemuan '.$attempt->session->number }}
            </div>

            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">{{ $attempt->session->title }}</h1>

            @if($attempt->submitted_at)
                <div class="flex items-center justify-center gap-2 text-white/90">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Dikumpulkan {{ $attempt->submitted_at->format('d M Y, H:i') }} WIB</span>
                </div>
            @else
                <div class="flex items-center justify-center gap-2 text-yellow-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-semibold">Belum Dikumpulkan</span>
                </div>
            @endif
        </div>
    </div>
</section>

<section class="py-8 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4">
        @if($attempt->submitted_at)
            @php
                $totalUnits = 0;
                $correctUnits = 0;

                foreach ($questions as $q) {
                    $type = $q->type ?? 'multiple_choice';

                    if ($type === 'fib_paragraph') {
                        $keys = (array) ($q->fib_answer_key ?? []);
                        $placeholders = (array) ($q->fib_placeholders ?? array_keys($keys));
                        $placeholders = array_values(array_unique(array_map('strval', $placeholders)));

                        $unitCount = max(1, count($placeholders));
                        $totalUnits += $unitCount;

                        $ansCorrect = $answers
                            ->where('question_id', $q->id)
                            ->whereIn('blank_index', $placeholders)
                            ->where('is_correct', true)
                            ->count();

                        $correctUnits += min($unitCount, $ansCorrect);
                    } else {
                        $totalUnits += 1;
                        $ans = $answers->firstWhere('question_id', $q->id);
                        if ($ans && $ans->is_correct) {
                            $correctUnits += 1;
                        }
                    }
                }

                $incorrectUnits = max(0, $totalUnits - $correctUnits);
                $accuracy = $totalUnits > 0 ? round(($correctUnits / $totalUnits) * 100, 1) : 0;
            @endphp

            <div class="bg-white rounded-xl p-6 shadow-lg mb-8 mt-2">
                <h2 class="text-xl font-bold text-gray-900 mb-4 text-center">Ringkasan Hasil</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="text-center bg-gray-50 border border-gray-200 rounded-lg p-3">
                        <div class="text-xl font-bold text-gray-900">{{ $totalUnits }}</div>
                        <div class="text-xs text-gray-600 mt-1">Total Unit</div>
                    </div>
                    <div class="text-center bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="text-xl font-bold text-green-600">{{ $correctUnits }}</div>
                        <div class="text-xs text-gray-700 mt-1">Benar</div>
                    </div>
                    <div class="text-center bg-red-50 border border-red-200 rounded-lg p-3">
                        <div class="text-xl font-bold text-red-600">{{ $incorrectUnits }}</div>
                        <div class="text-xs text-gray-700 mt-1">Salah</div>
                    </div>
                    <div class="text-center bg-purple-50 border border-purple-200 rounded-lg p-3">
                        <div class="text-xl font-bold text-purple-600">{{ $accuracy }}%</div>
                        <div class="text-xs text-gray-700 mt-1">Akurasi</div>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Pembahasan</h2>

                <div class="space-y-4">
                    @foreach($questions as $idx => $q)
                        @php $qType = $q->type ?? 'multiple_choice'; @endphp

                        @if($qType !== 'fib_paragraph')
                            @php
                                $ans = $answers->firstWhere('question_id', $q->id);
                                $chosen = $ans->answer ?? null;
                                $isCorrect = (bool)($ans->is_correct ?? false);
                            @endphp

                            <div class="question-card {{ $isCorrect ? 'correct' : 'incorrect' }} animate-fade-in" style="animation-delay: {{ $idx * 0.03 }}s">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 font-semibold text-gray-700">
                                        {{ $idx + 1 }}
                                    </div>
                                    <div class="flex-1 font-medium text-gray-900">{{ $q->question }}</div>
                                </div>
                                <div class="space-y-2 ml-11">
                                    @foreach(['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d] as $key => $text)
                                        @php
                                            $optClass = $key === $q->correct ? 'correct' : (($key === $chosen && !$isCorrect) ? 'incorrect' : '');
                                        @endphp
                                        <div class="option-item {{ $optClass }}">
                                            <div class="font-bold w-6 h-6 rounded bg-white border border-gray-300 flex items-center justify-center text-sm">{{ $key }}</div>
                                            <div class="flex-1 text-gray-700 text-sm">{{ $text }}</div>
                                            @if($key === $chosen)
                                                <span class="badge badge-your">Jawabanmu</span>
                                            @endif
                                            @if($key === $q->correct)
                                                <span class="badge badge-correct">Kunci</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        @else
                            @php
                                $ansRows = $answers->where('question_id', $q->id)->keyBy('blank_index');
                                $keys = (array)($q->fib_answer_key ?? []);
                                $paragraph = $q->paragraph_text ?? '';
                                $rendered = preg_replace_callback('/\[\[(\d+)\]\]/', function($m) use ($ansRows, $keys) {
                                    $i = (string)$m[1];
                                    $row = $ansRows->get($i);
                                    $val = trim((string)($row->answer ?? ''));
                                    $ok = (bool)($row->is_correct ?? false);
                                    $label = $val !== '' ? e($val) : '<em>—</em>';
                                    $html = '<span class="fib-token '.($ok?'ok':'no').'">'.$label;
                                    if (!$ok) {
                                        $k = $keys[$i] ?? null;
                                        if (is_array($k)) { $kShow = $k[0] ?? ''; }
                                        elseif (is_string($k)) { $kShow = $k; }
                                        elseif (is_object($k) && isset($k->regex)) { $kShow = $k->regex; }
                                        else { $kShow = ''; }
                                        if ($kShow !== '') $html .= ' <span class="fib-correct">'.e($kShow).'</span>';
                                    }
                                    $html .= '</span>';
                                    return $html;
                                }, e($paragraph));
                                $rendered = str_replace(['&lt;span','/span&gt;','&lt;em&gt;','&lt;/em&gt;'], ['<span','/span>','<em>','</em>'], $rendered);
                            @endphp

                            <div class="question-card {{ ($ansRows->where('is_correct', false)->isEmpty() ? 'correct' : 'incorrect') }} animate-fade-in" style="animation-delay: {{ $idx * 0.03 }}s">
                                <div class="flex items-start gap-3 mb-3">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 font-semibold text-gray-700">
                                        {{ $idx + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 mb-2">Fill in the Blank</div>
                                        <div class="text-gray-700 leading-relaxed">{!! $rendered !!}</div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

        @else
            @php
                $isFib = $attempt->quiz
                    ? $attempt->quiz->questions()->where('type', 'fib_paragraph')->exists()
                    : false;
            @endphp

            <div class="bg-white rounded-xl shadow p-8 text-center -mt-8">
                <div class="mx-auto w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Dikumpulkan</h3>
                <p class="text-gray-600 mb-6">
                    Attempt ini belum disubmit. Anda dapat melanjutkan pengerjaan quiz dari posisi terakhir.
                </p>
                <a href="{{ $isFib ? route('bl.quiz', $attempt->quiz_id) : route('bl.quiz.show', $attempt->id) }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    Lanjutkan Quiz
                </a>
            </div>
        @endif

        <div class="flex justify-center mt-8">
            <a href="{{ route('bl.history') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Riwayat
            </a>
        </div>
    </div>
</section>
@endsection