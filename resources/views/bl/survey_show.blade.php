{{-- resources/views/bl/survey_show.blade.php --}}
@extends('layouts.front')
@section('title', $survey->title ?? 'Kuesioner Basic Listening')

@push('styles')
<style>
  .hero-gradient{
    background: radial-gradient(1200px 600px at -10% -10%, rgba(255,255,255,.15) 0, transparent 60%),
                radial-gradient(1000px 600px at 110% -10%, rgba(255,255,255,.15) 0, transparent 60%),
                linear-gradient(135deg, #4f46e5 0%, #1e40af 100%);
  }
  .likert-grid { display:grid; grid-template-columns: 1fr repeat(5, 52px); gap:.5rem; align-items:center; }
  @media (max-width: 640px) { .likert-grid { grid-template-columns: 1fr; } .likert-scale{display:flex; gap:.5rem} }
</style>
@endpush

@section('content')
  {{-- HERO --}}
  <div class="hero-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 py-8 md:py-12">
      <h1 class="text-2xl md:text-3xl font-bold tracking-tight">
        {{ $survey->title ?? 'Kuesioner Basic Listening' }}
      </h1>

      {{-- meta bar --}}
      <div class="mt-3 text-sm text-blue-100 flex items-center gap-3 flex-wrap">
        {{-- Badge kategori --}}
        @php
          $cat = $survey->category ?? 'institute';
          $catMap = [
            'tutor' => ['label'=>'Tutor','cls'=>'border-violet-300 bg-violet-50 text-violet-800'],
            'supervisor' => ['label'=>'Supervisor','cls'=>'border-emerald-300 bg-emerald-50 text-emerald-800'],
            'institute' => ['label'=>'Lembaga','cls'=>'border-blue-300 bg-blue-50 text-blue-800'],
          ];
          $badge = $catMap[$cat] ?? $catMap['institute'];
        @endphp
        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium {{ $badge['cls'] }} !text-[12px]">
          {{ $badge['label'] }}
        </span>

        @if(isset($response) && $response->tutor?->name)
          <span>• Tutor: <strong class="text-white/90">{{ $response->tutor->name }}</strong></span>
        @endif
        @if(isset($response) && $response->supervisor?->name)
          <span>• Supervisor: <strong class="text-white/90">{{ $response->supervisor->name }}</strong></span>
        @endif

        @if(method_exists($survey,'isOpen') && !$survey->isOpen())
          <span class="ml-auto inline-flex items-center rounded-md bg-rose-600/20 px-2 py-1 text-xs text-rose-100">Ditutup</span>
        @endif
      </div>
    </div>
  </div>

  {{-- CONTENT --}}
  <div class="max-w-3xl mx-auto px-4 py-8">
    {{-- Flash messages --}}
    @if(session('success'))
      <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3">
        {{ session('success') }}
      </div>
    @endif
    @if(session('info'))
      <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3">
        {{ session('info') }}
      </div>
    @endif
    @if($errors->any())
      <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3">
        Terdapat isian yang belum benar. Silakan cek kembali bidang yang ditandai.
      </div>
    @endif

    {{-- Card --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
      @if(!empty($survey->description))
        <div class="px-5 py-4 md:px-8 md:py-6 border-b border-gray-200">
          <p class="text-sm text-gray-700">{{ $survey->description }}</p>
        </div>
      @endif

      <form method="POST" action="{{ route('bl.survey.submit', $survey) }}" class="px-5 py-5 md:px-8 md:py-8 space-y-8">
        @csrf

        {{-- Hidden if target=session --}}
        @if(($survey->target ?? 'final') === 'session')
          <input type="hidden" name="session_id" value="{{ old('session_id', $response->session_id ?? $survey->session_id) }}">
        @endif

        @php
          $questions = $survey->questions ?? collect();
        @endphp

        @forelse($questions as $idx => $q)
          <div class="space-y-3">
            {{-- Label + Required --}}
            <div class="flex items-start gap-2">
              <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-md bg-gray-100 text-gray-700 text-xs font-semibold">{{ $loop->iteration }}</span>
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-900">
                  {!! nl2br(e($q->question)) !!}
                  @if($q->is_required)
                    <span class="text-rose-600" title="Wajib">*</span>
                  @endif
                </label>
                @if(!empty($q->hint))
                  <p class="text-xs text-gray-500 mt-0.5">{{ $q->hint }}</p>
                @endif
              </div>
            </div>

            {{-- Field --}}
            @php
              $name = "q.{$q->id}";
              $err  = $errors->first($name);
            @endphp

            @if($q->type === 'likert')
              {{-- Likert 1..5 --}}
              <div class="likert-grid">
                <div class="sm:hidden text-xs text-gray-500 mb-1">Skala 1 (Sangat Tidak Setuju) — 5 (Sangat Setuju)</div>
                <div class="hidden sm:block text-xs text-gray-500">Skala</div>
                <div class="likert-scale sm:flex sm:flex-col sm:items-center">
                  <label class="sr-only" for="q-{{ $q->id }}-1">1</label>
                  <input id="q-{{ $q->id }}-1" type="radio" name="q[{{ $q->id }}]" value="1"
                         class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                         @checked(old("q.{$q->id}") == '1')>
                  <div class="hidden sm:block text-[11px] text-gray-500 mt-1">1</div>
                </div>
                <div class="likert-scale sm:flex sm:flex-col sm:items-center">
                  <label class="sr-only" for="q-{{ $q->id }}-2">2</label>
                  <input id="q-{{ $q->id }}-2" type="radio" name="q[{{ $q->id }}]" value="2"
                         class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                         @checked(old("q.{$q->id}") == '2')>
                  <div class="hidden sm:block text-[11px] text-gray-500 mt-1">2</div>
                </div>
                <div class="likert-scale sm:flex sm:flex-col sm:items-center">
                  <label class="sr-only" for="q-{{ $q->id }}-3">3</label>
                  <input id="q-{{ $q->id }}-3" type="radio" name="q[{{ $q->id }}]" value="3"
                         class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                         @checked(old("q.{$q->id}") == '3')>
                  <div class="hidden sm:block text-[11px] text-gray-500 mt-1">3</div>
                </div>
                <div class="likert-scale sm:flex sm:flex-col sm:items-center">
                  <label class="sr-only" for="q-{{ $q->id }}-4">4</label>
                  <input id="q-{{ $q->id }}-4" type="radio" name="q[{{ $q->id }}]" value="4"
                         class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                         @checked(old("q.{$q->id}") == '4')>
                  <div class="hidden sm:block text-[11px] text-gray-500 mt-1">4</div>
                </div>
                <div class="likert-scale sm:flex sm:flex-col sm:items-center">
                  <label class="sr-only" for="q-{{ $q->id }}-5">5</label>
                  <input id="q-{{ $q->id }}-5" type="radio" name="q[{{ $q->id }}]" value="5"
                         class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                         @checked(old("q.{$q->id}") == '5')>
                  <div class="hidden sm:block text-[11px] text-gray-500 mt-1">5</div>
                </div>
              </div>
            @else
              {{-- Text --}}
              <textarea
                name="q[{{ $q->id }}]"
                rows="4"
                maxlength="2000"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Tulis jawabanmu di sini..."
              >{{ old("q.{$q->id}") }}</textarea>
            @endif

            {{-- Error --}}
            @if($err)
              <p class="text-sm text-rose-600">{{ $err }}</p>
            @endif

            <hr class="border-gray-100 mt-4">
          </div>
        @empty
          <div class="text-sm text-gray-600">Belum ada pertanyaan pada kuesioner ini.</div>
        @endforelse

        {{-- ACTIONS --}}
        <div class="flex items-center justify-end gap-3 pt-2">
          <a href="{{ route('bl.survey.required') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Nanti Saja
          </a>
          <button
            type="submit"
            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            Kirim Jawaban
          </button>
        </div>

        <p class="text-[12px] text-gray-500">
          Dengan menekan <em>Kirim Jawaban</em>, kamu menyetujui data evaluasi digunakan untuk peningkatan kualitas pembelajaran.
        </p>
      </form>
    </div>
  </div>

  @push('scripts')
  <script>
    // Auto-scroll ke field error pertama (UX kecil)
    document.addEventListener('DOMContentLoaded', () => {
      const err = document.querySelector('.text-rose-600');
      if (err) {
        err.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  </script>
  @endpush
@endsection
