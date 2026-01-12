{{-- resources/views/ept/index.blade.php --}}
@extends('layouts.front')
@section('title', 'EPT - English Proficiency Test')

@section('content')
@php
    $user = auth()->user();
@endphp

{{-- HERO SECTION --}}
<div class="relative bg-slate-900 overflow-hidden pb-24">
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 to-teal-900 opacity-90"></div>
        <div class="absolute inset-0" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 30px 30px; opacity: 0.1;"></div>
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-emerald-400 rounded-full blur-3xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-teal-400 rounded-full blur-3xl opacity-20"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 pt-10 pb-10 md:pt-16">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-emerald-100 text-xs font-medium mb-4 backdrop-blur-md">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Computer Based Test
                </div>
                <h1 class="text-3xl md:text-5xl font-black text-white tracking-tight mb-3 leading-tight">
                    English Proficiency <br/> <span class="text-emerald-300">Test (EPT)</span>
                </h1>
                <p class="text-emerald-100/80 text-base md:text-lg max-w-xl leading-relaxed">
                    Tes kemampuan bahasa Inggris berbasis komputer dengan format TOEFL ITP.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- DASHBOARD GRID --}}
<div class="max-w-7xl mx-auto px-4 -mt-20 relative z-10 mb-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        @auth
            {{-- CARD 1: REGISTRASI & TOKEN --}}
            <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-100 bg-gradient-to-b from-slate-50/50 to-white">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-emerald-500"></i> Status Pendaftaran
                    </h3>
                </div>

                <div class="p-5 flex-1">
                    @if($registration)
                        <div class="space-y-4">
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                                <div class="flex items-center gap-2 mb-2 text-emerald-800">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span class="text-xs font-bold uppercase">Terdaftar</span>
                                </div>
                                <p class="text-sm font-semibold text-emerald-900">
                                    {{ $registration->session?->name ?? 'Belum ada sesi' }}
                                </p>
                                <p class="text-xs text-emerald-700 mt-1">
                                    {{ $registration->session?->date?->translatedFormat('l, d F Y') ?? '-' }}
                                </p>
                            </div>
                            
                            <a href="{{ route('ept.token') }}" class="flex items-center justify-center gap-2 w-full py-3 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-200">
                                <i class="fa-solid fa-key"></i> Lihat Token CBT
                            </a>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-user-plus text-slate-300 text-2xl"></i>
                            </div>
                            <p class="text-sm text-slate-600 mb-4">Anda belum terdaftar EPT.</p>
                            <a href="{{ route('dashboard.ept-registration.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700">
                                <i class="fa-solid fa-plus"></i> Daftar Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- CARD 2: SESI MENDATANG --}}
            <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden flex flex-col">
                <div class="p-5 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-regular fa-calendar"></i> Sesi Mendatang
                    </h3>
                    <a href="{{ route('ept.schedule') }}" class="text-[10px] font-semibold text-emerald-600 hover:text-emerald-800">Lihat Semua</a>
                </div>

                <div class="p-5 flex-1 overflow-y-auto max-h-[250px] space-y-3">
                    @forelse($upcomingSessions as $session)
                        <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-sm font-bold text-slate-800">{{ $session->name }}</h4>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $session->mode === 'online' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($session->mode) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600">
                                <i class="fa-regular fa-calendar mr-1"></i>
                                {{ $session->date->translatedFormat('d M Y') }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                <i class="fa-regular fa-clock mr-1"></i>
                                {{ $session->start_time }} - {{ $session->end_time }}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400">
                            <i class="fa-regular fa-calendar-xmark text-3xl mb-2"></i>
                            <p class="text-sm">Tidak ada sesi mendatang.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- CARD 3: QUICK ACTIONS --}}
            <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl shadow-xl shadow-emerald-900/20 overflow-hidden flex flex-col text-white p-6 relative">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                
                <h3 class="text-sm font-bold mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-bolt"></i> Quick Actions
                </h3>
                
                <div class="space-y-3 flex-1">
                    <a href="{{ route('ept.diagnostic') }}" class="flex items-center gap-3 p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors backdrop-blur-sm">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold">Alat Diagnosa</div>
                            <div class="text-xs text-emerald-200">Cek kesiapan sistem</div>
                        </div>
                    </a>
                    
                    <a href="{{ route('ept.schedule') }}" class="flex items-center gap-3 p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors backdrop-blur-sm">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold">Jadwal EPT</div>
                            <div class="text-xs text-emerald-200">Lihat semua sesi</div>
                        </div>
                    </a>
                    
                    <a href="{{ route('bl.history') }}" class="flex items-center gap-3 p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors backdrop-blur-sm">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <div class="text-sm font-bold">Riwayat Ujian</div>
                            <div class="text-xs text-emerald-200">Lihat hasil sebelumnya</div>
                        </div>
                    </a>
                </div>
            </div>
        @endauth

        @guest
            <div class="lg:col-span-3 bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8 text-center">
                <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 mb-4">
                    <i class="fa-solid fa-lock text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-slate-900">Akses Terbatas</h2>
                <p class="text-slate-500 mt-2 mb-6 max-w-md mx-auto">Silakan login untuk mengakses pendaftaran dan ujian EPT.</p>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition-all shadow-md">
                    Login Sekarang
                </a>
            </div>
        @endguest

    </div>
</div>

{{-- INFO SECTION --}}
<div class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 mb-4">
                <i class="fa-solid fa-headphones text-xl"></i>
            </div>
            <h3 class="font-bold text-slate-800 mb-2">Listening Comprehension</h3>
            <p class="text-sm text-slate-600">50 soal • 35 menit</p>
        </div>
        
        <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 mb-4">
                <i class="fa-solid fa-spell-check text-xl"></i>
            </div>
            <h3 class="font-bold text-slate-800 mb-2">Structure & Written Expression</h3>
            <p class="text-sm text-slate-600">40 soal • 25 menit</p>
        </div>
        
        <div class="bg-white rounded-xl border border-slate-100 p-6 shadow-sm">
            <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 mb-4">
                <i class="fa-solid fa-book-open text-xl"></i>
            </div>
            <h3 class="font-bold text-slate-800 mb-2">Reading Comprehension</h3>
            <p class="text-sm text-slate-600">50 soal • 55 menit</p>
        </div>
    </div>
</div>

@endsection
