{{-- resources/views/toefl/exam/reading.blade.php --}}
@extends('layouts.front')
@section('title', 'Reading Section - TOEFL')

@section('content')
<div class="min-h-screen bg-gray-100">
  
  {{-- Timer --}}
  @include('toefl.partials.timer')

  {{-- Header --}}
  <div class="bg-orange-600 text-white py-4 px-6">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold">Section 3: Reading Comprehension</h1>
        <p class="text-orange-200 text-sm">{{ $questions->count() }} pertanyaan</p>
      </div>
      <div class="text-right">
        <div class="text-sm text-orange-200">{{ $attempt->exam->name }}</div>
      </div>
    </div>
  </div>

  {{-- Questions --}}
  <div class="max-w-6xl mx-auto py-6 px-4">
    <form id="quiz-form" class="space-y-8">
      @csrf
      
      @php
        $currentPassage = null;
      @endphp
      
      @foreach($questions as $question)
        {{-- Show passage if different from previous --}}
        @if($question->passage && $question->passage !== $currentPassage)
          @php $currentPassage = $question->passage; @endphp
          <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-r-xl p-6 mb-4">
            <h3 class="font-bold text-yellow-800 mb-3">Reading Passage</h3>
            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed">
              {!! nl2br(e($question->passage)) !!}
            </div>
          </div>
        @endif
        
        <div class="bg-white rounded-xl shadow-md p-6" id="question-{{ $question->id }}">
          <div class="flex gap-4">
            <div class="flex-shrink-0">
              <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-orange-100 text-orange-600 font-bold">
                {{ $question->question_number }}
              </span>
            </div>
            <div class="flex-1">
              <p class="text-gray-900 font-medium mb-4">{{ $question->question }}</p>
              
              <div class="space-y-2">
                @foreach(['A', 'B', 'C', 'D'] as $opt)
                  @php $optKey = 'option_' . strtolower($opt); @endphp
                  <label class="flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all hover:bg-gray-50 
                    {{ ($answers[$question->id] ?? '') === $opt ? 'border-orange-500 bg-orange-50' : 'border-gray-200' }}"
                    data-question="{{ $question->id }}" data-option="{{ $opt }}">
                    <input type="radio" name="answer_{{ $question->id }}" value="{{ $opt }}" 
                      class="w-4 h-4 text-orange-600" 
                      {{ ($answers[$question->id] ?? '') === $opt ? 'checked' : '' }}
                      onchange="saveAnswer({{ $question->id }}, '{{ $opt }}')"
                    >
                    <span class="font-semibold text-gray-600">{{ $opt }}.</span>
                    <span class="text-gray-700">{{ $question->$optKey }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          </div>
        </div>
      @endforeach

    </form>

    {{-- Submit Final Button --}}
    <div class="mt-8 text-center">
      <form method="POST" action="{{ route('toefl.submit-section', $attempt) }}">
        @csrf
        <button type="submit" class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow-lg transition-colors">
          ✓ Selesaikan Ujian
        </button>
      </form>
      <p class="mt-2 text-sm text-gray-500">Pastikan semua jawaban sudah terisi</p>
    </div>
  </div>

</div>

<script>
const answerUrl = '{{ route("toefl.answer", $attempt) }}';
const csrfToken = '{{ csrf_token() }}';

async function saveAnswer(questionId, answer) {
  try {
    await fetch(answerUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        question_id: questionId,
        answer: answer,
      }),
    });
    
    // Visual feedback
    const labels = document.querySelectorAll(`[data-question="${questionId}"]`);
    labels.forEach(label => {
      label.classList.remove('border-orange-500', 'bg-orange-50');
      label.classList.add('border-gray-200');
    });
    const selected = document.querySelector(`[data-question="${questionId}"][data-option="${answer}"]`);
    if (selected) {
      selected.classList.remove('border-gray-200');
      selected.classList.add('border-orange-500', 'bg-orange-50');
    }
  } catch (e) {
    console.error('Save failed:', e);
  }
}
</script>
@endsection
