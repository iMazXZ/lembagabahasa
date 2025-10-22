@extends('layouts.front')
@section('title', 'Riwayat Skor')

@push('styles')
<style>
.history-hero {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    position: relative;
    overflow: hidden;
}

.stats-card {
    background: #fff;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    border: 1px solid #e5e7eb;
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-2px);
}

.stat-value {
    font-size: 2rem;
    font-weight: 800;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.attempt-card {
    background: #fff;
    border-radius: 12px;
    padding: 1.25rem;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    position: relative;
}

.attempt-card:hover {
    border-color: #8b5cf6;
    box-shadow: 0 8px 25px rgba(139,92,246,.1);
    transform: translateX(4px);
}

.session-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 999px;
    font-size: 0.875rem;
    font-weight: 600;
}

.session-regular {
    background: #dbeafe;
    color: #1e40af;
}

.session-uas {
    background: #fce7f3;
    color: #be185d;
}

.score-display {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 700;
}

.score-complete {
    background: #d1fae5;
    color: #065f46;
}

.score-pending {
    background: #fef3c7;
    color: #92400e;
}

.filter-tabs {
    display: flex;
    gap: 0.5rem;
    background: #fff;
    padding: 0.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
}

.filter-tab {
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.3s ease;
    color: #6b7280;
}

.filter-tab.active {
    background: #6366f1;
    color: #fff;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fadeInUp 0.4s ease-out forwards;
}
</style>
@endpush

@section('content')
<section class="history-hero py-12 md:py-16">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Riwayat Skor Saya</h1>
            <p class="text-white/90 text-lg max-w-2xl mx-auto">Pantau perkembangan dan pencapaian belajar Anda dalam setiap pertemuan</p>
        </div>

        @php
            $totalAttempts = $attempts->count();
            $completedAttempts = $attempts->whereNotNull('submitted_at')->count();
            $avgScore = $attempts->whereNotNull('score')->avg('score');
        @endphp

        <div class="max-w-2xl mx-auto">
            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20 shadow-xl">
                <div class="grid grid-cols-3 gap-2">
                    <div class="text-center">
                        <div class="text-white/80 text-xs font-semibold uppercase tracking-wider mb-1">Total</div>
                        <div class="text-2xl font-bold text-white mb-1">{{ $totalAttempts }}</div>
                        <div class="text-white/60 text-xs">Pertemuan</div>
                    </div>

                    <div class="text-center">
                        <div class="text-white/80 text-xs font-semibold uppercase tracking-wider mb-1">Selesai</div>
                        <div class="text-2xl font-bold text-white mb-1">{{ $completedAttempts }}</div>
                        <div class="text-white/60 text-xs">Tersubmit</div>
                    </div>

                    <div class="text-center">
                        <div class="text-white/80 text-xs font-semibold uppercase tracking-wider mb-1">Rata-rata</div>
                        <div class="text-2xl font-bold text-white mb-1">{{ $avgScore ? number_format($avgScore, 1) : '0' }}</div>
                        <div class="text-white/60 text-xs">Skor</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-8 bg-gray-50">
  <div class="max-w-4xl mx-auto px-4">
        <div class="mb-6">
            <div class="filter-tabs">
                <div class="filter-tab active" data-filter="all">Semua</div>
                <div class="filter-tab" data-filter="completed">Selesai</div>
                <div class="filter-tab" data-filter="pending">Belum Submit</div>
                <div class="filter-tab" data-filter="uas">UAS</div>
            </div>
        </div>

        <div class="space-y-4">
            @forelse($attempts as $index => $attempt)
                @php
                    $isUAS = (int)($attempt->session->number) > 5;
                    $hasScore = !is_null($attempt->score);
                @endphp
                <a href="{{ route('bl.history.show', $attempt) }}"
                   class="attempt-card block animate-fade-in"
                   style="animation-delay: {{ $index * 0.03 }}s"
                   data-type="{{ $isUAS ? 'uas' : 'regular' }}"
                   data-status="{{ $hasScore ? 'completed' : 'pending' }}">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="session-badge {{ $isUAS ? 'session-uas' : 'session-regular' }}">
                                    {{ $isUAS ? 'UAS' : 'Pertemuan '.$attempt->session->number }}
                                </span>
                                <span class="text-sm text-gray-500">
                                    {{ $attempt->created_at->format('d M Y • H:i') }} WIB
                                </span>
                            </div>
                            <h3 class="font-bold text-gray-900 text-lg">{{ $attempt->session->title }}</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($hasScore)
                                <div class="score-display score-complete">{{ (int)$attempt->score }}</div>
                            @else
                                <div class="score-display score-pending">Belum Submit</div>
                            @endif
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center bg-white rounded-xl p-8 border border-gray-200">
                    <div class="mx-auto w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Belum Ada Riwayat</h3>
                    <p class="text-gray-600">Mulai ikuti pertemuan untuk melihat riwayat skor Anda di sini.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

@push('scripts')
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
            cards.forEach((card, index) => {
                const type = card.getAttribute('data-type');
                const status = card.getAttribute('data-status');
                
                let show = filter === 'all' ||
                          (filter === 'completed' && status === 'completed') ||
                          (filter === 'pending' && status === 'pending') ||
                          (filter === 'uas' && type === 'uas');
                
                if (show) {
                    card.style.display = 'block';
                    card.style.animation = `fadeInUp 0.4s ease-out ${index * 0.03}s forwards`;
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endpush
@endsection