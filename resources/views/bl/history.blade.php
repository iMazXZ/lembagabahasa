@extends('layouts.front')
@section('title', 'Riwayat Skor')

@section('content')
{{-- HERO Section --}}
<div class="bg-gradient-to-br from-blue-600 to-indigo-800 text-white">
  <div class="max-w-7xl mx-auto px-4 py-12 md:py-16">
    <div class="text-center mb-8">
      <!-- <a href="{{ route('bl.index') }}" class="inline-flex items-center gap-2 text-blue-100 hover:text-white text-sm mb-4 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Beranda
      </a> -->
      <h1 class="text-4xl md:text-5xl font-bold mb-3">Riwayat Skor</h1>
      <p class="text-blue-100 text-lg">Pantau perkembangan dan pencapaian belajar Anda</p>
    </div>

    @php
      $totalAttempts = $attempts->count();
      $completedAttempts = $attempts->whereNotNull('submitted_at')->count();
      $avgScore = $attempts->whereNotNull('score')->avg('score');
    @endphp

    {{-- Stats Cards --}}
    <div class="max-w-4xl mx-auto">
      <div class="grid grid-cols-3 gap-3 md:gap-4">
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 text-center">
          <div class="text-blue-200 text-xs font-medium mb-1">Total</div>
          <div class="text-3xl font-bold mb-0.5">{{ $totalAttempts }}</div>
          <div class="text-blue-200 text-xs">Pertemuan</div>
        </div>
        
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 text-center">
          <div class="text-blue-200 text-xs font-medium mb-1">Selesai</div>
          <div class="text-3xl font-bold mb-0.5">{{ $completedAttempts }}</div>
          <div class="text-blue-200 text-xs">Tersubmit</div>
        </div>
        
        <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20 text-center">
          <div class="text-blue-200 text-xs font-medium mb-1">Rata-rata</div>
          <div class="text-3xl font-bold mb-0.5">{{ $avgScore ? number_format($avgScore, 1) : '0' }}</div>
          <div class="text-blue-200 text-xs">Skor</div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Content Section --}}
<div class="bg-gray-50 min-h-screen">
  <div class="max-w-4xl mx-auto px-4 py-8">
    {{-- Filter Tabs --}}
    <div class="mb-6">
      <div class="bg-white rounded-lg p-1 shadow-sm inline-flex gap-1">
        <button class="filter-tab active px-4 py-2 rounded-md text-sm font-medium transition-colors" data-filter="all">
          Semua
        </button>
        <button class="filter-tab px-4 py-2 rounded-md text-sm font-medium transition-colors" data-filter="completed">
          Selesai
        </button>
        <button class="filter-tab px-4 py-2 rounded-md text-sm font-medium transition-colors" data-filter="pending">
          Belum Submit
        </button>
        <button class="filter-tab px-4 py-2 rounded-md text-sm font-medium transition-colors" data-filter="uas">
          UAS
        </button>
      </div>
    </div>

    {{-- Attempts List --}}
    <div class="space-y-3">
      @forelse($attempts as $index => $attempt)
        @php
          $isUAS = (int)($attempt->session->number) > 5;
          $hasScore = !is_null($attempt->score);
        @endphp
        
        <a href="{{ route('bl.history.show', $attempt) }}"
           class="attempt-card block bg-white rounded-lg border border-slate-200 p-4 hover:border-blue-300 hover:shadow-md transition-all"
           data-type="{{ $isUAS ? 'uas' : 'regular' }}"
           data-status="{{ $hasScore ? 'completed' : 'pending' }}">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex-1">
              <div class="flex flex-wrap items-center gap-2 mb-2">
                @if($isUAS)
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-pink-100 text-pink-700 text-xs font-semibold">
                    Final Exam
                  </span>
                @else
                  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                    Meeting {{ $attempt->session->number }}
                  </span>
                @endif
                <span class="text-xs text-slate-500">
                  {{ $attempt->created_at->format('d M Y • H:i') }}
                </span>
              </div>
              <h3 class="font-semibold text-gray-900 text-base mb-1">{{ $attempt->session->title }}</h3>
              <div class="flex items-center gap-2 text-xs text-slate-500">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ $attempt->session->duration_minutes }} menit</span>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              @if($hasScore)
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200">
                  <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                  </svg>
                  <span class="font-bold text-emerald-700">{{ (int)$attempt->score }}</span>
                </div>
              @else
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200">
                  <svg class="w-4 h-4 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                  </svg>
                  <span class="font-semibold text-amber-700 text-sm">Belum Submit</span>
                </div>
              @endif
              
              <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </a>
      @empty
        <div class="text-center bg-white rounded-lg p-12 border border-slate-200">
          <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <h3 class="font-semibold text-gray-900 text-lg mb-2">Belum Ada Riwayat</h3>
          <p class="text-slate-600 mb-4">Mulai ikuti pertemuan untuk melihat riwayat skor Anda di sini.</p>
          <a href="{{ route('bl.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            Lihat Pertemuan
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </a>
        </div>
      @endforelse
    </div>
    {{-- Back Button --}}
    <div class="flex justify-center mt-8">
      <a href="{{ route('bl.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-300 rounded-lg font-medium text-gray-700 hover:bg-slate-50 transition-colors shadow-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke Basic Listening
      </a>
    </div>
  </div>
</div>

<style>
.filter-tab {
  color: #64748b;
}

.filter-tab.active {
  background: #3b82f6;
  color: white;
}

.filter-tab:hover:not(.active) {
  background: #f1f5f9;
  color: #334155;
}

.attempt-card[style*="display: none"] {
  display: none !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const tabs = document.querySelectorAll('.filter-tab');
  const cards = document.querySelectorAll('.attempt-card');
  
  tabs.forEach(tab => {
    tab.addEventListener('click', function() {
      const filter = this.dataset.filter;
      
      // Update active tab
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      
      // Filter cards
      cards.forEach(card => {
        const type = card.getAttribute('data-type');
        const status = card.getAttribute('data-status');
        
        let show = filter === 'all' ||
                  (filter === 'completed' && status === 'completed') ||
                  (filter === 'pending' && status === 'pending') ||
                  (filter === 'uas' && type === 'uas');
        
        if (show) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    });
  });
});
</script>

@endsection