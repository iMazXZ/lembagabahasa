{{-- resources/views/toefl/connect-code.blade.php --}}
@extends('layouts.front')
@section('title', 'Masukkan Connect Code - TOEFL')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 flex items-center justify-center px-4 py-12">
  <div class="w-full max-w-md">
    
    {{-- Header Card --}}
    <div class="bg-white rounded-t-2xl shadow-xl p-6 border-b-4 border-emerald-600">
      <div class="text-center">
        {{-- Icon --}}
        <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
          <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>

        {{-- Title --}}
        <h1 class="text-2xl font-bold text-gray-900 mb-2">
          TOEFL Online Test
        </h1>

        {{-- Exam Badge --}}
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-full text-sm font-semibold shadow-md">
          {{ $exam->name }}
        </div>

        {{-- Duration Info --}}
        <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
          <div class="bg-blue-50 rounded-lg p-2">
            <div class="font-semibold text-blue-700">Listening</div>
            <div class="text-blue-600">{{ $exam->package->listening_duration }} menit</div>
          </div>
          <div class="bg-purple-50 rounded-lg p-2">
            <div class="font-semibold text-purple-700">Structure</div>
            <div class="text-purple-600">{{ $exam->package->structure_duration }} menit</div>
          </div>
          <div class="bg-orange-50 rounded-lg p-2">
            <div class="font-semibold text-orange-700">Reading</div>
            <div class="text-orange-600">{{ $exam->package->reading_duration }} menit</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-b-2xl shadow-xl p-8">
      
      {{-- Error Message --}}
      @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ $errors->first() }}</p>
          </div>
        </div>
      @endif

      {{-- Form --}}
      <form method="POST" action="{{ route('toefl.verify', $exam) }}" class="space-y-5">
        @csrf

        <div>
          <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
            Connect Code
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <input 
              type="text" 
              id="code"
              name="code" 
              maxlength="64" 
              required
              autofocus
              class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all text-gray-900 font-mono text-lg tracking-wider placeholder-gray-400 @error('code') border-red-500 @enderror"
              value="{{ old('code') }}"
              placeholder="Masukkan kode akses"
            />
          </div>
          <p class="mt-2 text-xs text-gray-500">
            Masukkan kode yang diberikan oleh pengawas ujian.
          </p>
        </div>

        <button 
          type="submit"
          class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-[1.02] active:scale-[0.98]"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          Mulai Ujian
        </button>
      </form>

      {{-- Warning --}}
      <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
          </svg>
          <div class="text-sm text-yellow-800">
            <p class="font-semibold mb-1">Perhatian!</p>
            <ul class="text-xs space-y-1">
              <li>• Pastikan koneksi internet stabil</li>
              <li>• Jangan tutup atau refresh browser</li>
              <li>• Timer akan berjalan otomatis per section</li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    {{-- Security Notice --}}
    <div class="mt-6 text-center">
      <div class="inline-flex items-center gap-2 text-xs text-gray-500">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        Diakses melalui Safe Exam Browser
      </div>
    </div>

  </div>
</div>
@endsection
