{{-- resources/views/dashboard/ept-registration/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Pendaftaran EPT')
@section('page-title', 'Pendaftaran EPT')

@section('content')
<div class="space-y-6" x-data="{ showModal: false, downloadUrl: '' }">

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
                <form action="{{ route('dashboard.ept-registration.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    {{-- Label Bukti Pembayaran --}}
                    <label class="block text-sm font-bold text-slate-800 mb-3">
                        Bukti Pembayaran <span class="text-red-500">*</span>
                    </label>

                    {{-- Info Box Biru --}}
                    <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 -mt-1">
                        <p class="text-sm text-blue-800 flex items-start gap-2">
                            <i class="fa-solid fa-info-circle text-blue-500 mt-0.5"></i>
                            <span>Lakukan pembayaran terlebih dahulu, kemudian unggah bukti pembayaran di bawah ini.</span>
                        </p>
                    </div>

                    {{-- Warning Box Kuning --}}
                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
                        <p class="text-sm font-semibold text-amber-800 mb-2 flex items-center gap-2">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                            Perhatian! Pastikan foto bukti pembayaran:
                        </p>
                        <ul class="text-sm text-amber-700 space-y-1 ml-6 list-disc">
                            <li>Pastikan foto jelas, <strong>tidak buram</strong> atau ada bayangan</li>
                            <li>NPM dan jumlah pembayaran harus <strong>terlihat dengan jelas</strong></li>
                            <li>Gunakan hasil scan (CamScanner/scanner dokumen) atau <strong>screenshot langsung dari aplikasi bank</strong></li>
                        </ul>
                    </div>

                    {{-- Tombol Mengerti --}}
                    <div id="understand-button-wrapper-ept" class="flex justify-center">
                        <button type="button" id="btn-understand-ept"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-um-blue text-white font-bold text-sm shadow-lg shadow-blue-900/20 hover:bg-um-dark-blue transition-all hover:scale-[1.02]">
                            <i class="fa-solid fa-circle-check"></i>
                            Mengerti dan Unggah Bukti
                        </button>
                    </div>

                    {{-- Upload Area (hidden initially) --}}
                    <div id="upload-wrapper-ept" class="hidden space-y-6">
                        <div>
                            <div class="relative group">
                                <div id="payment-dropzone-ept"
                                    class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center
                                            hover:border-um-blue hover:bg-blue-50/50 transition-colors
                                            flex flex-col items-center justify-center gap-2 cursor-pointer">
                                    
                                    <div class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-1
                                                text-slate-400 group-hover:text-um-blue group-hover:bg-blue-100 transition-colors">
                                        <i class="fa-solid fa-upload"></i>
                                    </div>

                                    <p class="text-sm text-slate-600">
                                        Klik atau seret file ke sini
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        JPG, PNG, WebP (Maks. 8MB)
                                    </p>

                                    {{-- Preview --}}
                                    <div id="payment-preview-wrapper-ept" class="mt-3 hidden">
                                        <div class="flex items-center gap-3 justify-center">
                                            <img id="payment-preview-ept"
                                                src=""
                                                alt="Preview bukti pembayaran"
                                                class="h-12 w-12 rounded-lg object-cover border border-slate-200 shadow-sm">
                                            <div class="text-left">
                                                <p id="payment-filename-ept"
                                                class="text-xs font-semibold text-slate-700 truncate max-w-[180px]"></p>
                                                <p class="text-[11px] text-emerald-600">
                                                    <i class="fa-solid fa-check mr-1"></i>File siap diunggah
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <input
                                        id="bukti_pembayaran_input_ept"
                                        type="file"
                                        name="bukti_pembayaran"
                                        accept="image/*"
                                        required
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    >
                                </div>
                            </div>
                            @error('bukti_pembayaran') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-4 border-t border-slate-100">
                            <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-8 py-3 rounded-full bg-um-blue hover:bg-um-dark-blue text-white font-bold text-sm shadow-lg shadow-blue-900/20 transition-all hover:scale-[1.02]">
                                <i class="fa-solid fa-paper-plane"></i>
                                Daftar EPT
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    {{-- KONDISI 2: Pending --}}
    @elseif($registration->status === 'pending')
        <div class="bg-white rounded-2xl border border-slate-200 shadow-lg overflow-hidden">
            {{-- Header with gradient --}}
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-6 text-center">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-clock text-white text-3xl"></i>
                </div>
                <h2 class="text-xl font-bold text-white">Menunggu Verifikasi</h2>
                <p class="text-amber-100 text-sm mt-1">Pendaftaran Anda sedang diproses</p>
            </div>

            {{-- Info Cards --}}
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    {{-- Tanggal Daftar --}}
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-calendar text-amber-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-medium">Tanggal Daftar</p>
                                <p class="text-sm font-bold text-slate-800">{{ $registration->created_at->translatedFormat('d M Y') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-hourglass-half text-amber-600"></i>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 font-medium">Status</p>
                                <p class="text-sm font-bold text-amber-600">Menunggu</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info Box --}}
                <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                    <p class="text-sm text-blue-800 flex items-start gap-2">
                        <i class="fa-solid fa-info-circle text-blue-500 mt-0.5"></i>
                        <span>Anda akan menerima notifikasi WhatsApp setelah pendaftaran diverifikasi oleh admin.</span>
                    </p>
                </div>
            </div>
        </div>

    {{-- KONDISI 3: Approved --}}
    @elseif($registration->status === 'approved')
        {{-- Success Banner --}}
        <div class="bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl p-6 text-white shadow-lg text-center">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-circle-check text-3xl"></i>
            </div>
            <h2 class="text-xl font-bold">Pendaftaran Disetujui</h2>
            <p class="text-emerald-100 text-sm mt-1">Berikut adalah jadwal tes EPT yang telah ditetapkan untuk Anda</p>
        </div>

        {{-- Schedule Cards --}}
        <div class="grid gap-4">
            @php
                $grups = [
                    ['num' => 1, 'grup' => $registration->grup1, 'label' => 'Tes Pertama'],
                    ['num' => 2, 'grup' => $registration->grup2, 'label' => 'Tes Kedua'],
                    ['num' => 3, 'grup' => $registration->grup3, 'label' => 'Tes Ketiga'],
                ];
            @endphp

            @foreach($grups as $item)
                @php $grup = $item['grup']; $num = $item['num']; @endphp
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden hover:shadow-md transition-shadow">
                    {{-- Header with number --}}
                    <div class="flex items-center gap-4 p-4 border-b border-slate-100">
                        <div class="w-12 h-12 {{ $grup?->jadwal ? 'bg-gradient-to-br from-blue-600 to-blue-700' : 'bg-slate-200' }} rounded-xl flex items-center justify-center text-white font-bold text-xl shrink-0">
                            {{ $num }}
                        </div>
                        <div class="min-w-0 flex-1">
                            @if($grup)
                                <h3 class="font-bold text-slate-900">Grup {{ $grup->name }}</h3>
                                <p class="text-xs text-slate-400">{{ $item['label'] }}</p>
                            @else
                                <p class="text-slate-400 text-sm">Belum ditentukan</p>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Content --}}
                    <div class="p-4">
                        @if($grup)
                            @if($grup->jadwal)
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>{{ $grup->jadwal->translatedFormat('l, d M Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span>{{ $grup->jadwal->format('H:i') }} WIB</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-slate-600">
                                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $grup->lokasi }}</span>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="downloadUrl = '{{ route('dashboard.ept-registration.kartu', ['jadwal' => $num]) }}'; showModal = true"
                                        class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold shadow-sm transition-all">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    Download Kartu Peserta
                                </button>
                            @else
                                <div class="flex items-center gap-3 p-3 bg-amber-50 rounded-lg text-amber-700">
                                    <svg class="w-5 h-5 animate-pulse shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-medium">Menunggu penetapan jadwal dari admin</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Download Modal --}}
        <template x-teleport="body">
            <div x-show="showModal" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                 @click.self="showModal = false">
                <div x-show="showModal"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-amber-500 to-orange-500 p-5 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold">Informasi Penting</h3>
                                <p class="text-amber-100 text-sm">Harap perhatikan sebelum download</p>
                            </div>
                        </div>
                    </div>
                    {{-- Body --}}
                    <div class="p-5">
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">Print Kartu Peserta</p>
                                    <p class="text-sm text-slate-500">Bawa saat ujian untuk verifikasi</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">Bawa Identitas</p>
                                    <p class="text-sm text-slate-500">KTP atau Kartu Mahasiswa</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-900">Hadir Tepat Waktu</p>
                                    <p class="text-sm text-slate-500">15 menit sebelum jadwal dimulai</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    {{-- Footer --}}
                    <div class="p-5 pt-0 flex gap-3">
                        <button @click="showModal = false" 
                                class="flex-1 px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <a :href="downloadUrl" 
                           @click="showModal = false"
                           class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download
                        </a>
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // === Button "Mengerti dan Unggah Bukti" ===
        const btnUnderstand = document.getElementById('btn-understand-ept');
        const buttonWrapper = document.getElementById('understand-button-wrapper-ept');
        const uploadWrapper = document.getElementById('upload-wrapper-ept');

        if (btnUnderstand && buttonWrapper && uploadWrapper) {
            btnUnderstand.addEventListener('click', function () {
                buttonWrapper.classList.add('hidden');
                uploadWrapper.classList.remove('hidden');
            });
        }

        // === Preview Bukti Pembayaran ===
        const dropzone   = document.getElementById('payment-dropzone-ept');
        const input      = document.getElementById('bukti_pembayaran_input_ept');
        const previewBox = document.getElementById('payment-preview-wrapper-ept');
        const previewImg = document.getElementById('payment-preview-ept');
        const fileNameEl = document.getElementById('payment-filename-ept');

        if (dropzone && input && previewBox && previewImg && fileNameEl) {
            function handleFile(file) {
                if (!file) return;
                fileNameEl.textContent = file.name;

                if (!file.type.startsWith('image/')) {
                    previewImg.src = '';
                    previewBox.classList.remove('hidden');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewBox.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }

            input.addEventListener('change', function (e) {
                const file = e.target.files && e.target.files[0];
                handleFile(file);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-um-blue', 'bg-blue-50/50');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-um-blue', 'bg-blue-50/50');
                });
            });

            dropzone.addEventListener('drop', function (e) {
                const dt = e.dataTransfer;
                if (!dt || !dt.files || !dt.files[0]) return;
                input.files = dt.files;
                handleFile(dt.files[0]);
            });
        }
    });
</script>
@endsection
