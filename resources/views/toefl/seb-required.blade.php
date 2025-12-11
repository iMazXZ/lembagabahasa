{{-- resources/views/toefl/seb-required.blade.php --}}
@extends('layouts.front')
@section('title', 'Safe Exam Browser Diperlukan')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 via-orange-50 to-yellow-50 flex items-center justify-center px-4 py-12">
  <div class="w-full max-w-lg text-center">
    
    {{-- Icon --}}
    <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full mb-6">
      <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
    </div>

    {{-- Title --}}
    <h1 class="text-3xl font-bold text-gray-900 mb-4">
      Safe Exam Browser Diperlukan
    </h1>

    {{-- Description --}}
    <p class="text-gray-600 mb-8 leading-relaxed">
      Untuk mengakses ujian TOEFL online, Anda harus menggunakan 
      <strong class="text-gray-900">Safe Exam Browser (SEB)</strong>.
      Browser biasa tidak diizinkan untuk menjaga integritas ujian.
    </p>

    {{-- Steps --}}
    <div class="bg-white rounded-2xl shadow-xl p-6 text-left mb-8">
      <h2 class="font-semibold text-gray-900 mb-4">Langkah-langkah:</h2>
      <ol class="space-y-3 text-sm text-gray-600">
        <li class="flex items-start gap-3">
          <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
          <span>Download dan install <a href="https://safeexambrowser.org/download_en.html" target="_blank" class="text-blue-600 underline hover:text-blue-800">Safe Exam Browser</a></span>
        </li>
        <li class="flex items-start gap-3">
          <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
          <span>Download file konfigurasi <strong>.seb</strong> dari panitia ujian</span>
        </li>
        <li class="flex items-start gap-3">
          <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
          <span>Buka file .seb dengan Safe Exam Browser</span>
        </li>
        <li class="flex items-start gap-3">
          <span class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">4</span>
          <span>Masukkan Connect Code dan mulai ujian</span>
        </li>
      </ol>
    </div>

    {{-- Back Button --}}
    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
      </svg>
      Kembali ke Beranda
    </a>

  </div>
</div>
@endsection
