{{-- resources/views/toefl/exam/structure.blade.php --}}
@extends('layouts.front')
@section('title', 'Structure Section - TOEFL')

@section('content')
<div class="min-h-screen bg-gray-100">
  
  {{-- Timer --}}
  @include('toefl.partials.timer')

  {{-- Header --}}
  <div class="bg-purple-600 text-white py-4 px-6">
    <div class="max-w-5xl mx-auto flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold">Section 2: Structure & Written Expression</h1>
        <p class="text-purple-200 text-sm">{{ $questions->count() }} pertanyaan</p>
      </div>
      <div class="text-right">
        <div class="text-sm text-purple-200">{{ $attempt->exam->name }}</div>
      </div>
    </div>
  </div>

  {{-- Questions --}}
  <div class="max-w-5xl mx-auto py-6 px-4">
    <form id="quiz-form" class="space-y-6">
      @csrf
      
      @foreach($questions as $question)
      <div class="bg-white rounded-xl shadow-md p-6" id="question-{{ $question->id }}">
        <div class="flex gap-4">
          <div class="flex-shrink-0">
            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-purple-100 text-purple-600 font-bold">
              {{ $question->question_number }}
            </span>
          </div>
          <div class="flex-1">
            <p class="text-gray-900 font-medium mb-4">{{ $question->question }}</p>
            
            <div class="space-y-2">
              @foreach(['A', 'B', 'C', 'D'] as $opt)
                @php $optKey = 'option_' . strtolower($opt); @endphp
                <label class="flex items-center gap-3 p-3 rounded-lg border-2 cursor-pointer transition-all hover:bg-gray-50 
                  {{ ($answers[$question->id] ?? '') === $opt ? 'border-purple-500 bg-purple-50' : 'border-gray-200' }}"
                  data-question="{{ $question->id }}" data-option="{{ $opt }}">
                  <input type="radio" name="answer_{{ $question->id }}" value="{{ $opt }}" 
                    class="w-4 h-4 text-purple-600" 
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

    {{-- Submit Section Button --}}
    <div class="mt-8 text-center">
      <form method="POST" action="{{ route('toefl.submit-section', $attempt) }}">
        @csrf
        <button type="submit" class="px-8 py-4 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-xl shadow-lg transition-colors">
          Lanjut ke Section Berikutnya →
        </button>
      </form>
      <p class="mt-2 text-sm text-gray-500">Atau tunggu hingga waktu habis untuk otomatis lanjut</p>
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
      label.classList.remove('border-purple-500', 'bg-purple-50');
      label.classList.add('border-gray-200');
    });
    const selected = document.querySelector(`[data-question="${questionId}"][data-option="${answer}"]`);
    if (selected) {
      selected.classList.remove('border-gray-200');
      selected.classList.add('border-purple-500', 'bg-purple-50');
    }
  } catch (e) {
    console.error('Save failed:', e);
  }
}
</script>
@endsection
