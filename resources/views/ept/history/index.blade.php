{{-- resources/views/ept/history/index.blade.php --}}
@extends('layouts.front')
@section('title', 'Riwayat EPT')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('ept.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-800 mb-4">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard EPT
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Riwayat Ujian EPT</h1>
        <p class="text-slate-600 mt-1">Daftar ujian EPT yang telah Anda selesaikan.</p>
    </div>

    {{-- Attempts List --}}
    <div class="space-y-4">
        @forelse($attempts as $attempt)
            <a href="{{ route('ept.history.show', $attempt) }}" 
               class="block bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-800 mb-2">{{ $attempt->quiz?->name ?? 'EPT' }}</h3>
                            <div class="flex flex-wrap gap-4 text-sm text-slate-600">
                                <div class="flex items-center gap-1">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $attempt->submitted_at->translatedFormat('l, d F Y') }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $attempt->submitted_at->format('H:i') }}
                                </div>
                                @if($attempt->session)
                                    <div class="flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot"></i>
                                        {{ $attempt->session->name }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            {{-- Score Badge --}}
                            <div class="text-right">
                                <div class="text-sm text-slate-500 mb-1">Total Score</div>
                                <div class="text-3xl font-black {{ $attempt->total_score >= 500 ? 'text-emerald-600' : ($attempt->total_score >= 400 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ $attempt->total_score }}
                                </div>
                            </div>
                            
                            <i class="fa-solid fa-chevron-right text-slate-400"></i>
                        </div>
                    </div>
                </div>
                
                {{-- Section Scores Bar --}}
                <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex gap-6 text-sm">
                    <div>
                        <span class="text-slate-500">Listening:</span>
                        <span class="font-bold text-slate-700">{{ $attempt->scaled_listening }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Structure:</span>
                        <span class="font-bold text-slate-700">{{ $attempt->scaled_structure }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500">Reading:</span>
                        <span class="font-bold text-slate-700">{{ $attempt->scaled_reading }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="text-center py-16 bg-slate-50 rounded-xl">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-clipboard-list text-slate-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-2">Belum Ada Riwayat</h3>
                <p class="text-slate-500">Anda belum pernah menyelesaikan ujian EPT.</p>
            </div>
        @endforelse
    </div>
    
    {{-- Pagination --}}
    <div class="mt-6">
        {{ $attempts->links() }}
    </div>
</div>
@endsection
