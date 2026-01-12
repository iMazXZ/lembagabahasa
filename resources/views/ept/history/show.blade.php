{{-- resources/views/ept/history/show.blade.php --}}
@extends('layouts.front')
@section('title', 'Hasil EPT')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('ept.history.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-800 mb-4">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Hasil Ujian EPT</h1>
        <p class="text-slate-600 mt-1">{{ $attempt->quiz?->name }} • {{ $attempt->submitted_at->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- Total Score Card --}}
    <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 mb-6 text-white shadow-xl">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="text-center md:text-left">
                <div class="text-sm text-slate-400 uppercase tracking-wider mb-1">Total Score</div>
                <div class="text-6xl font-black">{{ $attempt->total_score }}</div>
                <div class="text-lg text-slate-300 mt-2">{{ $interpretation }}</div>
            </div>
            
            <div class="flex flex-col gap-3">
                @if($attempt->total_score >= 400)
                    <a href="{{ route('ept.history.certificate', $attempt) }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 rounded-xl font-bold hover:bg-emerald-700 transition-colors">
                        <i class="fa-solid fa-download"></i> Download Sertifikat
                    </a>
                    <a href="{{ route('ept.history.certificate.preview', $attempt) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-slate-700 rounded-xl font-semibold hover:bg-slate-600 transition-colors text-center justify-center">
                        <i class="fa-solid fa-eye"></i> Preview
                    </a>
                @else
                    <div class="px-6 py-3 bg-slate-700/50 rounded-xl text-slate-400 text-center">
                        <i class="fa-solid fa-lock mr-2"></i> Sertifikat (min. 400)
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Section Scores --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        @foreach($stats as $section => $data)
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-slate-700 capitalize">{{ $section }}</h3>
                    <span class="text-2xl font-black {{ $data['scaled'] >= 50 ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $data['scaled'] }}
                    </span>
                </div>
                <div class="text-sm text-slate-500">
                    {{ $data['correct'] }} / {{ $data['total'] }} benar
                </div>
                <div class="mt-3 h-2 bg-slate-100 rounded-full overflow-hidden">
                    @php $percent = $data['total'] > 0 ? ($data['correct'] / $data['total']) * 100 : 0; @endphp
                    <div class="h-full rounded-full {{ $percent >= 70 ? 'bg-emerald-500' : ($percent >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" 
                         style="width: {{ $percent }}%"></div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Info Grid --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="font-bold text-slate-800">Informasi Ujian</h3>
        </div>
        <div class="p-6 space-y-4">
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-600">Paket Soal</span>
                <span class="font-semibold text-slate-800">{{ $attempt->quiz?->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-600">Sesi</span>
                <span class="font-semibold text-slate-800">{{ $attempt->session?->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-600">Waktu Mulai</span>
                <span class="font-semibold text-slate-800">{{ $attempt->started_at?->format('d M Y, H:i') ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-slate-100">
                <span class="text-slate-600">Waktu Selesai</span>
                <span class="font-semibold text-slate-800">{{ $attempt->submitted_at?->format('d M Y, H:i') ?? '-' }}</span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-slate-600">Durasi</span>
                <span class="font-semibold text-slate-800">
                    @if($attempt->started_at && $attempt->submitted_at)
                        {{ $attempt->started_at->diffInMinutes($attempt->submitted_at) }} menit
                    @else
                        -
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>
@endsection
