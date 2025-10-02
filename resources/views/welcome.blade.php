@extends('layouts.front')
@section('title', 'Lembaga Bahasa UM Metro')

@section('content')

  {{-- HERO SECTION --}}
  <div class="relative bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 py-20 lg:py-32 overflow-hidden">
    {{-- Animated Background Elements --}}
    <div class="absolute inset-0 overflow-hidden">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-yellow-400/30 rounded-full blur-3xl animate-pulse"></div>
      <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-pink-400/30 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
      <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-cyan-400/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
      <div class="absolute top-20 right-1/4 w-64 h-64 bg-green-400/20 rounded-full blur-3xl animate-pulse" style="animation-delay: 1.5s;"></div>
    </div>

    {{-- Floating Shapes Animation --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-1/4 left-1/4 w-4 h-4 bg-yellow-300/40 rounded-full animate-bounce" style="animation-duration: 3s;"></div>
      <div class="absolute top-1/3 right-1/3 w-3 h-3 bg-pink-300/40 rounded-full animate-bounce" style="animation-duration: 4s; animation-delay: 0.5s;"></div>
      <div class="absolute bottom-1/4 right-1/4 w-5 h-5 bg-cyan-300/40 rounded-full animate-bounce" style="animation-duration: 3.5s; animation-delay: 1s;"></div>
      <div class="absolute bottom-1/3 left-1/3 w-4 h-4 bg-green-300/40 rounded-full animate-bounce" style="animation-duration: 4.5s; animation-delay: 1.5s;"></div>
    </div>

    {{-- Dot Pattern Overlay --}}
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 30px 30px;"></div>

    <div class="relative max-w-7xl mx-auto px-4 lg:px-8">
      {{-- Main Hero Content --}}
      <div class="text-center text-white mb-16">
        {{-- Badge --}}
        <div data-aos="fade-down" class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-6 py-3 mb-8">
          <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
          </span>
          <span class="text-sm font-semibold text-blue-100">Pelayanan Dibuka</span>
        </div>

        {{-- Main Title --}}
        <h1 data-aos="fade-up" class="text-4xl md:text-5xl lg:text-7xl font-extrabold mb-4 leading-tight">
          <span class="bg-clip-text text-transparent bg-gradient-to-r from-white via-blue-100 to-white">
            LEMBAGA BAHASA
          </span>
        </h1>
        
        <p data-aos="fade-up" data-aos-delay="100" class="text-lg md:text-xl lg:text-2xl text-blue-200 font-medium mb-3">
          Universitas Muhammadiyah Metro
        </p>
        
        <p data-aos="fade-up" data-aos-delay="150" class="text-base md:text-lg text-blue-300 max-w-2xl mx-auto mb-10">
          <em>"Supports Your Success"</em>
        </p>

        {{-- CTA Buttons --}}
        <div data-aos="fade-up" data-aos-delay="200" class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          @guest
            <a href="{{ route('filament.admin.auth.login') }}" 
              class="group relative inline-flex items-center gap-3 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white px-8 py-4 rounded-full font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
              <i class="fas fa-sign-in-alt"></i>
              <span>Masuk ke Akun</span>
              <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </a>
          @else
            <a href="{{ route('filament.admin.pages.2') }}" 
              class="group relative inline-flex items-center gap-3 bg-gradient-to-r from-green-500 to-teal-500 hover:from-green-600 hover:to-teal-600 text-white px-8 py-4 rounded-full font-bold text-lg shadow-xl hover:shadow-2xl hover:scale-105 transition-all duration-300">
              <i class="fas fa-user"></i>
              <span>Dashboard Saya</span>
              <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </a>
          @endguest

          <a href="#berita" 
            class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border-2 border-white/30 hover:bg-white/20 text-white px-8 py-4 rounded-full font-semibold text-lg transition-all duration-300 hover:scale-105">
            <span>Cek Jadwal dan Nilai</span>
            <i class="fas fa-arrow-down animate-bounce"></i>
          </a>
        </div>
      </div>

      {{-- Info Cards (Ringkas) --}}
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
        {{-- Card 1: Waktu Pelayanan --}}
        <div data-aos="fade-up" data-aos-delay="250" 
        class="group relative bg-white/15 backdrop-blur-md border border-white/30 rounded-2xl p-6 hover:bg-white/20 hover:border-white/40 transition-all duration-300 hover:scale-105 hover:shadow-2xl text-center">
          <div class="absolute inset-0 bg-gradient-to-br from-yellow-400/20 to-orange-400/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
          <div class="relative flex flex-col items-center">
        <div class="w-14 h-14 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all">
          <i class="fas fa-clock text-xl text-white"></i>
        </div>
        <h3 class="text-lg font-bold text-yellow-300 mb-3">Waktu Pelayanan</h3>
        <div class="space-y-2 text-sm text-blue-100 w-full">
          <div class="flex items-center justify-center gap-2 bg-white/10 rounded-lg py-2 px-3">
            <i class="fas fa-calendar-alt text-yellow-300 text-xs"></i>
            <span>Senin-Kamis 08:00-16:00</span>
          </div>
          <div class="flex items-center justify-center gap-2 bg-white/10 rounded-lg py-2 px-3">
            <i class="fas fa-calendar-alt text-yellow-300 text-xs"></i>
            <span>Jumat 08:00-11:30</span>
          </div>
          <div class="mt-3 pt-3 border-t border-white/20">
            <div class="bg-green-500/20 border border-green-400/30 rounded-lg py-1.5 px-3">
          <p class="text-xs font-semibold">
            <i class="fas fa-globe text-green-300 mr-1"></i>
            Online 24/7
          </p>
            </div>
          </div>
        </div>
          </div>
        </div>

        {{-- Card 2: Lokasi --}}
        <div data-aos="fade-up" data-aos-delay="300" 
            class="group relative bg-white/15 backdrop-blur-md border border-white/30 rounded-2xl p-6 hover:bg-white/20 hover:border-white/40 transition-all duration-300 hover:scale-105 hover:shadow-2xl text-center">
          <div class="absolute inset-0 bg-gradient-to-br from-pink-400/20 to-purple-400/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
          
          <div class="relative flex flex-col items-center">
            <div class="w-14 h-14 bg-gradient-to-br from-pink-500 to-purple-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all">
              <i class="fas fa-map-marker-alt text-xl text-white"></i>
            </div>
            
            <h3 class="text-lg font-bold text-pink-300 mb-3">Pendaftaran On Desk</h3>
            
            <div class="space-y-2 text-sm text-blue-100 w-full">
              <div class="flex items-center justify-center gap-2 bg-white/10 rounded-lg py-2 px-3">
                <i class="fas fa-university text-pink-300 text-xs"></i>
                <span class="font-semibold">Kampus 3 UM Metro</span>
              </div>
              <div class="mt-3 pt-3 border-t border-white/20">
                <a href="#kontak" class="inline-flex items-center gap-2 text-xs text-pink-200 hover:text-pink-300 transition-colors font-semibold">
                  <i class="fas fa-directions"></i>
                  Lihat Peta Lokasi
                </a>
              </div>
            </div>
          </div>
        </div>

        {{-- Card 3: Kontak --}}
        <div data-aos="fade-up" data-aos-delay="350" 
            class="group relative bg-white/15 backdrop-blur-md border border-white/30 rounded-2xl p-6 hover:bg-white/20 hover:border-white/40 transition-all duration-300 hover:scale-105 hover:shadow-2xl text-center">
          <div class="absolute inset-0 bg-gradient-to-br from-green-400/20 to-teal-400/20 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
          
          <div class="relative flex flex-col items-center">
            <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-teal-500 rounded-2xl flex items-center justify-center mb-4 shadow-lg group-hover:scale-110 group-hover:rotate-6 transition-all">
              <i class="fas fa-headset text-xl text-white"></i>
            </div>
            
            <h3 class="text-lg font-bold text-green-300 mb-3">Bantuan Layanan</h3>
            
            <div class="space-y-2 w-full">
              <a href="https://wa.me/6287790740408" target="_blank" 
                class="flex items-center justify-center gap-2 bg-white/10 hover:bg-green-500/30 rounded-lg py-2 px-3 transition-all group/link text-sm">
                <i class="fab fa-whatsapp text-green-300 text-lg"></i>
                <div class="text-left">
                  <p class="text-xs text-blue-200">WhatsApp</p>
                  <p class="font-bold text-white group-hover/link:text-green-300 transition-colors text-sm">087790740408</p>
                </div>
              </a>
              
              <a href="mailto:info@lembagabahasa.site" 
                class="flex items-center justify-center gap-2 bg-white/10 hover:bg-yellow-500/30 rounded-lg py-2 px-3 transition-all group/link text-sm">
                <i class="fas fa-envelope text-yellow-300 text-lg"></i>
                <div class="text-left">
                  <p class="text-xs text-blue-200">Email</p>
                  <p class="font-bold text-white group-hover/link:text-yellow-300 transition-colors text-xs break-all">info@lembagabahasa.site</p>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- SECTION: Berita / Jadwal / Nilai (desain split) --}}
    <section id="berita" class="py-6 lg:py-10 bg-white">
      {{-- Jadwal Tes EPT Offline --}}
      <x-post.section-split
          title="Jadwal Tes EPT Offline"
          :items="$schedules"
          :moreRoute="route('front.schedule')"
          emptyText="Belum ada jadwal."
      />

      {{-- Nilai Tes EPT --}}
      <x-post.section-split
          title="Nilai Tes EPT"
          :items="$scores"
          :moreRoute="route('front.scores')"
          emptyText="Belum ada pengumuman nilai."
      />

      {{-- Berita --}}
      <x-post.section-split
          title="Berita Terbaru"
          :items="$news"
          :moreRoute="route('front.news')"
          emptyText="Belum ada berita."
      />
    </section>

  {{-- BERANDA (stats & CTA) --}}
  <section id="beranda" class="py-12 lg:py-20 bg-gradient-to-br from-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
      <div data-aos="zoom-in" class="text-center max-w-4xl mx-auto">
        <div class="w-16 h-16 lg:w-24 lg:h-24 bg-um-blue rounded-2xl flex items-center justify-center mx-auto mb-6 lg:mb-8">
          <i class="fas fa-university text-white text-2xl lg:text-4xl"></i>
        </div>

        <h2 class="text-2xl lg:text-4xl font-bold mb-4 lg:mb-6 leading-tight">
          <span class="text-um-blue">Pusat Unggulan</span><br>
          <span class="text-xl lg:text-3xl text-gray-600">English Proficiency Test & Penerjemahan</span>
        </h2>

        <p class="text-gray-600 mb-8 lg:mb-12 text-lg lg:text-xl leading-relaxed max-w-3xl mx-auto">
          Lembaga Bahasa Universitas Muhammadiyah Metro menyediakan layanan
          <span class="font-semibold text-um-blue">English Proficiency Test (EPT)</span>
          terakreditasi dan layanan penerjemahan profesional dengan standar internasional.
        </p>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-8 mb-8 lg:mb-12">
          <div class="bg-white rounded-xl lg:rounded-2xl p-4 lg:p-6 shadow-sm">
            <div class="text-2xl lg:text-3xl font-bold text-um-blue">1500+</div>
            <div class="text-sm lg:text-base text-gray-600">Peserta EPT</div>
          </div>
          <div class="bg-white rounded-xl lg:rounded-2xl p-4 lg:p-6 shadow-sm">
            <div class="text-2xl lg:text-3xl font-bold text-purple-600">800+</div>
            <div class="text-sm lg:text-base text-gray-600">Dokumen Terjemah</div>
          </div>
          <div class="bg-white rounded-xl lg:rounded-2xl p-4 lg:p-6 shadow-sm">
            <div class="text-2xl lg:text-3xl font-bold text-teal-600">15+</div>
            <div class="text-sm lg:text-base text-gray-600">Tahun Pengalaman</div>
          </div>
          <div class="bg-white rounded-xl lg:rounded-2xl p-4 lg:p-6 shadow-sm">
            <div class="text-2xl lg:text-3xl font-bold text-um-gold">A</div>
            <div class="text-sm lg:text-base text-gray-600">Akreditasi</div>
          </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-4 lg:gap-6 justify-center items-center">
          <a href="{{ route('filament.admin.auth.login') }}" class="w-full lg:w-auto bg-um-blue text-white px-8 py-4 lg:px-10 lg:py-4 rounded-xl font-medium hover:bg-blue-700 transition-colors text-lg">
            <i class="fas fa-rocket mr-2"></i>Mulai Sekarang
          </a>
          <a href="#layanan" class="w-full lg:w-auto border-2 border-um-blue text-um-blue px-8 py-4 lg:px-10 lg:py-4 rounded-xl font-medium hover:bg-um-blue hover:text-white transition-colors text-lg">
            <i class="fas fa-info-circle mr-2"></i>Pelajari Lebih Lanjut
          </a>
        </div>
      </div>
    </div>
  </section>

  {{-- TENTANG --}}
  <section id="tentang" class="py-12 lg:py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
      <div data-aos="fade-up" data-aos-delay="100" class="text-center mb-12 lg:mb-16">
        <div class="w-20 h-20 lg:w-24 lg:h-24 bg-um-blue rounded-3xl flex items-center justify-center mx-auto mb-6">
          <i class="fas fa-university text-white text-3xl lg:text-4xl"></i>
        </div>
        <h2 class="text-3xl lg:text-4xl font-bold mb-4 lg:mb-6">Tentang Lembaga Bahasa UM Metro</h2>
        <p class="text-gray-600 leading-relaxed max-w-4xl mx-auto text-lg lg:text-xl">
          Lembaga Bahasa Universitas Muhammadiyah Metro adalah pusat unggulan
          yang berkomitmen memberikan layanan bahasa berkualitas tinggi dengan standar internasional.
          Dengan pengalaman lebih dari 15 tahun, kami telah melayani ribuan klien dari berbagai kalangan.
        </p>
      </div>

      <div data-aos="fade-up" data-aos-delay="300" class="bg-white rounded-2xl lg:rounded-3xl p-8 lg:p-12 shadow-lg">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8 mb-8 lg:mb-12">
          <div class="text-center p-6 bg-um-blue/10 rounded-2xl">
            <div class="text-3xl lg:text-4xl font-bold text-um-blue mb-2">15+</div>
            <div class="text-gray-600 font-medium">Tahun Pengalaman</div>
          </div>
          <div class="text-center p-6 bg-purple-100 rounded-2xl">
            <div class="text-3xl lg:text-4xl font-bold text-purple-600 mb-2">25+</div>
            <div class="text-gray-600 font-medium">Instruktur Ahli</div>
          </div>
          <div class="text-center p-6 bg-teal-100 rounded-2xl">
            <div class="text-3xl lg:text-4xl font-bold text-teal-600 mb-2">5K+</div>
            <div class="text-gray-600 font-medium">Alumni Sukses</div>
          </div>
          <div class="text-center p-6 bg-yellow-100 rounded-2xl">
            <div class="text-3xl lg:text-4xl font-bold text-um-gold mb-2">A+</div>
            <div class="text-gray-600 font-medium">Akreditasi</div>
          </div>
        </div>

        <div class="flex flex-wrap gap-3 lg:gap-4 justify-center">
          <span class="bg-um-blue/10 text-um-blue px-4 py-2 lg:px-6 lg:py-3 rounded-full font-medium">
            <i class="fas fa-certificate mr-2"></i>Tersertifikasi Internasional
          </span>
          <span class="bg-purple-100 text-purple-600 px-4 py-2 lg:px-6 lg:py-3 rounded-full font-medium">
            <i class="fas fa-users mr-2"></i>Tim Profesional
          </span>
          <span class="bg-teal-100 text-teal-600 px-4 py-2 lg:px-6 lg:py-3 rounded-full font-medium">
            <i class="fas fa-clock mr-2"></i>Layanan 24/7 Online
          </span>
        </div>
      </div>
    </div>
  </section>

  {{-- REGISTRASI / CTA --}}
  <section id="registrasi" class="py-12 lg:py-20 bg-gradient-to-r from-um-blue to-purple-600">
    <div data-aos="fade-up" data-aos-delay="100" class="max-w-4xl mx-auto px-4 lg:px-8 text-center text-white">
      <h2 class="text-3xl lg:text-4xl font-bold mb-6 lg:mb-8">Siap Memulai Perjalanan Bahasa Anda?</h2>
      <p class="text-lg lg:text-xl mb-8 lg:mb-12 text-blue-100">
        Bergabunglah dengan ribuan peserta yang telah merasakan pengalaman belajar dan uji bahasa terbaik
      </p>
      <div data-aos="zoom-in" data-aos-delay="300" class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
        <a href="{{ route('filament.admin.resources.ept.index') }}" class="bg-white text-um-blue px-8 py-6 rounded-2xl font-bold text-lg hover:bg-gray-100 transition-colors flex items-center justify-center">
          <i class="fas fa-clipboard-check mr-3 text-xl"></i>
          Daftar EPT Sekarang
        </a>
        <a href="{{ route('filament.admin.resources.penerjemahan.index') }}" class="bg-um-gold text-white px-8 py-6 rounded-2xl font-bold text-lg hover:bg-yellow-500 transition-colors flex items-center justify-center">
          <i class="fas fa-language mr-3 text-xl"></i>
          Konsultasi Penerjemahan
        </a>
      </div>
    </div>
  </section>

  {{-- KONTAK --}}
  <section id="kontak" class="py-12 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 lg:px-8">
      <div data-aos="fade-up" data-aos-delay="100" class="text-center mb-12 lg:mb-16">
        <h2 class="text-3xl lg:text-4xl font-bold mb-4 lg:mb-6">Hubungi Kami</h2>
        <p class="text-gray-600 text-lg lg:text-xl">Siap membantu Anda dengan layanan terbaik</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 mb-12 lg:mb-16">
        <div data-aos="zoom-in" data-aos-delay="200" class="bg-white rounded-2xl p-6 lg:p-8 shadow-lg text-center hover:shadow-xl transition-shadow">
          <div class="w-16 h-16 bg-um-blue rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-map-marker-alt text-white text-xl"></i>
          </div>
          <h3 class="font-bold text-lg mb-2">Alamat Kampus</h3>
          <p class="text-gray-600">Jalan Gatot Subroto No. 100 Yosodadi Kota Metro</p>
          <p class="text-gray-600">Lampung, Indonesia</p>
          <p class="text-sm text-um-blue mt-2 font-medium">Kampus 3 UM Metro</p>
        </div>

        <div data-aos="zoom-in" data-aos-delay="300" class="bg-white rounded-2xl p-6 lg:p-8 shadow-lg text-center hover:shadow-xl transition-shadow">
          <div class="w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-phone text-white text-xl"></i>
          </div>
          <h3 class="font-bold text-lg mb-2">Telepon & WhatsApp</h3>
          <p class="text-gray-600">(0725) 42445</p>
          <p class="text-gray-600">WhatsApp: 087790740408</p>
          <p class="text-sm text-green-600 mt-2 font-medium">Layanan 08:00-16:00 WIB</p>
        </div>

        <div data-aos="zoom-in" data-aos-delay="400" class="bg-white rounded-2xl p-6 lg:p-8 shadow-lg text-center hover:shadow-xl transition-shadow">
          <div class="w-16 h-16 bg-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-envelope text-white text-xl"></i>
          </div>
          <h3 class="font-bold text-lg mb-2">Email</h3>
          <p class="text-gray-600">info@ummetro.ac.id</p>
          <p class="text-gray-600">lembagabahasa@ummetro.ac.id</p>
          <p class="text-sm text-purple-600 mt-2 font-medium">Respon dalam 24 jam</p>
        </div>
      </div>

      <div class="text-center">
        <h3 class="text-xl lg:text-2xl font-bold mb-6">Ikuti Media Sosial Kami</h3>
        <div class="flex justify-center space-x-4 lg:space-x-6">
          <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-blue-600 text-white rounded-xl flex items-center justify-center hover:bg-blue-700 transition-colors shadow-lg"><i class="fab fa-facebook-f text-xl lg:text-2xl"></i></a>
          <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-pink-500 text-white rounded-xl flex items-center justify-center hover:bg-pink-600 transition-colors shadow-lg"><i class="fab fa-instagram text-xl lg:text-2xl"></i></a>
          <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-green-500 text-white rounded-xl flex items-center justify-center hover:bg-green-600 transition-colors shadow-lg"><i class="fab fa-whatsapp text-xl lg:text-2xl"></i></a>
          <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-blue-400 text-white rounded-xl flex items-center justify-center hover:bg-blue-500 transition-colors shadow-lg"><i class="fab fa-twitter text-xl lg:text-2xl"></i></a>
          <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-red-600 text-white rounded-xl flex items-center justify-center hover:bg-red-700 transition-colors shadow-lg"><i class="fab fa-youtube text-xl lg:text-2xl"></i></a>
        </div>
      </div>
    </div>
  </section>

@endsection
