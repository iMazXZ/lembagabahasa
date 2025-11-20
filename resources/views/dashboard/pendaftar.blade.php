{{-- resources/views/dashboard/pendaftar.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Dashboard Pendaftar')
@section('page-title', 'Overview')

@section('content')
@php
    /** @var \App\Models\User $user */
    use Illuminate\Support\Facades\Storage;

    $avatarUrl = $user->image
        ? Storage::url($user->image)
        : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=EBF4FF&color=1E40AF&bold=true';

    // Logic Biodata
    $hasBasicInfo = $user->prody_id && $user->srn && $user->year;
    $yearInt      = (int) $user->year;
    $needsManual  = $yearInt && $yearInt <= 2024;

    $biodataLengkap = $hasBasicInfo && (
        ! $needsManual || is_numeric($user->nilaibasiclistening)
    );
@endphp

<div class="space-y-6">

    {{-- SECTION 1: Welcome & Biodata Status --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Welcome Card --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-4 lg:p-8 flex items-center gap-4 lg:gap-8 relative overflow-hidden group">
            
            {{-- [BARU] Tombol Home di Pojok Kanan Atas --}}
            <a href="{{ url('/') }}" 
               class="absolute top-3 right-4 z-20 text-slate-400 hover:text-blue-600 border border-transparent transition-all" 
               title="Kembali ke Halaman Utama">
                <i class="fa-solid fa-house text-sm"></i>
            </a>
            {{-- [END BARU] --}}

            {{-- Dekorasi Background --}}
            <div class="hidden lg:block absolute top-0 right-0 -mt-4 -mr-4 w-40 h-40 bg-gradient-to-br from-blue-50 to-blue-100 rounded-full blur-2xl opacity-60 pointer-events-none"></div>
            
            {{-- ... Sisa kode Foto Profil dan Info User biarkan sama ... --}}
             <img src="{{ $avatarUrl }}" 
                 alt="Foto Profil" 
                 class="h-12 w-12 lg:h-24 lg:w-24 rounded-full object-cover border border-slate-200 shadow-sm shrink-0 z-10 transition-all duration-300">
            
            {{-- Info User (Sama seperti sebelumnya) --}}
            <div class="flex-1 min-w-0 z-10 space-y-1 lg:space-y-3">
               {{-- ... content info user ... --}}
               <div>
                    <h2 class="text-base lg:text-3xl font-bold text-slate-900 truncate leading-tight">
                        Hi, <span class="lg:hidden">{{ explode(' ', $user->name)[0] }}!</span>
                        <span class="hidden lg:inline">{{ $user->name }}!</span>
                    </h2>
                    <p class="text-xs lg:text-base text-slate-500 truncate">
                        {{ $user->email }}
                    </p>
                </div>
                 {{-- ... badge prodi & tahun ... --}}
                 <div class="hidden lg:flex flex-wrap gap-3">
                    @if ($user->prody)
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-blue-50 text-blue-700 text-sm font-medium border border-blue-100">
                            <i class="fa-solid fa-graduation-cap"></i>
                            {{ $user->prody->name }}
                        </span>
                    @endif
                    @if ($user->year)
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-50 text-slate-700 text-sm font-medium border border-slate-200">
                            <i class="fa-solid fa-calendar-days"></i>
                            Angkatan {{ $user->year }}
                        </span>
                    @endif
                </div>
                
                 {{-- TAMPILAN HP --}}
                <div class="lg:hidden flex items-center gap-2 text-[11px] font-medium text-slate-600">
                    @if ($user->prody)
                        <span class="flex items-center gap-1 truncate">
                            <i class="fa-solid fa-graduation-cap text-slate-400"></i>
                            {{ $user->prody->name }}
                        </span>
                    @endif
                    @if ($user->year)
                        <span class="text-slate-300 mx-1">•</span>
                        <span class="flex items-center gap-1 whitespace-nowrap">{{ $user->year }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Biodata Action Card --}}
        <div class="rounded-xl border shadow-sm p-6 flex flex-col justify-center relative overflow-hidden
             {{ $biodataLengkap ? 'bg-emerald-50 border-emerald-100' : 'bg-amber-50 border-amber-100' }}">
            
            <div class="flex items-start justify-between mb-2">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider {{ $biodataLengkap ? 'text-emerald-600' : 'text-amber-600' }}">
                        Status Akun
                    </p>
                    <h3 class="text-lg font-bold {{ $biodataLengkap ? 'text-emerald-800' : 'text-amber-800' }}">
                        {{ $biodataLengkap ? 'Biodata Lengkap' : 'Lengkapi Data' }}
                    </h3>
                </div>
                <div class="h-10 w-10 rounded-full flex items-center justify-center {{ $biodataLengkap ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                    <i class="fa-solid {{ $biodataLengkap ? 'fa-check' : 'fa-exclamation' }} text-lg"></i>
                </div>
            </div>

            <p class="text-sm {{ $biodataLengkap ? 'text-emerald-700' : 'text-amber-700' }} mb-4 leading-relaxed">
                @if ($biodataLengkap)
                    Akun Anda siap digunakan untuk mengajukan layanan.
                @else
                    Mohon lengkapi biodata {{ $needsManual ? 'dan nilai Basic Listening' : '' }} agar dapat mengakses layanan.
                @endif
            </p>

            <a href="{{ route('dashboard.biodata') }}" 
               class="inline-flex items-center justify-center gap-2 w-full py-2 rounded-lg text-sm font-semibold transition-colors
               {{ $biodataLengkap 
                  ? 'bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-50' 
                  : 'bg-amber-600 text-white hover:bg-amber-700 shadow-sm' }}">
                {{ $biodataLengkap ? 'Edit Biodata' : 'Lengkapi Sekarang' }}
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>

    {{-- Menggunakan variabel $user yang sudah didefinisikan di atas --}}
    @if ($user && $user->hasRole('tutor'))
        <div class="group relative bg-white rounded-xl border border-indigo-100 shadow-sm overflow-hidden hover:shadow-md transition-all duration-300">
            
            {{-- Aksen garis biru di kiri --}}
            <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-indigo-500"></div>

            {{-- Hiasan Background Halus --}}
            <div class="absolute right-0 top-0 w-24 h-24 bg-indigo-50 rounded-bl-full opacity-50 pointer-events-none"></div>

            <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                
                {{-- Kiri: Icon & Teks --}}
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-wider font-bold text-indigo-600 mb-0.5">
                            Akses Khusus
                        </p>
                        <h3 class="text-sm font-bold text-slate-800">
                            Panel Tutor Basic Listening
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Kelola mahasiswa binaan & input nilai di sini.
                        </p>
                    </div>
                </div>

                {{-- Kanan: Tombol Action (Lebih Soft) --}}
                <div class="flex-shrink-0">
                    <a href="{{ route('filament.admin.pages.2') }}" 
                       class="inline-flex w-full sm:w-auto justify-center items-center gap-2 px-4 py-2.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition-colors shadow-indigo-200 shadow-sm">
                        <span>Buka Panel</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- SECTION 2: Quick Actions (Fixed Layout) --}}
    <div>
        <h3 class="text-sm font-bold text-slate-900 mb-3 px-1">Menu Cepat</h3>
        
        {{-- REVISI: lg:grid-cols-5 agar muat 5 item sejajar di laptop --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">

            {{-- 1. Basic Listening --}}
            {{-- col-span-2 (HP) -> col-span-1 (Laptop/Tablet) --}}
            <a href="{{ route('bl.index') }}" 
               class="col-span-2 md:col-span-1 group relative overflow-hidden bg-violet-600 rounded-xl shadow-md shadow-violet-200 transition-all duration-200 hover:shadow-lg hover:bg-violet-700 flex items-center p-3.5 gap-3">
                
                {{-- Dekorasi Background --}}
                <div class="absolute right-0 top-0 -mt-2 -mr-2 w-16 h-16 bg-white opacity-10 rounded-full blur-xl pointer-events-none"></div>
                
                <div class="shrink-0 h-10 w-10 rounded-lg bg-white/20 flex items-center justify-center text-white border border-white/10 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-headphones text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-bold text-white truncate">Basic Listening</div>
                    <div class="text-[10px] text-violet-100/90 truncate mt-0.5">Kelas & Sertifikat</div>
                </div>
                {{-- Icon Panah (Hanya muncul di HP biar tidak sempit di laptop) --}}
                <i class="fa-solid fa-chevron-right text-white/50 text-xs hidden sm:block"></i>
            </a>

            {{-- Helper Style --}}
            @php
                $cardClass = "group bg-white border border-slate-200 rounded-xl p-3.5 flex items-center gap-3 shadow-sm hover:border-blue-300 hover:shadow-md transition-all duration-200";
                $iconBase  = "shrink-0 h-10 w-10 rounded-lg flex items-center justify-center text-lg transition-transform group-hover:scale-105";
            @endphp

            {{-- 2. Surat Rekomendasi --}}
            <a href="{{ route('dashboard.ept') }}" class="{{ $cardClass }}">
                <div class="{{ $iconBase }} bg-blue-50 text-blue-600">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-slate-800 group-hover:text-blue-600 truncate">Rekomendasi</div>
                    <div class="text-[10px] text-slate-500 truncate mt-0.5">Ajukan surat</div>
                </div>
            </a>

            {{-- 3. Penerjemahan --}}
            <a href="{{ route('dashboard.translation') }}" class="{{ $cardClass }} hover:border-indigo-300">
                <div class="{{ $iconBase }} bg-indigo-50 text-indigo-600">
                    <i class="fa-solid fa-language"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-slate-800 group-hover:text-indigo-600 truncate">Penerjemahan</div>
                    <div class="text-[10px] text-slate-500 truncate mt-0.5">Abstrak/Dokumen</div>
                </div>
            </a>

            {{-- 4. Cek Nilai EPT --}}
            <a href="{{ route('front.scores') }}" class="{{ $cardClass }} hover:border-emerald-300">
                <div class="{{ $iconBase }} bg-emerald-50 text-emerald-600">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-slate-800 group-hover:text-emerald-600 truncate">Cek Nilai EPT</div>
                    <div class="text-[10px] text-slate-500 truncate mt-0.5">Lihat skor</div>
                </div>
            </a>

            {{-- 5. Cek Jadwal EPT --}}
            <a href="{{ route('front.schedule') }}" class="{{ $cardClass }} hover:border-amber-300">
                <div class="{{ $iconBase }} bg-amber-50 text-amber-600">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-slate-800 group-hover:text-amber-600 truncate">Jadwal EPT</div>
                    <div class="text-[10px] text-slate-500 truncate mt-0.5">Info pelaksanaan</div>
                </div>
            </a>

        </div>
    </div>

    {{-- SECTION 3: Recent Activity / Status Widgets --}}
    @php
        $translation = $latestTranslation ?? null;
        $ept         = $latestEptSubmission ?? null;

        // Helper untuk menentukan Styling Full Card
        $getTheme = function($status) {
            $s = strtolower($status ?? '');
            
            // Default Theme (Abu-abu/Netral)
            $theme = [
                'card'    => 'bg-white border-slate-200 hover:border-slate-300',
                'text'    => 'text-slate-800',
                'subtext' => 'text-slate-500',
                'icon_bg' => 'bg-slate-100 text-slate-400',
                'badge'   => 'bg-slate-100 text-slate-600',
                'btn'     => 'bg-slate-800 text-white hover:bg-slate-700'
            ];

            // KUNING: Menunggu / Pending
            if ($s === 'menunggu' || $s === 'pending') {
                $theme = [
                    'card'    => 'bg-amber-50 border-amber-200 hover:border-amber-300',
                    'text'    => 'text-amber-900',
                    'subtext' => 'text-amber-700/70',
                    'icon_bg' => 'bg-white/60 text-amber-500',
                    'badge'   => 'bg-white text-amber-700 border border-amber-200 shadow-sm',
                    'btn'     => 'bg-amber-600 text-white hover:bg-amber-700'
                ];
            }
            // BIRU: Sedang Diproses (Opsional, jika ingin dibedakan)
            elseif ($s === 'diproses') {
                $theme = [
                    'card'    => 'bg-blue-50 border-blue-200 hover:border-blue-300',
                    'text'    => 'text-blue-900',
                    'subtext' => 'text-blue-700/70',
                    'icon_bg' => 'bg-white/60 text-blue-500',
                    'badge'   => 'bg-white text-blue-700 border border-blue-200 shadow-sm',
                    'btn'     => 'bg-blue-600 text-white hover:bg-blue-700'
                ];
            }
            // HIJAU CERAH (EMERALD): Disetujui, Approved, Selesai
            elseif (in_array($s, ['disetujui', 'approved', 'selesai'])) {
                $theme = [
                    'card'    => 'bg-emerald-50 border-emerald-200 hover:border-emerald-300', // Background hijau sangat muda
                    'text'    => 'text-emerald-900', // Teks hijau tua
                    'subtext' => 'text-emerald-700/70',
                    'icon_bg' => 'bg-white/60 text-emerald-600', // Icon hijau cerah
                    'badge'   => 'bg-white text-emerald-700 border border-emerald-200 shadow-sm',
                    'btn'     => 'bg-emerald-600 text-white hover:bg-emerald-700'
                ];
            }
            // MERAH: Ditolak / Rejected
            elseif (str_contains($s, 'ditolak') || $s === 'rejected') {
                $theme = [
                    'card'    => 'bg-rose-50 border-rose-200 hover:border-rose-300',
                    'text'    => 'text-rose-900',
                    'subtext' => 'text-rose-700/70',
                    'icon_bg' => 'bg-white/60 text-rose-500',
                    'badge'   => 'bg-white text-rose-700 border border-rose-200 shadow-sm',
                    'btn'     => 'bg-rose-600 text-white hover:bg-rose-700'
                ];
            }

            return (object) $theme;
        };

        $statusTrans = $translation?->status;
        $statusEpt = $ept?->status;

        $labelEpt = match($statusEpt) {
            'pending' => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => $statusEpt
        };

        $themeTrans = $getTheme($translation ? $statusTrans : null);
        $themeEpt   = $getTheme($ept ? $statusEpt : null);
    @endphp

    <h3 class="text-lg font-bold text-slate-800 mt-8 mb-5 px-1 flex items-center gap-2">
        <span class="w-1 h-6 bg-um-blue rounded-full"></span> Status Layanan Terakhir
    </h3>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Widget 1: Penerjemahan --}}
        <div class="group relative rounded-2xl border p-6 transition-all duration-300 flex flex-col h-full overflow-hidden {{ $themeTrans->card }}">
            
            {{-- Watermark Icon --}}
            <div class="absolute -right-6 -bottom-6 text-9xl opacity-[0.07] pointer-events-none select-none transition-transform group-hover:scale-110">
                <i class="fa-solid fa-language"></i>
            </div>

            {{-- Header --}}
            <div class="relative z-10 flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl flex items-center justify-center shadow-sm {{ $themeTrans->icon_bg }}">
                        <i class="fa-solid fa-language text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold {{ $themeTrans->text }}">Penerjemahan Abstrak</h4>
                        <p class="text-xs {{ $themeTrans->subtext }}">Layanan Bahasa</p>
                    </div>
                </div>
                @if($translation)
                    <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $themeTrans->badge }}">
                        {{ $statusTrans }}
                    </span>
                @endif
            </div>

            {{-- Body --}}
            <div class="relative z-10 flex-1 flex flex-col">
                @if (!$translation)
                    <div class="flex-1 flex flex-col justify-center py-2">
                        <p class="text-sm text-slate-500 mb-4">Belum ada pengajuan aktif saat ini.</p>
                        <a href="{{ route('dashboard.translation') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-um-blue hover:underline decoration-um-blue/30 underline-offset-4">
                            Ajukan Sekarang <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <div class="mt-2">
                        <p class="text-xs uppercase tracking-wide opacity-70 mb-1 {{ $themeTrans->text }}">Tanggal Pengajuan</p>
                        <p class="text-base font-bold {{ $themeTrans->text }}">
                            {{ optional($translation->submission_date ?? $translation->created_at)->translatedFormat('l, d F Y') }}
                        </p>
                    </div>

                    @php
                        $hasFile = $translation->dokumen_terjemahan || $translation->final_file_path;
                        $downloadUrl = $translation->dokumen_terjemahan
                            ? Storage::url($translation->dokumen_terjemahan)
                            : ($translation->final_file_path ? Storage::url($translation->final_file_path) : null);
                    @endphp

                    <div class="mt-auto pt-6 flex flex-wrap gap-3">
                        @if ($statusTrans === 'Selesai' && $hasFile && $downloadUrl)
                            <a href="{{ $downloadUrl }}" target="_blank" class="shadow-sm px-4 py-2 rounded-lg text-xs font-semibold transition-transform active:scale-95 flex items-center gap-2 bg-white text-emerald-700 border border-emerald-200 hover:bg-emerald-50">
                                <i class="fa-solid fa-download"></i> Unduh
                            </a>
                        @endif
                        <a href="{{ route('dashboard.translation') }}" class="shadow-sm px-4 py-2 rounded-lg text-xs font-semibold transition-transform active:scale-95 flex items-center gap-2 {{ $themeTrans->badge }} hover:brightness-95">
                            Detail <i class="fa-solid fa-arrow-right opacity-50"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- Widget 2: Surat Rekomendasi EPT --}}
        <div class="group relative rounded-2xl border p-6 transition-all duration-300 flex flex-col h-full overflow-hidden {{ $themeEpt->card }}">
            
            {{-- Watermark Icon --}}
            <div class="absolute -right-6 -bottom-6 text-9xl opacity-[0.07] pointer-events-none select-none transition-transform group-hover:scale-110">
                <i class="fa-solid fa-file-signature"></i>
            </div>

            {{-- Header --}}
            <div class="relative z-10 flex justify-between items-start mb-4">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl flex items-center justify-center shadow-sm {{ $themeEpt->icon_bg }}">
                        <i class="fa-solid fa-file-signature text-lg"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold {{ $themeEpt->text }}">Surat Rekomendasi</h4>
                        <p class="text-xs {{ $themeEpt->subtext }}">Administrasi EPT</p>
                    </div>
                </div>
                @if($ept)
                    <span class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider {{ $themeEpt->badge }}">
                        {{ $labelEpt }}
                    </span>
                @endif
            </div>

            {{-- Body --}}
            <div class="relative z-10 flex-1 flex flex-col">
                @if (!$ept)
                    <div class="flex-1 flex flex-col justify-center py-2">
                        <p class="text-sm text-slate-500 mb-4">Belum ada permintaan surat.</p>
                        <a href="{{ route('dashboard.ept') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-um-blue hover:underline decoration-um-blue/30 underline-offset-4">
                            Buat Surat <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <div class="mt-2">
                        <p class="text-xs uppercase tracking-wide opacity-70 mb-1 {{ $themeEpt->text }}">Waktu Pengajuan</p>
                        <p class="text-base font-bold {{ $themeEpt->text }}">
                            {{ optional($ept->created_at)->translatedFormat('l, d F Y') }}
                            <span class="text-xs font-normal opacity-80 ml-1">pukul {{ optional($ept->created_at)->format('H:i') }}</span>
                        </p>
                    </div>
                    
                    <div class="mt-auto pt-6 flex flex-wrap gap-3">
                        <a href="{{ route('dashboard.ept') }}" class="w-full justify-center shadow-sm px-4 py-2 rounded-lg text-xs font-semibold transition-transform active:scale-95 flex items-center gap-2 {{ $themeEpt->badge }} hover:brightness-95">
                            Lihat Riwayat <i class="fa-solid fa-arrow-right opacity-50"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection