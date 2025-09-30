<footer class="bg-gradient-to-r from-blue-900 to-purple-900 text-white py-12 lg:py-16">
  <div class="max-w-7xl mx-auto px-4 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
      <div>
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
            <i class="fas fa-language text-um-blue text-lg"></i>
          </div>
          <div>
            <div class="text-lg font-bold">Lembaga Bahasa</div>
            <div class="text-sm text-blue-200">UM Metro</div>
          </div>
        </div>
        <p class="text-blue-100 text-sm leading-relaxed">
          Membangun masa depan melalui penguasaan bahasa dan komunikasi global. Kami berkomitmen memberikan layanan terbaik dengan standar internasional.
        </p>
      </div>
      <div>
        <h4 class="text-lg font-bold mb-4">Tautan Cepat</h4>
        <ul class="space-y-2">
          <li><a href="{{ route('front.home') }}#beranda" class="text-blue-100 hover:text-um-gold">Beranda</a></li>
          <li><a href="{{ route('front.home') }}#layanan" class="text-blue-100 hover:text-um-gold">Layanan</a></li>
          <li><a href="{{ route('front.home') }}#tentang" class="text-blue-100 hover:text-um-gold">Tentang</a></li>
          <li><a href="{{ route('front.home') }}#kontak" class="text-blue-100 hover:text-um-gold">Kontak</a></li>
        </ul>
      </div>
      <div>
        <h4 class="text-lg font-bold mb-4">Jam Pelayanan</h4>
        <p class="text-blue-100 text-sm mb-2">Senin-Ahad: 08:00-16:00 WIB</p>
        <p class="text-blue-100 text-sm">Pendaftaran Online: 24 Jam</p>
      </div>
      <div>
        <h4 class="text-lg font-bold mb-4">Kontak Kami</h4>
        <p class="text-blue-100 text-sm mb-2">Jalan Gatot Subroto No. 100 Yosodadi Kota Metro</p>
        <p class="text-blue-100 text-sm mb-2">WhatsApp: 085269813879</p>
        <p class="text-blue-100 text-sm">Email: lembagabahasa@ummetro.ac.id</p>
      </div>
    </div>
    <div class="border-t border-blue-800 mt-8 pt-6 text-center">
      <p class="text-blue-200 text-sm">© {{ now()->year }} Lembaga Bahasa UM Metro. Hak cipta dilindungi.</p>
    </div>
  </div>

  {{-- Floating WA --}}
  <a href="https://wa.me/6285269813879" target="_blank" class="fixed bottom-6 right-6 w-14 h-14 lg:w-16 lg:h-16 bg-green-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-green-600 transition-colors z-50">
    <i class="fab fa-whatsapp text-xl lg:text-2xl"></i>
  </a>
</footer>
