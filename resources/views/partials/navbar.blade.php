<nav class="bg-white shadow-sm border-b sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 lg:px-4">
    <div class="flex justify-between items-center py-3 lg:py-4">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 lg:w-12 lg:h-12 bg-um-gold rounded-lg flex items-center justify-center">
          <i class="fas fa-language text-white text-lg lg:text-xl"></i>
        </div>
        <div>
          <div class="text-lg lg:text-xl font-bold text-um-gold">Lembaga Bahasa</div>
          <div class="text-xs lg:text-sm text-gray-500">Universitas Muhammadiyah Metro</div>
        </div>
      </div>

      {{-- Desktop menu --}}
      <div class="hidden lg:flex items-center space-x-6">
        <a href="{{ route('front.home') }}#beranda" class="text-gray-700 hover:text-um-blue font-medium">Beranda</a>
        <a href="{{ route('front.home') }}#layanan" class="text-gray-700 hover:text-um-blue font-medium">Layanan</a>
        <a href="{{ route('front.home') }}#tentang" class="text-gray-700 hover:text-um-blue font-medium">Tentang</a>
        <a href="{{ route('front.home') }}#kontak" class="text-gray-700 hover:text-um-blue font-medium">Kontak</a>

        @guest
          <a href="{{ route('filament.admin.auth.login') }}" class="bg-gradient-to-r from-um-gold to-purple-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all font-medium">
            <i class="fas fa-sign-in-alt mr-2"></i>Login
          </a>
        @else
          <a href="{{ route('filament.admin.pages.2') }}" class="flex items-center space-x-2 bg-gradient-to-r from-um-green to-teal-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all font-medium">
            <i class="fas fa-user-circle"></i><span>{{ Auth::user()->name }}</span>
          </a>
          <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
            @csrf
            <button type="submit" class="bg-gradient-to-r from-red-500 to-orange-500 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all font-medium">
              <i class="fas fa-sign-out-alt"></i>Logout
            </button>
          </form>
        @endguest
      </div>

      {{-- Mobile toggle --}}
      <button id="menuToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
        <i class="fas fa-bars text-gray-600"></i>
      </button>
    </div>
  </div>

  {{-- Mobile menu --}}
  <div id="mobileMenu" class="lg:hidden hidden bg-white border-t px-4 py-3">
    <div class="space-y-3">
      <a href="{{ route('front.home') }}#beranda" class="block py-2 text-gray-700 hover:text-um-blue">Beranda</a>
      <a href="{{ route('front.home') }}#layanan" class="block py-2 text-gray-700 hover:text-um-blue">Layanan</a>
      <a href="{{ route('front.home') }}#tentang" class="block py-2 text-gray-700 hover:text-um-blue">Tentang</a>
      <a href="{{ route('front.home') }}#kontak" class="block py-2 text-gray-700 hover:text-um-blue">Kontak</a>
      @guest
        <a href="{{ route('filament.admin.auth.login') }}" class="flex items-center space-x-2 bg-gradient-to-r from-um-blue to-purple-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all font-medium">
          <i class="fas fa-sign-in-alt mr-2"></i>Login
        </a>
      @else
        <a href="{{ route('filament.admin.pages.2') }}" class="flex items-center space-x-2 bg-gradient-to-r from-um-green to-teal-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all font-medium">
          <i class="fas fa-user-circle"></i><span>{{ Auth::user()->name }}</span>
        </a>
        <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
          @csrf
          <button type="submit" class="bg-gradient-to-r from-red-500 to-orange-500 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all font-medium">
            <i class="fas fa-sign-out-alt"></i>Logout
          </button>
        </form>
      @endguest
    </div>
  </div>
</nav>
