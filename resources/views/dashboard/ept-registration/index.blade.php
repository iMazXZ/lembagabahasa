{{-- resources/views/dashboard/ept-registration/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Pendaftaran EPT')
@section('page-title', 'Pendaftaran EPT')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Pendaftaran Tes EPT</h1>
            <p class="mt-1 text-sm text-slate-500">
                Daftarkan diri Anda untuk mengikuti tes EPT dengan mengunggah bukti pembayaran.
            </p>
        </div>
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Kembali ke Dashboard</span>
        </a>
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

    {{-- KONDISI 1: Belum daftar atau Ditolak --}}
    @if(!$registration || $registration->status === 'rejected')
        
        @if($registration && $registration->status === 'rejected')
            <div class="bg-red-50 rounded-xl border-2 border-red-200 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-red-100 text-red-500 rounded-full flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-xmark text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-red-800">Pendaftaran Ditolak</h3>
                        <p class="text-sm text-red-700 mt-1">
                            <strong>Alasan:</strong> {{ $registration->rejection_reason ?? 'Tidak ada keterangan.' }}
                        </p>
                        <p class="text-sm text-red-600 mt-2">Silakan unggah ulang bukti pembayaran yang valid.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-slate-400"></i>
                    Formulir Pendaftaran
                </h2>
            </div>
            <div class="p-6 sm:p-8">
                <form action="{{ route('dashboard.ept-registration.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                        <p class="text-sm text-blue-800">
                            <i class="fa-solid fa-info-circle text-blue-500 mr-2"></i>
                            Lakukan pembayaran terlebih dahulu, kemudian unggah bukti pembayaran di bawah ini.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">
                            Bukti Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="bukti_pembayaran" accept="image/*" required
                               class="block w-full text-sm text-slate-600
                                      file:mr-4 file:py-3 file:px-5
                                      file:rounded-lg file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-um-blue file:text-white
                                      hover:file:bg-um-dark-blue cursor-pointer border-2 border-slate-200 rounded-xl py-2 px-3 bg-white shadow-sm">
                        <p class="mt-2 text-xs text-slate-500">Format: JPG, PNG, WebP. Maksimal 8MB.</p>
                        @error('bukti_pembayaran') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="pt-4 border-t border-slate-100">
                        <button type="submit"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-full bg-um-blue hover:bg-um-dark-blue text-white font-bold text-sm shadow-lg shadow-blue-900/20 transition-all hover:scale-[1.02]">
                            <i class="fa-solid fa-paper-plane"></i>
                            Daftar EPT
                        </button>
                    </div>
                </form>
            </div>
        </div>

    {{-- KONDISI 2: Pending --}}
    @elseif($registration->status === 'pending')
        <div class="bg-amber-50 rounded-2xl border-2 border-amber-200 shadow-lg p-8 sm:p-12 flex flex-col items-center text-center">
            <div class="w-20 h-20 bg-amber-100 text-amber-500 rounded-full flex items-center justify-center mb-6">
                <i class="fa-solid fa-clock text-4xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900 mb-3">Menunggu Verifikasi</h2>
            <p class="text-slate-500 max-w-lg mx-auto mb-4 leading-relaxed">
                Pendaftaran Anda sedang dalam proses verifikasi oleh admin.
                <br>Anda akan menerima notifikasi WhatsApp setelah diverifikasi.
            </p>
            <p class="text-xs text-slate-400">Diajukan pada: {{ $registration->created_at->translatedFormat('d F Y, H:i') }}</p>
        </div>

    {{-- KONDISI 3: Approved --}}
    @elseif($registration->status === 'approved')
        @if(!$registration->hasSchedule())
            <div class="bg-emerald-50 rounded-2xl border-2 border-emerald-200 shadow-lg p-8 sm:p-12 flex flex-col items-center text-center">
                <div class="w-20 h-20 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mb-6">
                    <i class="fa-solid fa-check text-4xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-3">Pendaftaran Disetujui</h2>
                <p class="text-slate-500 max-w-lg mx-auto leading-relaxed">
                    Selamat! Pendaftaran Anda telah disetujui. Jadwal tes akan segera diinformasikan.
                </p>
            </div>
        @else
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 bg-emerald-50">
                    <h2 class="text-sm font-bold text-emerald-800 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-check text-emerald-600"></i>
                        Jadwal Tes EPT Anda
                    </h2>
                </div>
                <div class="p-6 space-y-4">
                    @foreach([1, 2, 3] as $i)
                        @php
                            $grup = $registration->{"grup_$i"};
                            $jadwal = $registration->{"jadwal_$i"};
                        @endphp
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-um-blue text-white rounded-xl flex items-center justify-center font-bold text-lg shrink-0">{{ $i }}</div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $grup }}</p>
                                    <p class="text-sm text-slate-500">
                                        <i class="fa-regular fa-calendar mr-1"></i>{{ $jadwal->translatedFormat('l, d F Y') }}
                                        <span class="mx-2">•</span>
                                        <i class="fa-regular fa-clock mr-1"></i>{{ $jadwal->format('H:i') }} WIB
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 mt-6">
                        <p class="text-sm text-blue-800">
                            <i class="fa-solid fa-location-dot text-blue-500 mr-2"></i>
                            <strong>Lokasi:</strong> Ruang Stanford
                        </p>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <a href="{{ route('dashboard.ept-registration.kartu') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-lg transition-all hover:scale-[1.02]">
                        <i class="fa-solid fa-download"></i>
                        Download Kartu Peserta
                    </a>
                </div>
            </div>
            <div class="bg-amber-50 rounded-xl border border-amber-200 p-5">
                <h3 class="font-bold text-amber-800 flex items-center gap-2 mb-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                    Informasi Penting
                </h3>
                <ul class="text-sm text-amber-700 space-y-2">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-print text-amber-500 mt-0.5"></i>
                        <span><strong>Print kartu peserta</strong> dan bawa saat ujian untuk verifikasi.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-id-card text-amber-500 mt-0.5"></i>
                        <span>Wajib membawa <strong>KTP / Kartu Mahasiswa</strong> sebagai identitas diri.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-clock text-amber-500 mt-0.5"></i>
                        <span>Hadir <strong>15 menit</strong> sebelum jadwal tes dimulai.</span>
                    </li>
                </ul>
            </div>
        @endif
    @endif
</div>
@endsection
