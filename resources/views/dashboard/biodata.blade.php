@extends('layouts.dashboard')

@section('title', 'Biodata')
@section('page-title', 'Biodata Pengguna')

@section('content')
<style>
    @keyframes pulse-once {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    .animate-pulse-once {
        animation: pulse-once 1s ease-in-out 3;
    }
</style>
@php
    /** @var \App\Models\User $user */
    use Illuminate\Support\Facades\Storage;

    $avatarUrl = $user->image
        ? Storage::url($user->image)
        : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=EBF4FF&color=1E40AF&bold=true';
    
    $initialYear = old('year', $user->year);
    $shouldOpenPasswordModal = $errors->has('current_password') || $errors->has('password');
@endphp

<div
    x-data="{
        year: '{{ $initialYear }}',
        changePasswordOpen: {{ $shouldOpenPasswordModal ? 'true' : 'false' }}
    }"
    class="max-w-7xl mx-auto"
>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- KOLOM KIRI: Kartu Profil (Lebar 4/12) --}}
        <div class="lg:col-span-4 space-y-6">
            
            {{-- Profile Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden relative group">
                {{-- Header Background --}}
                <div class="h-32 bg-gradient-to-br from-slate-800 to-um-blue relative overflow-hidden">
                    <div class="absolute inset-0 opacity-20 pattern-grid-lg"></div> {{-- Optional Pattern --}}
                    <div class="absolute -bottom-4 -right-4 text-9xl text-white opacity-5 rotate-12">
                        <i class="fa-solid fa-user-circle"></i>
                    </div>
                </div>

                {{-- Avatar & Basic Info --}}
                <div class="px-6 pb-6 relative">
                    <div class="relative -mt-12 mb-4 flex justify-center">
                        <div class="p-1.5 bg-white rounded-full shadow-sm">
                            <img src="{{ $avatarUrl }}" 
                                 alt="Avatar" 
                                 class="h-28 w-28 rounded-full object-cover border border-slate-100 bg-slate-50">
                        </div>
                    </div>

                    <div class="text-center mb-6">
                        <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    </div>

                    {{-- Detailed Info List --}}
                    <div class="space-y-3 bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-regular fa-id-card text-slate-400 w-4"></i> NPM
                            </span>
                            <span class="font-semibold text-slate-700">{{ $user->srn ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm border-t border-slate-200/60 pt-3">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-solid fa-graduation-cap text-slate-400 w-4"></i> Prodi
                            </span>
                            <span class="font-semibold text-slate-700 text-right truncate ml-4">
                                {{ $user->prody->name ?? '-' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm border-t border-slate-200/60 pt-3">
                            <span class="text-slate-500 flex items-center gap-2">
                                <i class="fa-regular fa-calendar text-slate-400 w-4"></i> Angkatan
                            </span>
                            <span class="font-semibold text-slate-700">{{ $user->year ?? '-' }}</span>
                        </div>
                    </div>

                    {{-- Security Action --}}
                    <div class="mt-6 pt-6 border-t border-slate-100">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Keamanan Akun</h4>
                        <button type="button"
                                @click="changePasswordOpen = true"
                                class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-medium hover:border-amber-300 hover:bg-amber-50 hover:text-amber-700 transition-all group/btn shadow-sm">
                            <span class="flex items-center gap-2"><i class="fa-solid fa-lock text-slate-400 group-hover/btn:text-amber-500"></i> Ganti Password</span>
                            <i class="fa-solid fa-chevron-right text-xs text-slate-300 group-hover/btn:text-amber-400"></i>
                        </button>
                        
                        @if (session('password_success'))
                            <div class="mt-3 text-xs text-emerald-600 bg-emerald-50 px-3 py-2 rounded-lg border border-emerald-100 flex items-center gap-2 animate-pulse">
                                <i class="fa-solid fa-check-circle"></i> {{ session('password_success') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: Form Edit (Lebar 8/12) --}}
        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-2xl">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Edit Informasi</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Perbarui data diri dan profil akademik Anda.</p>
                    </div>
                    <div class="hidden sm:block">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-medium border border-blue-100">
                            <i class="fa-solid fa-circle-info"></i> Pastikan data valid
                        </span>
                    </div>
                </div>

                <form method="POST"
                      action="{{ route('bl.profile.complete.submit') }}"
                      enctype="multipart/form-data"
                      class="p-6 space-y-8">
                    @csrf
                    <input type="hidden" name="next" value="{{ route('dashboard') }}">

                    {{-- SECTION 1: Informasi Pribadi --}}
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-um-blue"></span> Data Pribadi
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Nama Lengkap --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5 ml-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-user text-slate-400 text-sm"></i>
                                    </div>
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                           class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-um-blue focus:ring-um-blue sm:text-sm transition-all duration-200"
                                           placeholder="Nama sesuai KTM">
                                </div>
                                @error('name') <p class="mt-1 text-xs text-rose-600 pl-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Email --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5 ml-1">Email Akun <span class="text-rose-500">*</span></label>
                                <div class="relative opacity-75">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-regular fa-envelope text-slate-400 text-sm"></i>
                                    </div>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}" readonly
                                           class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-100 text-slate-500 sm:text-sm cursor-not-allowed"
                                           title="Email tidak dapat diubah">
                                </div>
                            </div>

                            {{-- WhatsApp dengan Verifikasi OTP --}}
                            @php
                                $hasPendingOtp = $user->whatsapp_otp && $user->whatsapp_otp_expires_at && now()->isBefore($user->whatsapp_otp_expires_at);
                                $initialStep = $user->whatsapp_verified_at ? 'verified' : ($hasPendingOtp ? 'otp' : 'input');
                                $remainingSeconds = $hasPendingOtp ? (int) max(0, now()->diffInSeconds($user->whatsapp_otp_expires_at, false)) : 0;
                            @endphp
                            <div class="md:col-span-2 {{ $hasPendingOtp ? 'ring-2 ring-green-400 ring-offset-2 rounded-2xl p-4 bg-green-50/50 animate-pulse-once' : '' }}" 
                                 x-data="{
                                    phone: '{{ old('whatsapp', $user->whatsapp) }}',
                                    otp: '',
                                    step: '{{ $initialStep }}',
                                    loading: false,
                                    error: '',
                                    countdown: {{ $remainingSeconds }},
                                    async sendOtp() {
                                        if (!this.phone || this.phone.length < 10) {
                                            this.error = 'Nomor WhatsApp tidak valid';
                                            return;
                                        }
                                        this.loading = true;
                                        this.error = '';
                                        try {
                                            const res = await fetch('{{ route('api.whatsapp.send-otp') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                },
                                                body: JSON.stringify({ whatsapp: this.phone })
                                            });
                                            const data = await res.json();
                                            if (data.success) {
                                                this.step = 'otp';
                                                this.countdown = 60;
                                                this.startCountdown();
                                            } else {
                                                this.error = data.message || 'Gagal mengirim OTP';
                                            }
                                        } catch (e) {
                                            this.error = 'Terjadi kesalahan';
                                        }
                                        this.loading = false;
                                    },
                                    async verifyOtp() {
                                        if (!this.otp || this.otp.length !== 6) {
                                            this.error = 'Kode OTP harus 6 digit';
                                            return;
                                        }
                                        this.loading = true;
                                        this.error = '';
                                        try {
                                            const res = await fetch('{{ route('api.whatsapp.verify-otp') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                },
                                                body: JSON.stringify({ otp: this.otp })
                                            });
                                            const data = await res.json();
                                            if (data.success) {
                                                this.step = 'verified';
                                            } else {
                                                this.error = data.message || 'OTP tidak valid';
                                            }
                                        } catch (e) {
                                            this.error = 'Terjadi kesalahan';
                                        }
                                        this.loading = false;
                                    },
                                    startCountdown() {
                                        const interval = setInterval(() => {
                                            this.countdown--;
                                            if (this.countdown <= 0) clearInterval(interval);
                                        }, 1000);
                                    },
                                    changeNumber() {
                                        this.step = 'input';
                                        this.otp = '';
                                        this.error = '';
                                    }
                                 }">
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5 ml-1">
                                    Nomor WhatsApp <span class="text-rose-500">*</span>
                                </label>
                                
                                {{-- Step 1: Input Nomor --}}
                                <div x-show="step === 'input'" class="space-y-3">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa-brands fa-whatsapp text-green-500"></i>
                                        </div>
                                        <input type="tel" 
                                               x-model="phone"
                                               name="whatsapp" 
                                               inputmode="numeric"
                                               class="pl-10 block w-full py-3 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500 text-base transition-all duration-200"
                                               placeholder="Masukan Nomor WhatsApp">
                                    </div>
                                    <button type="button"
                                            @click="sendOtp()"
                                            :disabled="loading || !phone"
                                            :class="{'opacity-50 cursor-not-allowed': loading || !phone}"
                                            class="w-full py-3 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                                        <i x-show="!loading" class="fa-solid fa-paper-plane"></i>
                                        <i x-show="loading" class="fa-solid fa-spinner fa-spin"></i>
                                        <span>Kirim Kode OTP</span>
                                    </button>
                                    <p class="text-xs text-slate-500 text-center">
                                        <i class="fa-solid fa-circle-info text-slate-400"></i>
                                        Kode verifikasi akan dikirim ke WhatsApp Anda
                                    </p>
                                </div>

                                {{-- Step 2: Input OTP --}}
                                <div x-show="step === 'otp'" class="space-y-3">
                                    <div class="p-3 rounded-xl bg-green-50 border border-green-100 text-sm text-green-700">
                                        <i class="fa-solid fa-check-circle"></i>
                                        OTP dikirim ke <strong x-text="phone"></strong>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-2">
                                        <input type="text" 
                                               x-model="otp"
                                               maxlength="6"
                                               inputmode="numeric"
                                               pattern="[0-9]*"
                                               class="w-full sm:flex-1 text-center text-xl tracking-[0.4em] font-mono py-3 rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-green-500 focus:ring-green-500"
                                               placeholder="● ● ● ● ● ●">
                                        <button type="button"
                                                @click="verifyOtp()"
                                                :disabled="loading || otp.length !== 6"
                                                :class="{'opacity-50 cursor-not-allowed': loading || otp.length !== 6}"
                                                class="w-full sm:w-auto px-6 py-3 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2">
                                            <i x-show="!loading" class="fa-solid fa-check"></i>
                                            <i x-show="loading" class="fa-solid fa-spinner fa-spin"></i>
                                            <span>Verifikasi</span>
                                        </button>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <button type="button" @click="changeNumber()" class="text-slate-500 hover:text-slate-700">
                                            <i class="fa-solid fa-arrow-left"></i> Ganti Nomor
                                        </button>
                                        <button type="button" 
                                                @click="sendOtp()" 
                                                :disabled="countdown > 0"
                                                :class="countdown > 0 ? 'text-slate-400 cursor-not-allowed' : 'text-green-600 hover:text-green-700'">
                                            <span x-show="countdown > 0">Kirim ulang (<span x-text="countdown"></span>s)</span>
                                            <span x-show="countdown <= 0">Kirim ulang OTP</span>
                                        </button>
                                    </div>
                                </div>

                                {{-- Step 3: Verified --}}
                                <div x-show="step === 'verified'" class="space-y-2">
                                    <div class="p-4 rounded-xl bg-green-50 border border-green-200">
                                        <div class="flex items-center gap-3">
                                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                                                <i class="fa-brands fa-whatsapp text-green-600 text-xl"></i>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-base font-bold text-slate-800" x-text="phone"></p>
                                                <p class="text-sm text-green-600 font-medium flex items-center gap-1">
                                                    <i class="fa-solid fa-circle-check"></i>
                                                    Terverifikasi
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="whatsapp" x-bind:value="phone">
                                    <button type="button" @click="changeNumber()" class="text-xs text-slate-500 hover:text-slate-700 flex items-center gap-1">
                                        <i class="fa-solid fa-pen-to-square"></i> Ganti Nomor WhatsApp
                                    </button>
                                </div>

                                {{-- Error Message --}}
                                <p x-show="error" x-text="error" class="mt-2 text-xs text-rose-600 pl-1"></p>
                                @error('whatsapp') <p class="mt-1 text-xs text-rose-600 pl-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Upload Foto --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5 ml-1">Foto Profil</label>
                                <div class="flex items-center gap-4 p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 hover:bg-slate-100 transition-colors">
                                    <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-400">
                                        <i class="fa-solid fa-camera"></i>
                                    </div>
                                    <div class="flex-1">
                                        <input type="file" name="image" id="file-upload" class="hidden" onchange="document.getElementById('file-name').innerText = this.files[0].name">
                                        <label for="file-upload" class="cursor-pointer text-sm font-medium text-um-blue hover:text-um-dark-blue focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-um-blue">
                                            <span>Upload foto baru</span>
                                        </label>
                                        <p class="text-xs text-slate-500 mt-0.5" id="file-name">JPG, PNG hingga 2MB</p>
                                        @if($user->image)
                                            <button type="submit"
                                                    form="delete-photo-form"
                                                    class="mt-2 text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1">
                                                <i class="fa-solid fa-trash"></i> Hapus Foto Profil
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                @error('image') <p class="mt-1 text-xs text-rose-600 pl-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="border-slate-100 border-dashed">

                    {{-- SECTION 2: Data Akademik --}}
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-um-blue"></span> Data Akademik
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- NPM --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5 ml-1">NPM / NIM <span class="text-rose-500">*</span></label>
                                <input type="text" name="srn" value="{{ old('srn', $user->srn) }}"
                                       class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-um-blue focus:ring-um-blue sm:text-sm transition-all duration-200">
                                @error('srn') <p class="mt-1 text-xs text-rose-600 pl-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Angkatan --}}
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5 ml-1">Tahun Angkatan <span class="text-rose-500">*</span></label>
                                <select name="year" x-model="year"
                                        class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-um-blue focus:ring-um-blue sm:text-sm transition-all duration-200">
                                    <option value="">Pilih Tahun</option>
                                    @foreach (collect(range(2017, (int)date('Y')+1))->reverse() as $y)
                                        <option value="{{ $y }}" @selected((int) old('year', $user->year) === $y)>{{ $y }}</option>
                                    @endforeach
                                </select>
                                @error('year') <p class="mt-1 text-xs text-rose-600 pl-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Prodi --}}
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5 ml-1">Program Studi <span class="text-rose-500">*</span></label>
                                <select name="prody_id"
                                        class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-um-blue focus:ring-um-blue sm:text-sm transition-all duration-200">
                                    <option value="">Pilih Program Studi</option>
                                    @foreach ($prodis as $prody)
                                        <option value="{{ $prody->id }}" @selected(old('prody_id', $user->prody_id) == $prody->id)>
                                            {{ $prody->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('prody_id') <p class="mt-1 text-xs text-rose-600 pl-1">{{ $message }}</p> @enderror
                            </div>

                            {{-- Nilai BL (Conditional) --}}
                            <div x-show="year && parseInt(year) <= 2024" x-collapse 
                                 class="md:col-span-2 bg-blue-50/60 p-5 rounded-xl border border-blue-100 relative overflow-hidden">
                                <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 bg-blue-100 rounded-full blur-xl opacity-50"></div>
                                <div class="relative z-10">
                                    <label class="flex items-center gap-2 text-sm font-bold text-um-blue mb-2">
                                        <i class="fa-solid fa-star"></i> Nilai Basic Listening
                                    </label>
                                    <p class="text-xs text-slate-600 mb-3 leading-relaxed">
                                        Wajib diisi untuk mahasiswa angkatan <strong>2024 dan sebelumnya</strong> sebagai syarat administrasi.
                                    </p>
                                    <input type="number" name="nilaibasiclistening" min="0" max="100" placeholder="0-100"
                                           value="{{ old('nilaibasiclistening', $user->nilaibasiclistening) }}"
                                           class="block w-full max-w-[150px] rounded-lg border-blue-200 focus:border-um-blue focus:ring-um-blue sm:text-sm bg-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Form Footer --}}
                    <div class="pt-6 flex items-center justify-end gap-3">
                        <button type="reset" class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors">
                            Reset
                        </button>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-um-blue hover:bg-um-dark-blue text-white text-sm font-semibold shadow-lg shadow-blue-900/20 transition-all hover:scale-[1.02] active:scale-95">
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>

                @if($user->image)
                    {{-- Form terpisah untuk hapus foto (di luar form utama agar tidak bentrok method) --}}
                    <form id="delete-photo-form" action="{{ route('dashboard.biodata.photo.delete') }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Ganti Password (Modern Style) --}}
    <div x-show="changePasswordOpen" 
         class="fixed inset-0 z-[60] overflow-y-auto" 
         aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
        
        <div x-show="changePasswordOpen"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="changePasswordOpen"
                 @click.away="changePasswordOpen = false"
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-8 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 scale-100" x-transition:leave-end="opacity-0 translate-y-8 scale-95"
                 class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all border border-slate-100">
                
                {{-- Modal Header --}}
                <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50 flex items-center gap-4">
                    <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
                        <i class="fa-solid fa-key text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Ganti Password</h3>
                        <p class="text-xs text-slate-500">Amankan akun Anda dengan password baru.</p>
                    </div>
                </div>
                
                {{-- Modal Body --}}
                <div class="px-6 py-6">
                    <form id="passwordForm" method="POST" action="{{ route('dashboard.password.update') }}" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password Lama</label>
                            <input type="password" name="current_password" 
                                   class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-amber-500 focus:ring-amber-500 sm:text-sm transition-colors">
                            @error('current_password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Password Baru</label>
                            <input type="password" name="password" 
                                   class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-amber-500 focus:ring-amber-500 sm:text-sm transition-colors">
                            @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" 
                                   class="block w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:border-amber-500 focus:ring-amber-500 sm:text-sm transition-colors">
                        </div>
                    </form>
                </div>

                {{-- Modal Footer --}}
                <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 border-t border-slate-100">
                    <button type="submit" form="passwordForm" 
                            class="inline-flex justify-center rounded-xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 transition-colors">
                        Simpan
                    </button>
                    <button type="button" @click="changePasswordOpen = false" 
                            class="inline-flex justify-center rounded-xl bg-white px-5 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
