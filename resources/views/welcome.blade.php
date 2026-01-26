@extends('layouts.front')

@section('title', 'Lembaga Bahasa UM Metro | EPT, Penerjemahan, Pelatihan Bahasa')

@section('meta')
  <meta name="description" content="Lembaga Bahasa UM Metro menyediakan layanan English Proficiency Test (EPT), penerjemahan dokumen, dan pelatihan Basic Listening untuk mahasiswa dan umum di Kota Metro, Lampung.">
  <meta name="keywords" content="Lembaga Bahasa UM Metro, EPT UM Metro, Penerjemahan, Basic Listening, Jadwal EPT, Nilai EPT, Pelatihan Bahasa Inggris">
  <meta name="author" content="Lembaga Bahasa UM Metro">
@endsection

@section('content')

{{-- HERO SECTION (Matching Basic Listening Style) --}}
<div class="relative bg-slate-900 overflow-hidden">
    {{-- Background Decor --}}
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-indigo-900 opacity-90"></div>
        {{-- Pattern dots --}}
        <div class="absolute inset-0" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 30px 30px; opacity: 0.1;"></div>
        {{-- Glow effects --}}
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-blue-400 rounded-full blur-3xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-indigo-400 rounded-full blur-3xl opacity-20"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 pt-10 pb-10 md:pt-16 md:pb-16">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 lg:gap-10">
            {{-- Left: Title & Description --}}
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-blue-100 text-xs font-medium mb-4 backdrop-blur-md">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    Pelayanan Dibuka
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white tracking-tight mb-1 leading-tight">
                    LEMBAGA <span class="text-blue-300">BAHASA</span>
                </h1>
                <p class="text-blue-100/90 text-lg md:text-xl font-medium mb-1">
                    Universitas Muhammadiyah Metro
                </p>
                <p class="text-blue-200/70 text-base italic mb-6">
                    "Supports Your Success"
                </p>
                
                {{-- CTA Buttons --}}
                <div class="flex flex-wrap gap-3">
                    @guest
                        <a href="{{ route('register') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-blue-900 bg-white rounded-xl hover:bg-blue-50 transition-all shadow-lg shadow-blue-900/20 transform hover:-translate-y-0.5">
                            <i class="fas fa-user-plus mr-2"></i> Daftar
                        </a>
                        <a href="{{ route('login') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 rounded-xl hover:from-amber-600 hover:to-orange-600 transition-all shadow-lg shadow-orange-900/20 transform hover:-translate-y-0.5">
                            <i class="fas fa-sign-in-alt mr-2"></i> Login
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-teal-500 rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all shadow-lg shadow-teal-900/20 transform hover:-translate-y-0.5">
                            <i class="fas fa-user-circle mr-2"></i> {{ Auth::user()->name }}
                        </a>
                    @endguest
                </div>
            </div>
            
            {{-- Right: Quick Links Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-3 lg:w-72">
                <a href="{{ route('verification.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white hover:bg-white/20 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fas fa-check-circle text-lg"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Verifikasi Dokumen</p>
                        <p class="text-[11px] text-blue-200">Cek keaslian sertifikat</p>
                    </div>
                </a>
                <a href="{{ route('bl.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white hover:bg-white/20 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fas fa-headphones text-lg"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Basic Listening</p>
                        <p class="text-[11px] text-blue-200">Kelas & Sertifikat</p>
                    </div>
                </a>
                <a href="#berita" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/10 border border-white/20 backdrop-blur-md text-white hover:bg-white/20 transition-all group">
                    <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                        <i class="fas fa-calendar-alt text-lg"></i>
                    </div>
                    <div>
                        <p class="font-bold text-sm">Jadwal & Nilai EPT</p>
                        <p class="text-[11px] text-blue-200">Lihat pengumuman</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- SECTION: Jadwal / Nilai (Card Grid) & Berita (Split Layout) --}}
<section id="berita" class="bg-white py-2">
  <x-post.card-grid title="Jadwal Tes EPT Offline" :items="$schedules" :moreRoute="route('front.schedule')" emptyText="Belum ada jadwal." type="schedule"/>
  <x-post.card-grid title="Nilai Tes EPT" :items="$scores" :moreRoute="route('front.scores')" emptyText="Belum ada pengumuman nilai." type="scores"/>
  <!-- <x-post.section-split title="Berita Terbaru" :items="$news" :moreRoute="route('front.news')" emptyText="Belum ada berita."/> -->
</section>

