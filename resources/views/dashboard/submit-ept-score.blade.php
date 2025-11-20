{{-- resources/views/dashboard/submit-ept-score.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Pengajuan Surat Rekomendasi')
@section('page-title', 'Surat Rekomendasi EPT')

@section('content')
<div class="space-y-6">

    {{-- Header & Actions (Disederhanakan) --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Pengajuan Surat Rekomendasi</h1>
            <p class="mt-1 text-sm text-slate-500 max-w-2xl">
                Ajukan surat rekomendasi EPT setelah memenuhi persyaratan Basic Listening dan melampirkan 3 bukti nilai tes.
            </p>
        </div>

        {{-- Hanya tombol Kembali --}}
        <div class="shrink-0">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
            </a>
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

    {{-- SECTION 1: Riwayat Pengajuan (Card Style - No Scroll) --}}
    <div class="space-y-4">
        <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2 px-1">
            <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
            Riwayat Pengajuan
        </h2>

        @if($submissions->isEmpty())
            {{-- State Kosong --}}
            <div class="bg-white rounded-xl border border-slate-200 border-dashed p-8 flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center mb-3">
                    <i class="fa-solid fa-inbox text-slate-300 text-xl"></i>
                </div>
                <p class="text-sm font-semibold text-slate-900">Belum ada riwayat</p>
                <p class="text-xs text-slate-500 mt-1">
                    Riwayat pengajuan Anda akan muncul di sini.
                </p>
            </div>
        @else
            {{-- List Layout (Pengganti Tabel) --}}
            <div class="grid grid-cols-1 gap-4">
                @foreach($submissions as $row)
                    @php
                        $statusConfig = match ($row->status) {
                            'pending'  => ['label' => 'Menunggu', 'class' => 'bg-amber-100 text-amber-800 border-amber-200'],
                            'approved' => ['label' => 'Disetujui', 'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                            'rejected' => ['label' => 'Ditolak', 'class' => 'bg-red-100 text-red-800 border-red-200'],
                            default    => ['label' => $row->status, 'class' => 'bg-slate-100 text-slate-800 border-slate-200'],
                        };
                    @endphp

                    {{-- Kartu Item --}}
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:border-blue-300 transition-colors">
                        
                        {{-- Baris Atas: Tanggal & Status --}}
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <p class="text-xs text-slate-500 font-medium uppercase tracking-wider mb-1">Tanggal Pengajuan</p>
                                <div class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $row->created_at->translatedFormat('d M Y, H:i') }}
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusConfig['class'] }}">
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>

                        {{-- Baris Tengah: Skor (Grid Mini) --}}
                        <div class="bg-slate-50 rounded-lg border border-slate-100 p-3 mb-4">
                            <p class="text-[10px] text-slate-400 text-center uppercase font-bold tracking-wider mb-2">Rincian Skor</p>
                            <div class="grid grid-cols-3 gap-2 text-center divide-x divide-slate-200">
                                <div>
                                    <div class="text-[10px] text-slate-500 mb-0.5">Tes 1</div>
                                    <div class="text-sm font-mono font-bold text-slate-800">{{ $row->nilai_tes_1 }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-500 mb-0.5">Tes 2</div>
                                    <div class="text-sm font-mono font-bold text-slate-800">{{ $row->nilai_tes_2 }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] text-slate-500 mb-0.5">Tes 3</div>
                                    <div class="text-sm font-mono font-bold text-slate-800">{{ $row->nilai_tes_3 }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Info Catatan --}}
                         @if($row->catatan_admin)
                            <div class="mb-4 text-xs text-slate-600 bg-yellow-50 border border-yellow-100 p-3 rounded-lg">
                                <span class="font-bold text-yellow-700 block mb-1"><i class="fa-regular fa-comment-dots"></i> Catatan Admin:</span> 
                                {{ $row->catatan_admin }}
                            </div>
                        @endif

                        {{-- Baris Bawah: Tombol Aksi (Hanya muncul jika Approved) --}}
                        @if($row->status === 'approved')
                            @php
                                $pdfUrlRow = filled($row->verification_code)
                                    ? route('verification.ept.pdf', ['code' => $row->verification_code, 'dl' => 1])
                                    : route('ept-submissions.pdf', [$row, 'dl' => 1]);

                                $verifyUrlRow = $row->verification_url
                                    ?: (filled($row->verification_code)
                                        ? route('verification.show', ['code' => $row->verification_code], true)
                                        : null);
                            @endphp

                            <div class="flex flex-col sm:flex-row gap-2 mt-2 pt-3 border-t border-slate-100">
                                {{-- Tombol Verifikasi (Jika ada linknya) --}}
                                @if($verifyUrlRow)
                                    <a href="{{ $verifyUrlRow }}" target="_blank"
                                       class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 hover:text-um-blue transition">
                                        <i class="fa-solid fa-certificate"></i> Cek Link Verifikasi
                                    </a>
                                @endif

                                {{-- Tombol Download (Primary) --}}
                                <a href="{{ $pdfUrlRow }}" target="_blank"
                                   class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-bold hover:bg-emerald-100 transition">
                                    <i class="fa-solid fa-download"></i> Download PDF
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- SECTION 2: Form Pengajuan --}}
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-slate-400"></i>
                Formulir Pengajuan Baru
            </h2>
        </div>

        <div class="p-6 sm:p-8">
            {{-- KONDISI 1: Biodata Belum Lengkap --}}
            @if(! $biodataComplete)
                <div class="flex flex-col items-center text-center py-8">
                    <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-full flex items-center justify-center mb-4 animate-bounce">
                        <i class="fa-solid fa-triangle-exclamation text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Biodata Belum Lengkap</h3>
                    <p class="text-slate-500 max-w-md mx-auto mt-2 mb-6 text-sm">
                        Sistem mendeteksi data diri Anda belum lengkap (Prodi, NPM, Tahun Angkatan).
                        <br>Khusus angkatan <strong>≤ 2024</strong> wajib mengisi nilai Basic Listening manual.
                    </p>
                    <a href="{{ route('dashboard.biodata') }}"
                       class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-semibold shadow-md transition">
                        <i class="fa-solid fa-user-pen"></i> Lengkapi Biodata
                    </a>
                </div>

            {{-- KONDISI 2: Angkatan Baru Belum BL --}}
            @elseif($year >= 2025 && ! $completedBL)
                <div class="flex flex-col items-center text-center py-8">
                    <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-headphones text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Wajib Basic Listening</h3>
                    <p class="text-slate-500 max-w-md mx-auto mt-2 mb-6 text-sm">
                        Mahasiswa angkatan 2025 ke atas wajib mengikuti program <strong>Basic Listening</strong> terlebih dahulu
                        sebelum mengajukan surat rekomendasi.
                    </p>
                    <a href="{{ route('bl.index') }}"
                       class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md transition">
                        <i class="fa-solid fa-arrow-right"></i> Daftar Basic Listening
                    </a>
                </div>

            {{-- KONDISI 3: Sedang Ada Pengajuan Aktif --}}
            @elseif($hasSubmissions)
                <div class="flex flex-col items-center text-center py-8">
                    <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mb-4">
                        <i class="fa-solid fa-file-circle-check text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Pengajuan Sedang Diproses</h3>
                    <p class="text-slate-500 max-w-md mx-auto mt-2 text-sm">
                        Anda sudah memiliki pengajuan yang berstatus <strong>Menunggu</strong> atau <strong>Disetujui</strong>.
                        Silakan tunggu proses selesai atau hubungi admin jika ada kesalahan.
                    </p>
                </div>

            {{-- KONDISI 4: FORMULIR READY --}}
            @else
                <form action="{{ route('dashboard.ept.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    {{-- Loop 3 Kartu Tes --}}
                    @foreach(range(1, 3) as $i)
                    <div class="relative rounded-xl border border-slate-200 p-5 hover:border-blue-300 hover:bg-blue-50/30 transition-colors">
                        {{-- Badge Nomor --}}
                        <div class="absolute -top-3 -left-3 w-8 h-8 rounded-full bg-um-blue text-white flex items-center justify-center font-bold text-sm shadow-sm border-2 border-white">
                            {{ $i }}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                            {{-- Label Header (Mobile Only / Visual) --}}
                            <div class="md:col-span-12 mb-1">
                                <h3 class="text-sm font-bold text-slate-800">Percobaan Tes Ke-{{ $i }}</h3>
                            </div>

                            {{-- Input Nilai --}}
                            <div class="md:col-span-3">
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                    Skor <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="nilai_tes_{{ $i }}" value="{{ old('nilai_tes_'.$i) }}"
                                       class="w-full rounded-lg border-slate-300 focus:border-um-blue focus:ring-um-blue sm:text-sm"
                                       min="0" max="677" placeholder="Contoh: 450" required>
                                @error('nilai_tes_'.$i) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Input Tanggal --}}
                            <div class="md:col-span-4">
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                    Tanggal Tes <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="tanggal_tes_{{ $i }}" value="{{ old('tanggal_tes_'.$i) }}"
                                       class="w-full rounded-lg border-slate-300 focus:border-um-blue focus:ring-um-blue sm:text-sm" required>
                                @error('tanggal_tes_'.$i) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Input File --}}
                            <div class="md:col-span-5">
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5">
                                    Bukti Screenshot <span class="text-red-500">*</span>
                                </label>
                                <input type="file" name="foto_path_{{ $i }}" accept="image/*"
                                       class="block w-full text-xs text-slate-500
                                              file:mr-3 file:py-2 file:px-3
                                              file:rounded-md file:border-0
                                              file:text-xs file:font-semibold
                                              file:bg-slate-100 file:text-slate-700
                                              hover:file:bg-slate-200 cursor-pointer border border-slate-200 rounded-lg">
                                <p class="mt-1 text-[10px] text-slate-400">Format JPG/PNG, Max 8MB.</p>
                                @error('foto_path_'.$i) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-8 py-3 rounded-full bg-um-blue hover:bg-um-dark-blue text-white font-bold text-sm shadow-lg shadow-blue-900/20 transition-all hover:scale-[1.02]">
                            <i class="fa-solid fa-paper-plane"></i>
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection