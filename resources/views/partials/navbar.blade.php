{{-- resources/views/partials/navbar.blade.php --}}
<nav class="bg-white/95 backdrop-blur border-b sticky top-0 z-50">
  @php
    // Helper active untuk route verifikasi
    $isVerification = request()->routeIs('verification.*');
    $linkBase   = 'text-gray-700 hover:text-um-blue font-medium transition-colors';
    $linkActive = $isVerification ? 'text-um-blue' : '';
  @endphp

  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center py-3 lg:py-4">

      {{-- BRAND --}}
      <a href="{{ route('front.home') }}" class="group flex items-center gap-1">
        <img
          src="{{ asset('images/logo-um.png') }}"
          alt="Logo UM Metro"
          class="h-12 w-12 md:h-12 md:w-12 object-contain"
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
        {{-- HAPUS: Profil & Kontak --}}
        {{-- TAMBAH: Basic Listening --}}
        <a href="{{ route('bl.index') }}" class="{{ $linkBase }}">Basic Listening</a>

        {{-- Verifikasi: halaman sendiri, bisa diberi state active --}}
        <a href="{{ route('verification.index') }}" class="{{ $linkBase }} {{ $linkActive }}">
          Verifikasi Dokumen
        </a>

        @guest
          <div class="flex items-center gap-3">
            {{-- Register (outline) --}}
            @if (Route::has('filament.admin.auth.register'))
              <a href="{{ route('filament.admin.auth.register') }}"
                class="inline-flex items-center gap-2 bg-white text-um-blue border border-um-blue px-5 py-2.5 rounded-full shadow-sm hover:bg-blue-50 transition">
                <i class="fas fa-user-plus" aria-hidden="true"></i>
                <span>Register</span>
              </a>
            @endif

            {{-- Login (gradient) --}}
            <a href="{{ route('filament.admin.auth.login') }}"
              class="inline-flex items-center gap-2 bg-gradient-to-r from-um-gold to-purple-600 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md hover:scale-[1.02] transition">
              <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
              <span>Login</span>
            </a>
          </div>
        @else
          @php
              $user = Auth::user();

              if ($user->hasRole('tutor')) {
                  $dashboardRoute = route('dashboard.pendaftar');
              } elseif ($user->hasRole('pendaftar')) {
                  $dashboardRoute = route('dashboard.pendaftar');
              } elseif ($user->hasAnyRole(['Admin', 'Staf Administrasi', 'Kepala Lembaga', 'Penerjemah'])) {
                  $dashboardRoute = route('filament.admin.pages.2');
              } else {
                  $dashboardRoute = route('front.home');
              }
          @endphp

          {{-- Tombol profil / dashboard --}}
          <a href="{{ $dashboardRoute }}"
            class="inline-flex items-center gap-2 bg-gradient-to-r from-um-green to-teal-600 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md hover:scale-[1.02] transition">
            <i class="fas fa-user-circle" aria-hidden="true"></i>
            <span class="truncate max-w-[180px]">{{ $user->name }}</span>
          </a>

          {{-- Logout --}}
          <form method="POST" action="{{ route('logout') }}">
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
      <button id="menuToggle"
              class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
              aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle menu">
        <i class="fas fa-bars text-gray-600 text-xl" aria-hidden="true"></i>
      </button>
    </div>
  </div>

  {{-- Mobile menu --}}
  <div id="mobileMenu" class="hidden lg:hidden bg-white border-t px-4 py-3">
    <div class="flex flex-col gap-2">
      {{-- HAPUS: Profil & Kontak --}}
      {{-- TAMBAH: Basic Listening --}}
      <a href="{{ route('bl.index') }}" class="py-2 {{ $linkBase }}">Basic Listening</a>
      <a href="{{ route('verification.index') }}" class="py-2 {{ $linkBase }} {{ $linkActive }}">Verifikasi Dokumen</a>

      @guest
        @if (Route::has('filament.admin.auth.register'))
          <a href="{{ route('filament.admin.auth.register') }}"
            class="mt-2 inline-flex items-center justify-center gap-2 bg-white text-um-blue border border-um-blue px-5 py-2.5 rounded-full shadow-sm hover:bg-blue-50 transition">
            <i class="fas fa-user-plus" aria-hidden="true"></i>
            <span>Register</span>
          </a>
        @endif

        <a href="{{ route('filament.admin.auth.login') }}"
          class="mt-2 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-um-gold to-purple-600 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md transition">
          <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
          <span>Login</span>
        </a>
      @else
        @php
            $user = Auth::user();

            if ($user->hasRole('tutor')) {
                $dashboardRoute = route('dashboard.pendaftar');
            } elseif ($user->hasRole('pendaftar')) {
                $dashboardRoute = route('dashboard.pendaftar');
            } elseif ($user->hasAnyRole(['Admin', 'Staf Administrasi', 'Kepala Lembaga', 'Penerjemah'])) {
                $dashboardRoute = route('filament.admin.pages.2');
            } else {
                $dashboardRoute = route('front.home');
            }
        @endphp

        <a href="{{ $dashboardRoute }}"
          class="mt-2 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-um-green to-teal-600 text-white px-5 py-2.5 rounded-full shadow-sm hover:shadow-md transition">
          <i class="fas fa-user-circle" aria-hidden="true"></i>
          <span class="truncate max-w-[180px]">{{ $user->name }}</span>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
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
