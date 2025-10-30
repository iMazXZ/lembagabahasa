{{-- resources/views/bl/survey_edit_choice.blade.php --}}
@extends('layouts.front')
@section('title','Ubah Pilihan Pembimbing')

@section('content')
  <div class="bg-gradient-to-br from-sky-600 to-indigo-700 text-white">
    <div class="max-w-5xl mx-auto px-4 py-8">
      <h1 class="text-2xl md:text-3xl font-bold">Ubah Pilihan Tutor & Supervisor</h1>
      <p class="text-blue-100 text-sm md:text-base mt-1">Saat disimpan, draft kuesioner yang belum terkirim akan ikut diperbarui.</p>
    </div>
  </div>

  <div class="max-w-3xl mx-auto px-4 py-6">
    @if(session('success'))
      <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2.5 text-sm">{{ session('success') }}</div>
    @endif

    @if($errors->any())
      <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-3 py-2.5 text-sm">
        <div class="font-semibold">Periksa kembali isian:</div>
        <ul class="list-disc list-inside mt-1">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
      <form method="POST" action="{{ route('bl.survey.update-choice') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="return_url" value="{{ $returnUrl }}">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Tutor</label>
          <select name="tutor_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
            <option value="">— Pilih Tutor —</option>
            @foreach($tutors as $t)
              <option value="{{ $t->id }}" @selected((int)$currentTutorId === (int)$t->id)>{{ $t->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Supervisor</label>
          <select name="supervisor_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" required>
            <option value="">— Pilih Supervisor —</option>
            @foreach($supervisors as $s)
              <option value="{{ $s->id }}" @selected((int)$currentSupervisorId === (int)$s->id)>{{ $s->name }}</option>
            @endforeach
          </select>
        </div>

        <div class="flex items-center justify-end gap-2.5">
          <a href="{{ $returnUrl }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3.5 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
          <button type="submit" class="inline-flex items-center rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Simpan</button>
        </div>
      </form>
    </div>
  </div>
@endsection