{{-- SECTION: Layanan --}}
@if($services->isNotEmpty())
<section id="layanan" class="py-12 lg:py-16 bg-slate-50">
  <div class="max-w-4xl mx-auto px-4 lg:px-8">
    {{-- Header --}}
    <div class="text-center mb-8">
      <h2 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Layanan Kami</h2>
      <p class="text-gray-500 text-sm">Informasi lengkap tentang layanan yang tersedia</p>
    </div>
    
    {{-- Simple List --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">
      @foreach($services as $service)
        <a href="{{ route('layanan.show', $service->slug) }}" 
           class="flex items-center gap-4 p-4 hover:bg-gray-50 transition group">
          <div class="w-10 h-10 bg-teal-100 rounded-lg flex items-center justify-center text-teal-600 flex-shrink-0">
            <i class="fas fa-file-alt"></i>
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-gray-900 group-hover:text-teal-600 transition truncate">
              {{ $service->title }}
            </h3>
            <p class="text-sm text-gray-500 truncate">{{ $service->excerpt ?: 'Lihat detail layanan' }}</p>
          </div>
          <i class="fas fa-chevron-right text-gray-300 group-hover:text-teal-500 transition"></i>
        </a>
      @endforeach
    </div>
    
    {{-- See All --}}
    <div class="text-center mt-6">
      <a href="{{ route('layanan.index') }}" class="inline-flex items-center gap-2 text-teal-600 font-semibold hover:text-teal-700 transition text-sm">
        Lihat Semua Layanan
        <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>
@endif

{{-- TENTANG - Modern Bento Grid --}}
<section id="tentang" class="py-16 lg:py-24 bg-gradient-to-b from-white to-slate-50">
  <div class="max-w-7xl mx-auto px-4 lg:px-8">
    
    {{-- Section Header --}}
    <div class="text-center mb-12">
      <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 text-blue-700 text-sm font-semibold mb-4">
        <i class="fas fa-info-circle"></i>
        Tentang Kami
      </div>
      <h2 class="text-3xl lg:text-5xl font-black text-slate-900 mb-4">
        Lembaga <span class="text-blue-600">Bahasa</span> UM Metro
      </h2>
      <p class="text-slate-500 text-lg max-w-2xl mx-auto">
        Pusat pengembangan bahasa terpercaya dengan standar internasional
      </p>
    </div>

    {{-- Bento Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
      
      {{-- Main Card (2x2 on desktop) --}}
      <div class="col-span-2 row-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-700 p-8 text-white">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
        
        <div class="relative z-10">
          <div class="w-16 h-16 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center mb-6">
            <i class="fas fa-graduation-cap text-3xl"></i>
          </div>
          <h3 class="text-2xl lg:text-3xl font-bold mb-3">Mendukung Kesuksesan Anda</h3>
          <p class="text-blue-100 text-sm lg:text-base leading-relaxed mb-6">
            Sejak 2009, kami berkomitmen menyediakan layanan bahasa berkualitas tinggi untuk mendukung perjalanan akademik dan profesional mahasiswa serta masyarakat umum.
          </p>
          <div class="flex items-center gap-2 text-sm font-medium">
            <span class="px-3 py-1 bg-white/20 backdrop-blur rounded-full">"Supports Your Success"</span>
          </div>
        </div>
      </div>

      {{-- Stat Cards with Count Animation --}}
      <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 flex flex-col justify-center text-center hover:shadow-xl hover:-translate-y-1 transition-all">
        <div class="text-4xl lg:text-5xl font-black bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
          <span class="count-up" data-target="{{ $stats['tahun_pengalaman'] ?? 15 }}">0</span>+
        </div>
        <div class="text-slate-500 text-sm font-medium">Tahun Pengalaman</div>
      </div>
      
      <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 flex flex-col justify-center text-center hover:shadow-xl hover:-translate-y-1 transition-all">
        <div class="text-4xl lg:text-5xl font-black bg-gradient-to-r from-emerald-500 to-teal-500 bg-clip-text text-transparent mb-2">
          <span class="count-up" data-target="{{ $stats['alumni'] ?? 0 }}">0</span>+
        </div>
        <div class="text-slate-500 text-sm font-medium">Alumni Sukses</div>
      </div>
      
      <div class="bg-white rounded-2xl p-6 shadow-lg shadow-slate-200/50 border border-slate-100 flex flex-col justify-center text-center hover:shadow-xl hover:-translate-y-1 transition-all">
        <div class="text-4xl lg:text-5xl font-black bg-gradient-to-r from-amber-500 to-orange-500 bg-clip-text text-transparent mb-2">
          <span class="count-up" data-target="{{ $stats['instruktur'] ?? 0 }}">0</span>+
        </div>
        <div class="text-slate-500 text-sm font-medium">Instruktur & Penerjemah</div>
      </div>
      
      <div class="bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl p-6 shadow-lg flex flex-col justify-center text-center text-white hover:shadow-xl hover:-translate-y-1 transition-all">
        <div class="text-4xl lg:text-5xl font-black mb-2">A+</div>
        <div class="text-amber-100 text-sm font-medium">Akreditasi</div>
      </div>

      {{-- Features Row --}}
      <div class="col-span-2 lg:col-span-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-slate-900 rounded-2xl p-5 flex items-center gap-4 text-white hover:bg-slate-800 transition-colors">
          <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shrink-0">
            <i class="fas fa-certificate text-lg"></i>
          </div>
          <div>
            <div class="font-bold text-sm">Tersertifikasi</div>
            <div class="text-slate-400 text-xs">Standar Internasional</div>
          </div>
        </div>
        
        <div class="bg-slate-900 rounded-2xl p-5 flex items-center gap-4 text-white hover:bg-slate-800 transition-colors">
          <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center shrink-0">
            <i class="fas fa-users text-lg"></i>
          </div>
          <div>
            <div class="font-bold text-sm">Tim Profesional</div>
            <div class="text-slate-400 text-xs">Tenaga Ahli Berpengalaman</div>
          </div>
        </div>
        
        <div class="bg-slate-900 rounded-2xl p-5 flex items-center gap-4 text-white hover:bg-slate-800 transition-colors">
          <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center shrink-0">
            <i class="fas fa-headset text-lg"></i>
          </div>
          <div>
            <div class="font-bold text-sm">Layanan Responsif</div>
            <div class="text-slate-400 text-xs">Siap Membantu Anda</div>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

{{-- VIDEO PROFIL --}}
<section id="profil" class="py-12 lg:py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 lg:px-8 text-center">
    <h2 class="text-3xl lg:text-4xl font-bold mb-4">Profil Lembaga Bahasa</h2>
    <p class="text-gray-600 text-lg lg:text-xl mb-10 max-w-3xl mx-auto">
      Kenali lebih dekat layanan dan fasilitas Lembaga Bahasa UM Metro
    </p>
    <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-gray-900 max-w-5xl mx-auto" style="aspect-ratio: 16/9;">
      <iframe class="absolute inset-0 w-full h-full"
        src="https://www.youtube.com/embed/MBWXzhED58Y"
        title="Profil Lembaga Bahasa UM Metro"
        loading="lazy"
        referrerpolicy="strict-origin-when-cross-origin"
        allowfullscreen></iframe>
    </div>
  </div>
</section>

{{-- KONTAK --}}
<section id="kontak" class="py-12 lg:py-20">
  <div class="max-w-7xl mx-auto px-4 lg:px-8 text-center">
    <h2 class="text-3xl lg:text-4xl font-bold mb-6">Hubungi Kami</h2>
    <p class="text-gray-600 text-lg mb-12">Siap membantu Anda dengan layanan terbaik</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
      <div class="bg-white rounded-2xl p-6 shadow-lg">
        <div class="w-16 h-16 bg-um-blue rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-map-marker-alt text-white text-xl"></i>
        </div>
        <h3 class="font-bold text-lg mb-2">Alamat Kampus</h3>
        <p class="text-gray-600">Jalan Gatot Subroto No. 100 Yosodadi Kota Metro</p>
        <p class="text-gray-600">Lampung, Indonesia</p>
        <p class="text-sm text-um-blue mt-2 font-medium">Kampus 3 UM Metro</p>
      </div>
      <div class="bg-white rounded-2xl p-6 shadow-lg">
        <div class="w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-phone text-white text-xl"></i>
        </div>
        <h3 class="font-bold text-lg mb-2">Telepon & WhatsApp</h3>
        <p class="text-gray-600">(0725) 42445</p>
        <p class="text-gray-600">087790740408</p>
        <p class="text-sm text-green-600 mt-2 font-medium">Layanan 08:00–16:00 WIB</p>
      </div>
      <div class="bg-white rounded-2xl p-6 shadow-lg">
        <div class="w-16 h-16 bg-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
          <i class="fas fa-envelope text-white text-xl"></i>
        </div>
        <h3 class="font-bold text-lg mb-2">Email</h3>
        <p class="text-gray-600">info@ummetro.ac.id</p>
        <p class="text-gray-600">lembagabahasa@ummetro.ac.id</p>
        <p class="text-sm text-purple-600 mt-2 font-medium">Respon dalam 24 jam</p>
      </div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
// Count-up animation with Intersection Observer
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.count-up');
    
    const animateCounter = (counter) => {
        const target = parseInt(counter.dataset.target);
        const duration = 2000; // 2 seconds
        const start = 0;
        const increment = target / (duration / 16);
        let current = start;
        
        const updateCounter = () => {
            current += increment;
            if (current < target) {
                counter.textContent = Math.floor(current);
                requestAnimationFrame(updateCounter);
            } else {
                counter.textContent = target;
            }
        };
        
        updateCounter();
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => observer.observe(counter));
});
</script>
@endpush
