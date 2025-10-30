{{-- resources/views/bl/survey_start.blade.php --}}
@extends('layouts.front')
@section('title','Mulai Kuesioner Basic Listening')

@push('styles')
<style>
  .hero-gradient{
    background: radial-gradient(1200px 600px at -10% -10%, rgba(255,255,255,.15) 0, transparent 60%),
                radial-gradient(1000px 600px at 110% -10%, rgba(255,255,255,.15) 0, transparent 60%),
                linear-gradient(135deg, #4f46e5 0%, #1e40af 100%);
  }
</style>
@endpush

@section('content')
  {{-- HERO --}}
  <div class="hero-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 py-10 md:py-14">
      <h1 class="text-3xl md:text-4xl font-bold tracking-tight">Pilih Tutor & Supervisor</h1>
      <p class="mt-2 text-blue-100">Langkah awal sebelum mengisi rangkaian kuesioner (Tutor → Supervisor → Lembaga).</p>
    </div>
  </div>

  {{-- MAIN --}}
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
    @if(session('danger'))
      <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3">
        {{ session('danger') }}
      </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">
      <div class="px-5 py-5 md:px-8 md:py-8 space-y-6">
        <div class="space-y-1">
          <h2 class="text-xl font-semibold text-gray-900">Konfirmasi Pembimbing</h2>
          <p class="text-sm text-gray-600">
            Silakan pilih <strong>Tutor</strong> dan <strong>Supervisor</strong> yang sesuai.
            Pilihan ini akan tersimpan untuk kuesioner berikutnya.
          </p>
        </div>

        @php
          $hasTutors = isset($tutors) && $tutors->count() > 0;
          $hasSupervisors = isset($supervisors) && $supervisors->count() > 0;
        @endphp

        @if(!$hasTutors || !$hasSupervisors)
          <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3">
            @if(!$hasTutors && !$hasSupervisors)
              Data <strong>tutor</strong> dan <strong>supervisor</strong> belum tersedia. Hubungi admin.
            @elseif(!$hasTutors)
              Data <strong>tutor</strong> belum tersedia. Hubungi admin.
            @else
              Data <strong>supervisor</strong> belum tersedia. Hubungi admin.
            @endif
          </div>
        @endif

        <form method="POST" action="{{ route('bl.survey.start.submit') }}" class="space-y-6">
          @csrf

          {{-- TUTOR --}}
          <div>
            <label for="tutor_id" class="block text-sm font-medium text-gray-700 mb-1">Tutor</label>
            <select
              id="tutor_id"
              name="tutor_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              @disabled(!$hasTutors)
              required
            >
              <option value="">— Pilih tutor —</option>
              @foreach($tutors as $t)
                <option value="{{ $t->id }}" @selected(old('tutor_id') == $t->id)>{{ $t->name }}</option>
              @endforeach
            </select>
            @error('tutor_id')
              <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- SUPERVISOR --}}
          <div>
            <label for="supervisor_id" class="block text-sm font-medium text-gray-700 mb-1">Supervisor</label>
            <select
              id="supervisor_id"
              name="supervisor_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              @disabled(!$hasSupervisors)
              required
            >
              <option value="">— Pilih supervisor —</option>
              @foreach($supervisors as $s)
                <option value="{{ $s->id }}" @selected(old('supervisor_id') == $s->id)>{{ $s->name }}</option>
              @endforeach
            </select>
            @error('supervisor_id')
              <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- ACTIONS --}}
          <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ url()->previous() ?: route('bl.history') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Batal
            </a>
            <button
              type="submit"
              class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
              @disabled(!$hasTutors || !$hasSupervisors)
            >
              Lanjut ke Kuesioner
            </button>
          </div>
        </form>

        {{-- Bantuan kecil --}}
        <p class="text-xs text-gray-500">
          Setelah menekan <em>Lanjut</em>, kamu akan diarahkan ke kuesioner pertama (Tutor), kemudian otomatis ke Supervisor dan Lembaga.
        </p>
      </div>
    </div>
  </div>
@endsection
