{{-- resources/views/ept/schedule.blade.php --}}
@extends('layouts.front')
@section('title', 'Jadwal EPT')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('ept.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-800 mb-4">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard EPT
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Jadwal Sesi EPT</h1>
        <p class="text-slate-600 mt-1">Pilih sesi yang tersedia untuk mengikuti ujian EPT.</p>
    </div>

    {{-- Sessions List --}}
    <div class="space-y-4">
        @forelse($sessions as $session)
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-slate-800">{{ $session->name }}</h3>
                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $session->mode === 'online' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($session->mode) }}
                                </span>
                                @if($session->is_active)
                                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700">Aktif</span>
                                @endif
                            </div>
                            
                            <div class="flex flex-wrap gap-4 text-sm text-slate-600">
                                <div class="flex items-center gap-1">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $session->date->translatedFormat('l, d F Y') }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $session->start_time }} - {{ $session->end_time }}
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fa-solid fa-users"></i>
                                    {{ $session->registrations_count }}/{{ $session->max_participants }} peserta
                                </div>
                            </div>
                            
                            @if($session->quiz)
                                <div class="mt-2 text-xs text-slate-500">
                                    <i class="fa-solid fa-file-lines mr-1"></i>
                                    Paket: {{ $session->quiz->name }}
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            @if($session->isFull())
                                <span class="px-4 py-2 bg-slate-100 text-slate-500 rounded-lg text-sm font-semibold">
                                    Penuh
                                </span>
                            @else
                                <a href="{{ route('dashboard.ept-registration.index') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700 transition-colors">
                                    Daftar
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                
                @if($session->isOnline() && $session->hasZoom())
                    <div class="px-6 py-3 bg-blue-50 border-t border-blue-100 flex items-center gap-2 text-sm text-blue-700">
                        <i class="fa-solid fa-video"></i>
                        <span>Proctoring via Zoom tersedia</span>
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-16 bg-slate-50 rounded-xl">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-regular fa-calendar-xmark text-slate-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700 mb-2">Tidak Ada Sesi Tersedia</h3>
                <p class="text-slate-500">Belum ada sesi EPT yang dijadwalkan. Silakan cek kembali nanti.</p>
            </div>
        @endforelse
    </div>
    
    {{-- Pagination --}}
    <div class="mt-6">
        {{ $sessions->links() }}
    </div>
</div>
@endsection
