@extends('layouts.front')

@section('title', 'Lembaga Bahasa UM Metro | EPT, Penerjemahan, Pelatihan Bahasa')

@section('meta')
  <meta name="description" content="Lembaga Bahasa UM Metro menyediakan layanan English Proficiency Test (EPT), penerjemahan dokumen, dan pelatihan Basic Listening untuk mahasiswa dan umum di Kota Metro, Lampung.">
  <meta name="keywords" content="Lembaga Bahasa UM Metro, EPT UM Metro, Penerjemahan, Basic Listening, Jadwal EPT, Nilai EPT, Pelatihan Bahasa Inggris">
  <meta name="author" content="Lembaga Bahasa UM Metro">
@endsection

@section('content')

{{-- HERO SECTION --}}
<section class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-indigo-700 to-purple-800 py-20 lg:py-28">
  <div aria-hidden="true" class="absolute inset-0 opacity-25 pointer-events-none">
    <div class="absolute -top-32 -right-24 w-80 h-80 rounded-full blur-3xl bg-yellow-300/30"></div>
    <div class="absolute -bottom-32 -left-24 w-80 h-80 rounded-full blur-3xl bg-pink-300/30"></div>
  </div>

  <div class="relative max-w-7xl mx-auto px-4 lg:px-8 text-center text-white">
    {{-- Status badge --}}
    <div class="flex justify-center mb-8">
      <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur border border-white/20 rounded-full px-5 py-2">
        <span class="text-sm font-semibold">Pelayanan Dibuka</span>
      </div>
    </div>

    <h1 class="text-4xl md:text-5xl lg:text-7xl font-extrabold mb-3 leading-tight">
      <span class="bg-clip-text text-transparent bg-gradient-to-r from-white via-blue-100 to-white">
        LEMBAGA BAHASA
      </span>
    </h1>
    <p class="text-lg md:text-xl lg:text-2xl text-blue-100 font-medium">Universitas Muhammadiyah Metro</p>
    <p class="text-base md:text-lg text-blue-200 italic mb-10">"Supports Your Success"</p>

    {{-- CTA: Register + Login --}}
    <div class="flex flex-row gap-3 justify-center items-center mb-10">
      @if (Route::has('filament.admin.auth.register'))
        <a href="{{ route('filament.admin.auth.register') }}"
           class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                  bg-white text-um-blue border border-um-blue
                  px-6 py-3.5 rounded-full font-semibold
                  shadow-md hover:shadow-lg sm:hover:scale-105 transition">
          <i class="fas fa-user-plus"></i>
          <span>Register</span>
        </a>
      @endif

      <a href="{{ route('filament.admin.auth.login') }}"
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                bg-gradient-to-r from-um-gold to-purple-600 text-white
                px-6 py-3.5 rounded-full font-semibold
                shadow-md hover:shadow-lg sm:hover:scale-105 transition">
        <i class="fas fa-sign-in-alt"></i>
        <span>Login</span>
      </a>
    </div>

    {{-- CTA sekunder --}}
    <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
      <a href="{{ route('verification.index') }}" 
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                bg-white/10 hover:bg-white/15 text-white
                px-6 py-3.5 rounded-full font-semibold shadow-md border border-white/20 backdrop-blur transition">
        <span>Verifikasi Dokumen</span>
        <i class="fas fa-check-circle"></i>
      </a>

      <a href="{{ route('bl.index') }}" 
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                bg-white/10 hover:bg-white/15 text-white
                px-6 py-3.5 rounded-full font-semibold shadow-md border border-white/20 backdrop-blur transition">
        <span>Basic Listening</span>
        <i class="fas fa-headphones"></i>
      </a>

      <a href="#berita" 
         class="w-full sm:w-auto inline-flex items-center justify-center gap-2
                bg-white/10 hover:bg-white/15 text-white
                px-6 py-3.5 rounded-full font-semibold shadow-md border border-white/20 backdrop-blur transition">
        <span>Cek Jadwal &amp; Nilai EPT</span>
        <i class="fas fa-arrow-down"></i>
      </a>
    </div>
  </div>
</section>

{{-- SECTION: Berita / Jadwal / Nilai --}}
<section id="berita" class="py-8 bg-white">
  <x-post.section-split title="Jadwal Tes EPT Offline" :items="$schedules" :moreRoute="route('front.schedule')" emptyText="Belum ada jadwal."/>
  <x-post.section-split title="Nilai Tes EPT" :items="$scores" :moreRoute="route('front.scores')" emptyText="Belum ada pengumuman nilai."/>
  <x-post.section-split title="Berita Terbaru" :items="$news" :moreRoute="route('front.news')" emptyText="Belum ada berita."/>
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

{{-- TENTANG --}}
<section id="tentang" class="py-12 lg:py-20 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 lg:px-8 text-center">
    <div class="w-20 h-20 bg-um-blue rounded-3xl flex items-center justify-center mx-auto mb-6">
      <i class="fas fa-university text-white text-3xl"></i>
    </div>
    <h2 class="text-3xl lg:text-4xl font-bold mb-4">Tentang Lembaga Bahasa UM Metro</h2>
    <p class="text-gray-600 text-lg max-w-4xl mx-auto mb-12">
      Lembaga Bahasa Universitas Muhammadiyah Metro adalah pusat unggulan
      yang berkomitmen memberikan layanan bahasa berkualitas tinggi dengan standar internasional.
    </p>

    <div class="bg-white rounded-2xl p-8 shadow-lg">
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="text-center p-6 bg-um-blue/10 rounded-2xl">
          <div class="text-3xl font-bold text-um-blue mb-2">15+</div>
          <div class="text-gray-600 font-medium">Tahun Pengalaman</div>
        </div>
        <div class="text-center p-6 bg-purple-100 rounded-2xl">
          <div class="text-3xl font-bold text-purple-600 mb-2">25+</div>
          <div class="text-gray-600 font-medium">Instruktur Ahli</div>
        </div>
        <div class="text-center p-6 bg-teal-100 rounded-2xl">
          <div class="text-3xl font-bold text-teal-600 mb-2">5K+</div>
          <div class="text-gray-600 font-medium">Alumni Sukses</div>
        </div>
        <div class="text-center p-6 bg-yellow-100 rounded-2xl">
          <div class="text-3xl font-bold text-um-gold mb-2">A+</div>
          <div class="text-gray-600 font-medium">Akreditasi</div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 justify-center">
        <span class="bg-um-blue/10 text-um-blue px-4 py-2 rounded-full font-medium">
          <i class="fas fa-certificate mr-2"></i>Tersertifikasi Internasional
        </span>
        <span class="bg-purple-100 text-purple-600 px-4 py-2 rounded-full font-medium">
          <i class="fas fa-users mr-2"></i>Tim Profesional
        </span>
        <span class="bg-teal-100 text-teal-600 px-4 py-2 rounded-full font-medium">
          <i class="fas fa-clock mr-2"></i>Layanan Online 24/7 Online
        </span>
      </div>
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
