{{-- resources/views/dashboard/translation/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Penerjemahan Abstrak')
@section('page-title', 'Layanan Penerjemahan')

@section('content')
<div class="space-y-6">

    {{-- Header & Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Penerjemahan Abstrak</h1>
            <p class="mt-1 text-sm text-slate-500">
                Daftar riwayat pengajuan penerjemahan dokumen abstrak Anda.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>

            @if($canCreate)
                <a href="{{ route('dashboard.translation.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-um-blue text-white text-xs font-bold shadow-md hover:bg-um-dark-blue transition">
                    <i class="fa-solid fa-plus"></i>
                    <span>Ajukan Baru</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 flex items-start gap-3">
            <i class="fa-solid fa-circle-check text-emerald-600 mt-0.5"></i>
            <div class="text-sm text-emerald-800">{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-lg bg-red-50 border border-red-200 p-4 flex items-start gap-3">
            <i class="fa-solid fa-circle-xmark text-red-600 mt-0.5"></i>
            <div class="text-sm text-red-800">{{ session('error') }}</div>
        </div>
    @endif

    {{-- Info Banner (Dynamic State) --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 flex items-start gap-4">
            @if (! $biodataComplete)
                <div class="shrink-0 w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i class="fa-solid fa-user-exclamation text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Biodata Belum Lengkap</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Silakan lengkapi <strong>Prodi, NPM, dan Tahun Angkatan</strong> terlebih dahulu.
                        Untuk angkatan <strong>2024 ke bawah</strong>, wajib mengisi nilai <strong>Basic Listening</strong> manual.
                    </p>
                </div>
            @elseif (! $completedBL)
                <div class="shrink-0 w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-headphones text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Syarat Basic Listening</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Anda belum menyelesaikan <strong>Basic Listening</strong>. Tombol pengajuan akan muncul otomatis setelah nilai Attendance & Final Test terdata.
                    </p>
                </div>
            @elseif ($records->isEmpty())
                <div class="shrink-0 w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
                    <i class="fa-regular fa-file-lines text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Siap Mengajukan</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Silakan ajukan dokumen abstrak Anda melalui tombol <strong>Ajukan Baru</strong> di kanan atas.
                    </p>
                </div>
            @else
                <div class="shrink-0 w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fa-solid fa-list-check text-lg"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Ringkasan Permohonan</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Pantau status permohonan penerjemahan Anda di bawah ini.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- List Riwayat (Card Style - No Table Scroll) --}}
    <div class="space-y-4">
        @if($records->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 border-dashed p-10 flex flex-col items-center justify-center text-center">
                <div class="w-14 h-14 rounded-full bg-slate-50 flex items-center justify-center mb-4">
                    <i class="fa-solid fa-inbox text-slate-300 text-2xl"></i>
                </div>
                <p class="text-sm font-semibold text-slate-900">Belum ada data pengajuan</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach($records as $row)
                    @php
                        $status = $row->status ?? '-';
                        // Helper warna status
                        $colorClass = match (true) {
                            $status === 'Menunggu' => 'bg-amber-100 text-amber-800 border-amber-200',
                            in_array($status, ['Diproses', 'Disetujui'], true) => 'bg-sky-100 text-sky-800 border-sky-200',
                            $status === 'Selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            str_contains($status, 'Tidak Valid') || str_starts_with($status, 'Ditolak') => 'bg-red-100 text-red-800 border-red-200',
                            default => 'bg-slate-100 text-slate-800 border-slate-200',
                        };

                        $canDownload = in_array(strtolower($status), ['selesai', 'disetujui', 'completed', 'approved'], true)
                                    && (filled($row->translated_text) || filled($row->final_file_path));
                        $downloadUrl = route('penerjemahan.pdf', [$row, 'dl' => 1]);
                    @endphp

                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:border-blue-300 transition-all group">
                        {{-- Header Kartu --}}
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                            <div class="flex items-start gap-3">
                                <div class="h-10 w-10 rounded-full bg-slate-50 flex items-center justify-center text-slate-500 shrink-0">
                                    <i class="fa-solid fa-language"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-0.5">Tanggal Pengajuan</p>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ optional($row->submission_date ?? $row->created_at)->translatedFormat('d F Y, H:i') }}
                                    </p>
                                </div>
                            </div>
                            <span class="self-start px-3 py-1 rounded-full text-xs font-bold border {{ $colorClass }}">
                                {{ $status }}
                            </span>
                        </div>

                        {{-- Content Detail --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 text-sm">
                             <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <span class="text-xs text-slate-400 block mb-1">Estimasi / Selesai</span>
                                <span class="font-medium text-slate-700">
                                     {{ $row->completion_date ? $row->completion_date->translatedFormat('d F Y') : '-' }}
                                </span>
                            </div>
                             <div class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                                <span class="text-xs text-slate-400 block mb-1">Jenis Layanan</span>
                                <span class="font-medium text-slate-700">Terjemahan Abstrak</span>
                            </div>
                        </div>

                        {{-- Footer Action --}}
                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-slate-100">
                            @if(str_starts_with($status, 'Ditolak'))
                                <a href="{{ route('dashboard.translation.edit', $row) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-50 text-amber-700 text-xs font-bold hover:bg-amber-100 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    Perbaiki Data
                                </a>
                            @endif

                            @if($canDownload)
                                <a href="{{ $downloadUrl }}" target="_blank"
                                   class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-sm transition">
                                    <i class="fa-solid fa-file-arrow-down"></i>
                                    Download Hasil PDF
                                </a>
                            @endif

                            @if(!$canDownload && !str_starts_with($status, 'Ditolak'))
                                <span class="text-xs text-slate-400 italic px-2">
                                    Menunggu proses...
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection