@extends('layouts.front')
@section('title','Ubah Pilihan Tutor & Supervisor')

@push('styles')
<style>
  .hero-gradient{
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  }
</style>
@endpush

@section('content')
  {{-- HERO --}}
  <div class="hero-gradient text-white">
    <div class="max-w-7xl mx-auto px-4 py-6 md:py-10">
      <div class="flex items-center gap-2 mb-3">
        <a href="{{ $returnUrl }}" class="text-white/80 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
        </a>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Ubah Pilihan Tutor & Supervisor</h1>
      </div>
      <p class="text-blue-100 text-sm md:text-base">Perbarui pilihan tutor dan supervisor untuk kuesioner Anda.</p>
    </div>
  </div>

  {{-- MAIN --}}
  <div class="max-w-3xl mx-auto px-4 py-6">

    {{-- Flash messages --}}
    @if(session('success'))
      <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2.5 text-sm">
        {{ session('success') }}
      </div>
    @endif
    @if(session('info'))
      <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-3 py-2.5 text-sm">
        {{ session('info') }}
      </div>
    @endif

    {{-- Current Selection Info --}}
    @if($currentTutor || $currentSupervisor)
      <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
        <h3 class="text-sm font-semibold text-blue-900 mb-3 flex items-center gap-2">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
          Pilihan Saat Ini
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          @if($currentTutor)
            <div class="bg-white rounded-lg p-3 border border-blue-100">
              <p class="text-xs text-blue-600 mb-1">Tutor</p>
              <p class="text-sm font-semibold text-blue-900">{{ $currentTutor->name }}</p>
            </div>
          @endif
          @if($currentSupervisor)
            <div class="bg-white rounded-lg p-3 border border-blue-100">
              <p class="text-xs text-blue-600 mb-1">Supervisor</p>
              <p class="text-sm font-semibold text-blue-900">{{ $currentSupervisor->name }}</p>
            </div>
          @endif
        </div>
      </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
      <div class="px-4 py-5 md:px-6 md:py-6 space-y-5">
        <div class="space-y-1">
          <h2 class="text-lg md:text-xl font-semibold text-gray-900">Pilih Pembimbing Baru</h2>
          <p class="text-xs md:text-sm text-gray-600">
            Pilihan baru akan diterapkan untuk semua kuesioner yang belum disubmit.
          </p>
        </div>

        @php
          $hasTutors = isset($tutors) && $tutors->count() > 0;
          $hasSupervisors = isset($supervisors) && $supervisors->count() > 0;
        @endphp

        @if(!$hasTutors || !$hasSupervisors)
          <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-3 py-2.5 text-sm">
            @if(!$hasTutors && !$hasSupervisors)
              Data <strong>tutor</strong> dan <strong>supervisor</strong> belum tersedia. Hubungi admin.
            @elseif(!$hasTutors)
              Data <strong>tutor</strong> belum tersedia. Hubungi admin.
            @else
              Data <strong>supervisor</strong> belum tersedia. Hubungi admin.
            @endif
          </div>
        @endif

        <form method="POST" action="{{ route('bl.survey.update-choice') }}" class="space-y-4">
          @csrf
          <input type="hidden" name="return_url" value="{{ $returnUrl }}">

          {{-- TUTOR --}}
          <div>
            <label for="tutor_id" class="block text-sm font-medium text-gray-700 mb-1.5">Tutor</label>
            <select
              id="tutor_id"
              name="tutor_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              @disabled(!$hasTutors)
              required
            >
              <option value="">— Pilih tutor —</option>
              @foreach($tutors as $t)
                <option value="{{ $t->id }}" @selected($currentTutorId == $t->id)>{{ $t->name }}</option>
              @endforeach
            </select>
            @error('tutor_id')
              <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- SUPERVISOR --}}
          <div>
            <label for="supervisor_id" class="block text-sm font-medium text-gray-700 mb-1.5">Supervisor</label>
            <select
              id="supervisor_id"
              name="supervisor_id"
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              @disabled(!$hasSupervisors)
              required
            >
              <option value="">— Pilih supervisor —</option>
              @foreach($supervisors as $s)
                <option value="{{ $s->id }}" @selected($currentSupervisorId == $s->id)>{{ $s->name }}</option>
              @endforeach
            </select>
            @error('supervisor_id')
              <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Warning --}}
          <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
            <div class="flex items-start gap-2">
              <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
              </svg>
              <div class="flex-1">
                <p class="text-xs font-medium text-amber-800 mb-0.5">Perhatian!</p>
                <p class="text-xs text-amber-700">
                  Mengubah pilihan ini akan memperbarui semua kuesioner yang belum Anda submit. 
                  Kuesioner yang sudah disubmit tidak akan berubah.
                </p>
              </div>
            </div>
          </div>

          {{-- ACTIONS --}}
          <div class="flex items-center justify-end gap-2.5 pt-3">
            <a href="{{ $returnUrl }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Batal
            </a>
            <button
              type="submit"
              class="inline-flex items-center rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
              @disabled(!$hasTutors || !$hasSupervisors)
            >
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection