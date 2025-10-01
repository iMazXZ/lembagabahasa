<nav class="bg-white/95 backdrop-blur border-b sticky top-0 z-50">
  @php
    // helper active untuk route verifikasi
    $isVerification = request()->routeIs('verification.*');
    $linkBase = 'text-gray-700 hover:text-um-blue font-medium transition-colors';
    $linkActive = $isVerification ? 'text-um-blue' : '';
  @endphp

  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center py-3 lg:py-4">

      {{-- BRAND: background putih, tanpa lingkaran di balik logo --}}
      <a href="{{ route('front.home') }}" class="group flex items-center gap-1">
        <img
          src="{{ asset('images/logo-um.png') }}"
          alt="Logo UM Metro"
          class="h-9 w-9 md:h-12 md:w-12 object-contain"
          loading="lazy">

        <div class="leading-tight">
          <div class="font-extrabold tracking-tight text-[18px] md:text-[20px] text-slate-900">
            <span>Lembaga</span><span class="text-um-gold">Bahasa</span>
          </div>
          <div class="text-slate-500 text-[11px] md:text-[10px] -mt-0.5">
            Universitas Muhammadiyah Metro
          </div>
          <div class="italic text-slate-700 text-[12px] md:text-[10px]">
            Supports Your Success
          </div>
        </div>
      </a>

      {{-- Desktop menu --}}
      <div class="hidden lg:flex items-center gap-6">
        <a href="{{ route('front.home') }}#beranda" class="{{ $linkBase }}">Beranda</a>
        <a href="{{ route('front.home') }}#layanan" class="{{ $linkBase }}">Layanan</a>
        <a href="{{ route('front.home') }}#tentang" class="{{ $linkBase }}">Tentang</a>
        <a href="{{ route('front.home') }}#kontak" class="{{ $linkBase }}">Kontak</a>

        {{-- Verifikasi: halaman sendiri, bisa diberi state active --}}
        <a href="{{ route('verification.index') }}"
           class="{{ $linkBase }} {{ $linkActive }}">
          Verifikasi Dokumen
        </a>

        @guest
          <a href="{{ route('filament.admin.auth.login') }}"
             class="inline-flex items-center gap-2 bg-gradient-to-r from-um-gold to-purple-600 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md hover:scale-[1.02] transition">
            <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
            <span>Login</span>
          </a>
        @else
          <a href="{{ route('filament.admin.pages.2') }}"
             class="inline-flex items-center gap-2 bg-gradient-to-r from-um-green to-teal-600 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md hover:scale-[1.02] transition">
            <i class="fas fa-user-circle" aria-hidden="true"></i>
            <span class="truncate max-w-[180px]">{{ Auth::user()->name }}</span>
          </a>
          <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
            @csrf
            <button type="submit"
              class="inline-flex items-center gap-2 bg-gradient-to-r from-red-500 to-orange-500 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md hover:scale-[1.02] transition">
              <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
              <span>Logout</span>
            </button>
          </form>
        @endguest
      </div>

      {{-- Mobile toggle --}}
      <button id="menuToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100" aria-controls="mobileMenu" aria-expanded="false">
        <i class="fas fa-bars text-gray-600"></i>
        <span class="sr-only">Toggle menu</span>
      </button>
    </div>
  </div>

  {{-- Mobile menu --}}
  <div id="mobileMenu" class="lg:hidden hidden bg-white border-t px-4 py-3">
    <div class="flex flex-col gap-2">
      <a href="{{ route('front.home') }}#beranda" class="py-2 {{ $linkBase }}">Beranda</a>
      <a href="{{ route('front.home') }}#layanan" class="py-2 {{ $linkBase }}">Layanan</a>
      <a href="{{ route('front.home') }}#tentang" class="py-2 {{ $linkBase }}">Tentang</a>
      <a href="{{ route('front.home') }}#kontak" class="py-2 {{ $linkBase }}">Kontak</a>
      <a href="{{ route('verification.index') }}" class="py-2 {{ $linkBase }} {{ $linkActive }}">Verifikasi Dokumen</a>

      @guest
        <a href="{{ route('filament.admin.auth.login') }}"
           class="mt-2 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-um-gold to-purple-600 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md transition">
          <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
          <span>Login</span>
        </a>
      @else
        <a href="{{ route('filament.admin.pages.2') }}"
           class="mt-2 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-um-green to-teal-600 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md transition">
          <i class="fas fa-user-circle" aria-hidden="true"></i>
          <span class="truncate max-w-[180px]">{{ Auth::user()->name }}</span>
        </a>
        <form method="POST" action="{{ route('filament.admin.auth.logout') }}" class="mt-2">
          @csrf
          <button type="submit"
            class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-red-500 to-orange-500 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md transition">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            <span>Logout</span>
          </button>
        </form>
      @endguest
    </div>
  </div>
</nav>

{{-- Script kecil toggle + auto-close --}}
<script>
  (function(){
    const toggle = document.getElementById('menuToggle');
    const menu   = document.getElementById('mobileMenu');
    if(!toggle || !menu) return;

    toggle.addEventListener('click', () => {
      const open = menu.classList.contains('hidden');
      menu.classList.toggle('hidden');
      toggle.setAttribute('aria-expanded', String(open));
    });

    // auto-close saat klik link
    menu.querySelectorAll('a').forEach(a => {
      a.addEventListener('click', () => {
        menu.classList.add('hidden');
        toggle.setAttribute('aria-expanded', 'false');
      });
    });
  })();
</script>
