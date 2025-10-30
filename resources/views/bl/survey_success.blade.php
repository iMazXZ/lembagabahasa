{{-- resources/views/bl/survey_success.blade.php --}}
@extends('layouts.front')
@section('title', 'Kuesioner Selesai - Basic Listening')

@push('styles')
<style>
  .celebration-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
  }
  
  .celebration-gradient::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
    background-size: 50px 50px;
    animation: backgroundScroll 20s linear infinite;
  }
  
  @keyframes backgroundScroll {
    0% { transform: translate(0, 0); }
    100% { transform: translate(50px, 50px); }
  }
  
  .confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    background: #fbbf24;
    animation: confetti-fall 3s linear infinite;
  }
  
  @keyframes confetti-fall {
    0% {
      transform: translateY(-100vh) rotate(0deg);
      opacity: 1;
    }
    100% {
      transform: translateY(100vh) rotate(360deg);
      opacity: 0;
    }
  }
  
  .confetti:nth-child(1) { left: 10%; animation-delay: 0s; background: #fbbf24; }
  .confetti:nth-child(2) { left: 20%; animation-delay: 0.2s; background: #ef4444; }
  .confetti:nth-child(3) { left: 30%; animation-delay: 0.4s; background: #10b981; }
  .confetti:nth-child(4) { left: 40%; animation-delay: 0.6s; background: #3b82f6; }
  .confetti:nth-child(5) { left: 50%; animation-delay: 0.8s; background: #8b5cf6; }
  .confetti:nth-child(6) { left: 60%; animation-delay: 1s; background: #ec4899; }
  .confetti:nth-child(7) { left: 70%; animation-delay: 1.2s; background: #06b6d4; }
  .confetti:nth-child(8) { left: 80%; animation-delay: 1.4s; background: #f59e0b; }
  .confetti:nth-child(9) { left: 90%; animation-delay: 1.6s; background: #14b8a6; }
  
  .check-animation {
    animation: checkPop 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  }
  
  @keyframes checkPop {
    0% {
      transform: scale(0) rotate(0deg);
      opacity: 0;
    }
    50% {
      transform: scale(1.2) rotate(180deg);
    }
    100% {
      transform: scale(1) rotate(360deg);
      opacity: 1;
    }
  }
  
  .fade-in-up {
    animation: fadeInUp 0.8s ease-out;
  }
  
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
  
  .pulse-glow {
    animation: pulseGlow 2s ease-in-out infinite;
  }
  
  @keyframes pulseGlow {
    0%, 100% {
      box-shadow: 0 0 20px rgba(139, 92, 246, 0.5);
    }
    50% {
      box-shadow: 0 0 40px rgba(139, 92, 246, 0.8);
    }
  }
</style>
@endpush

@section('content')
  {{-- HERO with Confetti --}}
  <div class="celebration-gradient text-white relative">
    {{-- Confetti elements --}}
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    <div class="confetti"></div>
    
    <div class="max-w-4xl mx-auto px-4 py-12 md:py-16 text-center relative z-10">
      {{-- Success Icon --}}
      <div class="mb-6 flex justify-center">
        <div class="check-animation w-20 h-20 md:w-24 md:h-24 rounded-full bg-white flex items-center justify-center shadow-2xl">
          <svg class="w-12 h-12 md:w-14 md:h-14 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
      </div>
      
      {{-- Main Message --}}
      <h1 class="text-3xl md:text-4xl font-bold mb-3 fade-in-up">
        🎉 Terima Kasih!
      </h1>
      <p class="text-lg md:text-xl text-white/90 mb-2 fade-in-up" style="animation-delay: 0.1s;">
        Semua Kuesioner Telah Berhasil Diisi
      </p>
      <p class="text-sm md:text-base text-white/80 max-w-2xl mx-auto fade-in-up" style="animation-delay: 0.2s;">
        Masukan Anda sangat berharga untuk meningkatkan kualitas pembelajaran Basic Listening di Universitas Muhammadiyah Metro
      </p>
    </div>
  </div>

  {{-- CONTENT --}}
  <div class="max-w-4xl mx-auto px-4 py-8 -mt-6">
    
    {{-- Summary Card --}}
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-6 fade-in-up" style="animation-delay: 0.3s;">
      <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-5 py-4 border-b border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
          <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
          </svg>
          Ringkasan Kuesioner
        </h2>
      </div>
      
      <div class="p-5 md:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          {{-- Total Completed --}}
          <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div>
                <p class="text-xs text-green-600 font-medium">Total Selesai</p>
                <p class="text-2xl font-bold text-green-700">{{ $completedCount ?? 0 }}</p>
              </div>
            </div>
          </div>
          
          {{-- Tutor Info --}}
          @if($tutor)
            <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl p-4 border border-violet-100">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-violet-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-violet-600 font-medium">Tutor</p>
                  <p class="text-sm font-bold text-violet-700 truncate">{{ $tutor->name }}</p>
                </div>
              </div>
            </div>
          @endif
          
          {{-- Supervisor Info --}}
          @if($supervisor)
            <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-xl p-4 border border-blue-100">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                  <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-blue-600 font-medium">Supervisor</p>
                  <p class="text-sm font-bold text-blue-700 truncate">{{ $supervisor->name }}</p>
                </div>
              </div>
            </div>
          @endif
        </div>
        
        {{-- Timeline --}}
        <div class="space-y-3">
          <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Kuesioner yang Telah Diselesaikan
          </h3>
          
          <div class="space-y-2">
            <div class="flex items-center gap-3 bg-violet-50 rounded-lg p-3 border border-violet-100">
              <div class="w-8 h-8 rounded-full bg-violet-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-violet-900">📚 Evaluasi Tutor</p>
                <p class="text-xs text-violet-600">Penilaian terhadap metode pengajaran tutor</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3 bg-emerald-50 rounded-lg p-3 border border-emerald-100">
              <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-emerald-900">👥 Evaluasi Supervisor</p>
                <p class="text-xs text-emerald-600">Penilaian terhadap bimbingan supervisor</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3 bg-blue-50 rounded-lg p-3 border border-blue-100">
              <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-blue-900">🏛️ Evaluasi Lembaga</p>
                <p class="text-xs text-blue-600">Penilaian terhadap fasilitas dan pengelolaan</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Certificate Section --}}
    @if($canDownloadCertificate)
      <div class="bg-gradient-to-br from-amber-50 via-yellow-50 to-orange-50 rounded-2xl shadow-xl border-2 border-amber-200 p-6 md:p-8 mb-6 fade-in-up pulse-glow" style="animation-delay: 0.4s;">
        <div class="flex flex-col md:flex-row items-center gap-6">
          <div class="w-20 h-20 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center flex-shrink-0 shadow-lg">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
          </div>
          <div class="flex-1 text-center md:text-left">
            <h3 class="text-xl font-bold text-gray-900 mb-1">🎓 Sertifikat Tersedia!</h3>
            <p class="text-sm text-gray-700 mb-3">
              Selamat! Anda telah menyelesaikan semua persyaratan. Sertifikat Basic Listening Anda siap diunduh.
            </p>
            <div class="flex flex-col sm:flex-row gap-2 justify-center md:justify-start">
              <a href="{{ route('bl.certificate.download') }}?inline=1" target="_blank"
                 class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border-2 border-amber-300 text-amber-700 rounded-lg hover:bg-amber-50 transition-all font-semibold text-sm shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview
              </a>
              <a href="{{ route('bl.certificate.download') }}"
                 class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 text-white rounded-lg hover:from-amber-600 hover:to-orange-600 transition-all font-semibold text-sm shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download Sertifikat PDF
              </a>
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="bg-blue-50 rounded-xl border border-blue-200 p-5 mb-6 fade-in-up" style="animation-delay: 0.4s;">
        <div class="flex items-start gap-3">
          <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
          <div class="flex-1">
            <h3 class="text-sm font-semibold text-blue-900 mb-1">Sertifikat Belum Tersedia</h3>
            <p class="text-xs text-blue-700 leading-relaxed">
              Untuk mendapatkan sertifikat, pastikan Anda telah menyelesaikan semua pertemuan dan ujian akhir. Hubungi tutor jika ada pertanyaan.
            </p>
          </div>
        </div>
      </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3 fade-in-up" style="animation-delay: 0.5s;">
      <a href="{{ route('bl.history') }}"
         class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all font-semibold shadow-lg shadow-indigo-500/30">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Lihat Riwayat Skor
      </a>
      <a href="{{ route('bl.index') }}"
         class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-white border-2 border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-all font-semibold shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
        Kembali ke Dashboard
      </a>
    </div>

    {{-- Footer Note --}}
    <div class="mt-8 text-center fade-in-up" style="animation-delay: 0.6s;">
      <p class="text-xs text-gray-500 leading-relaxed max-w-2xl mx-auto">
        💡 Masukan Anda akan membantu kami meningkatkan kualitas program Basic Listening. 
        Jika ada pertanyaan atau kendala, silakan hubungi tim pengajar melalui koordinator program.
      </p>
    </div>

  </div>
@endsection