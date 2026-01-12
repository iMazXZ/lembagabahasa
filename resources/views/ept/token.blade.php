{{-- resources/views/ept/token.blade.php --}}
@extends('layouts.front')
@section('title', 'Token CBT EPT')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-10">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('ept.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-800 mb-4">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard EPT
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Token CBT Anda</h1>
        <p class="text-slate-600 mt-1">Token ini digunakan untuk login ke sistem ujian EPT.</p>
    </div>

    @if($registration)
        {{-- Session Info --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Informasi Sesi</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-600">Sesi</span>
                    <span class="font-semibold text-slate-800">{{ $registration->session?->name ?? 'Belum ditentukan' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Tanggal</span>
                    <span class="font-semibold text-slate-800">{{ $registration->session?->date?->translatedFormat('l, d F Y') ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-600">Waktu</span>
                    <span class="font-semibold text-slate-800">{{ $registration->session?->start_time ?? '-' }} - {{ $registration->session?->end_time ?? '-' }}</span>
                </div>
                @if($registration->session?->isOnline() && $registration->session?->zoom_link)
                    <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                        <span class="text-slate-600">Zoom</span>
                        <a href="{{ $registration->session->zoom_link }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">
                            <i class="fa-solid fa-video mr-1"></i> Buka Zoom
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Token Card --}}
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-8 text-white shadow-xl shadow-emerald-200">
            <div class="text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-sm">
                    <i class="fa-solid fa-key text-2xl"></i>
                </div>
                
                @if($registration->hasToken())
                    <p class="text-sm text-emerald-100 mb-2">Token CBT Anda</p>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 mb-4">
                        <p class="text-3xl font-mono font-black tracking-widest">
                            {{ $registration->cbt_token }}
                        </p>
                    </div>
                    <p class="text-xs text-emerald-200">
                        Dirilis pada: {{ $registration->token_released_at->translatedFormat('d M Y, H:i') }}
                    </p>
                @else
                    <p class="text-lg font-semibold mb-2">Token Belum Tersedia</p>
                    <p class="text-sm text-emerald-100 max-w-xs mx-auto">
                        Token CBT akan muncul setelah pengawas melakukan absensi pada hari ujian.
                    </p>
                    <div class="mt-6 flex items-center justify-center gap-2 text-emerald-200">
                        <i class="fa-solid fa-clock"></i>
                        <span class="text-sm">Menunggu pengawas...</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Instructions --}}
        <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-5">
            <h4 class="font-bold text-amber-800 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-circle-info"></i> Petunjuk
            </h4>
            <ul class="text-sm text-amber-700 space-y-2">
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-amber-500 mt-0.5"></i>
                    <span>Pastikan Anda hadir tepat waktu sesuai jadwal.</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-amber-500 mt-0.5"></i>
                    <span>Token akan aktif setelah pengawas melakukan absensi.</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-amber-500 mt-0.5"></i>
                    <span>Gunakan Safe Exam Browser (SEB) untuk mengakses ujian.</span>
                </li>
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-check text-amber-500 mt-0.5"></i>
                    <span>Jangan bagikan token Anda kepada siapapun.</span>
                </li>
            </ul>
        </div>
    @else
        <div class="text-center py-16 bg-slate-50 rounded-xl">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-user-xmark text-slate-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-700 mb-2">Belum Terdaftar</h3>
            <p class="text-slate-500 mb-6">Anda belum terdaftar untuk mengikuti EPT.</p>
            <a href="{{ route('dashboard.ept-registration.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-bold hover:bg-emerald-700">
                <i class="fa-solid fa-plus"></i> Daftar Sekarang
            </a>
        </div>
    @endif
</div>
@endsection
