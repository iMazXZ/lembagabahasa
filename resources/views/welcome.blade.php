<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembaga Bahasa UM Metro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'um-blue': '#1e40af',
                        'um-green': '#059669',
                        'um-gold': '#f59e0b',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white text-gray-900">

    <!-- <div class="flex items-center justify-center bg-red-700 text-center p-3 font-medium text-white">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <p>Aplikasi ini masih dalam tahap <b>Beta Testing</b>. Beberapa fitur mungkin belum berfungsi dengan baik.</p>
    </div>   -->

    <!-- Desktop/Mobile Navbar -->
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
                
                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center space-x-6">
                    <a href="#beranda" class="text-gray-700 hover:text-um-blue font-medium transition-colors">Beranda</a>
                    <a href="#layanan" class="text-gray-700 hover:text-um-blue font-medium transition-colors">Layanan</a>
                    <a href="#tentang" class="text-gray-700 hover:text-um-blue font-medium transition-colors">Tentang</a>
                    <a href="#kontak" class="text-gray-700 hover:text-um-blue font-medium transition-colors">Kontak</a>
                    @guest
                        <a href="{{ route('filament.admin.auth.login') }}" class="bg-gradient-to-r from-um-gold to-purple-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all duration-300 font-medium">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login
                        </a>
                    @else
                        <a href="{{ route('filament.admin.pages.2') }}" class="flex items-center space-x-2 bg-gradient-to-r from-um-green to-teal-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all duration-300 font-medium">
                            <i class="fas fa-user-circle"></i>
                            <span>{{ Auth::user()->name }}</span>
                        </a>

                        <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                            @csrf
                            <button type="submit" class="bg-gradient-to-r from-red-500 to-orange-500 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all duration-300 font-medium">
                                <i class="fas fa-sign-out-alt"></i>Logout
                            </button>
                        </form>
                    @endguest
                </div>

                <!-- Mobile Menu Button -->
                <button id="menuToggle" class="lg:hidden p-2 rounded-lg hover:bg-gray-100">
                    <i class="fas fa-bars text-gray-600"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="lg:hidden hidden bg-white border-t px-4 py-3">
            <div class="space-y-3">
                <a href="#beranda" class="block py-2 text-gray-700 hover:text-um-blue">Beranda</a>
                <a href="#layanan" class="block py-2 text-gray-700 hover:text-um-blue">Layanan</a>
                <a href="#tentang" class="block py-2 text-gray-700 hover:text-um-blue">Tentang</a>
                <a href="#kontak" class="block py-2 text-gray-700 hover:text-um-blue">Kontak</a>
                @guest
                <a href="{{ route('filament.admin.auth.login') }}" class="flex items-center space-x-2 bg-gradient-to-r from-um-blue to-purple-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all duration-300 font-medium">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </a>
                @else
                <a href="{{ route('filament.admin.pages.2') }}" class="flex items-center space-x-2 bg-gradient-to-r from-um-green to-teal-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all duration-300 font-medium">
                    <i class="fas fa-user-circle"></i>
                     <span>{{ Auth::user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                    @csrf
                    <button type="submit" class="bg-gradient-to-r from-red-500 to-orange-500 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all duration-300 font-medium">
                        <i class="fas fa-sign-out-alt"></i>Logout
                    </button>
                </form>
                @endguest
            </div>
        </div>
    </nav>

    <div class="bg-gradient-to-r from-blue-900 via-blue-800 to-purple-900 py-16 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 text-center text-white">
            <h1 data-aos="fade-up" class="text-4xl lg:text-7xl font-bold mb-6 lg:mb-8 leading-tight">
                LEMBAGA BAHASA<br>
                <span class="text-sm lg:text-2xl text-blue-200">UNIVERSITAS MUHAMMADIYAH METRO</span>
            </h1>
            
            <div data-aos="fade-up" data-aos-delay="100" class="mb-8 lg:mb-12">
                @guest
                    <a href="{{ route('filament.admin.auth.login') }}" class="inline-block bg-um-gold hover:bg-yellow-500 text-white px-8 py-4 lg:px-12 lg:py-6 rounded-xl font-bold text-lg lg:text-xl transition-colors">
                        <i class="fas fa-sign-in mr-3"></i>Masuk ke Akun
                    </a>
                @else
                    <a href="{{ route('filament.admin.pages.2') }}" class="inline-block bg-um-green hover:bg-green-600 text-white px-8 py-4 lg:px-12 lg:py-6 rounded-xl font-bold text-lg lg:text-xl transition-colors">
                        <i class="fas fa-user mr-3"></i>Dashboard Saya
                    </a>
                @endguest
            </div>

            <!-- Service Info Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 mt-12 lg:mt-20">
                <div data-aos="fade-up" data-aos-delay="200" class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 lg:p-8 text-center">
                    <div class="w-16 h-16 lg:w-20 lg:h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clock text-2xl lg:text-3xl text-white"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-bold text-um-gold mb-2">Waktu Pelayanan</h3>
                    <p class="text-sm lg:text-base text-blue-100 mb-1">Senin-Ahad, Pukul 08:00-16:00 WIB</p>
                    <p class="text-xs lg:text-sm text-blue-200">Pendaftaran Online Buka 24 Jam</p>
                </div>

                <!-- Pendaftaran On Desk -->
                <div data-aos="fade-up" data-aos-delay="300" class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 lg:p-8 text-center">
                    <div class="w-16 h-16 lg:w-20 lg:h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trophy text-2xl lg:text-3xl text-white"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-bold text-um-gold mb-2">Pendaftaran On Desk</h3>
                    <p class="text-sm lg:text-base text-blue-100 mb-1">Kampus 3 UM Metro</p>
                    <p class="text-xs lg:text-sm text-blue-200">Jalan Gatot Subroto No. 100 Yosodadi Kota Metro</p>
                </div>

                <!-- Bantuan Layanan -->
                <div data-aos="fade-up" data-aos-delay="400" class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 lg:p-8 text-center">
                    <div class="w-16 h-16 lg:w-20 lg:h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-headset text-2xl lg:text-3xl text-white"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-bold text-um-gold mb-2">Bantuan Layanan</h3>
                    <p class="text-sm lg:text-base text-blue-100 mb-1">Whatsapp : 085269813879</p>
                    <p class="text-xs lg:text-sm text-blue-200">Email: info@lemabagabahasa.site</p>
                </div>
            </div>
        </div>
    </div>

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

                <!-- Enhanced Stats -->
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

    <!-- Services Section - Enhanced -->
    <section id="layanan" class="py-12 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div data-aos="fade-up" class="text-center mb-12 lg:mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-4 lg:mb-6">Layanan Unggulan Kami</h2>
                <p class="text-gray-600 text-lg lg:text-xl max-w-2xl mx-auto">Dua layanan utama yang telah terpercaya melayani ribuan klien dengan standar internasional</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                <!-- EPT Service -->
                <div data-aos="fade-right" data-aos-delay="100" class="bg-white rounded-2xl lg:rounded-3xl p-8 lg:p-10 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow">
                    <div class="flex items-start space-x-6">
                        <div class="flex-1">
                            <h3 class="text-xl lg:text-2xl font-bold mb-3 lg:mb-4">English Proficiency Test</h3>
                            <p class="text-gray-600 mb-6 lg:mb-8 leading-relaxed">
                                Uji kemampuan bahasa Inggris dengan standar internasional. 
                                Dapatkan sertifikat resmi untuk keperluan akademik dan profesional.
                            </p>
                            <div class="space-y-3 mb-6 lg:mb-8">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-check text-um-blue mr-3"></i>
                                    <span>Sertifikat resmi terakreditasi</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-check text-um-blue mr-3"></i>
                                    <span>Tes berbasis komputer modern</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-check text-um-blue mr-3"></i>
                                    <span>Hasil cepat dan akurat</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-check text-um-blue mr-3"></i>
                                    <span>Standar internasional TOEFL/IELTS</span>
                                </div>
                            </div>
                            <a href="{{ route('filament.admin.resources.ept.index') }}" class="inline-flex items-center bg-um-blue text-white px-6 py-3 lg:px-8 lg:py-4 rounded-xl font-medium hover:bg-blue-700 transition-colors text-lg">
                                <i class="fas fa-user-plus mr-2"></i>Daftar EPT Sekarang
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Translation Service -->
                <div data-aos="fade-left" data-aos-delay="100" class="bg-white rounded-2xl lg:rounded-3xl p-8 lg:p-10 shadow-xl border border-gray-100 hover:shadow-2xl transition-shadow">
                    <div class="flex items-start space-x-6">
                        <div class="flex-1">
                            <h3 class="text-xl lg:text-2xl font-bold mb-3 lg:mb-4">Penerjemahan Dokumen</h3>
                            <p class="text-gray-600 mb-6 lg:mb-8 leading-relaxed">
                                Layanan penerjemahan profesional dengan akurasi tinggi. 
                                Didukung tim translator berpengalaman dan bersertifikat.
                            </p>
                            <div class="space-y-3 mb-6 lg:mb-8">
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-check text-purple-600 mr-3"></i>
                                    <span>Translator tersertifikasi internasional</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-check text-purple-600 mr-3"></i>
                                    <span>Berbagai jenis dokumen</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-check text-purple-600 mr-3"></i>
                                    <span>Garansi akurasi tinggi</span>
                                </div>
                                <div class="flex items-center text-gray-600">
                                    <i class="fas fa-check text-purple-600 mr-3"></i>
                                    <span>Pengerjaan cepat & tepat waktu</span>
                                </div>
                            </div>
                            <a href="{{ route('filament.admin.resources.penerjemahan.index') }}" class="inline-flex items-center bg-purple-600 text-white px-6 py-3 lg:px-8 lg:py-4 rounded-xl font-medium hover:bg-purple-700 transition-colors text-lg">
                                <i class="fas fa-file-alt mr-2"></i>Mulai Terjemahkan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Why Choose Us - Enhanced -->
            <div data-aos="fade-up" data-aos-delay="200" class="mt-16 lg:mt-24 bg-gray-50 rounded-2xl lg:rounded-3xl p-8 lg:p-12">
                <h3 class="text-2xl lg:text-3xl font-bold text-center mb-8 lg:mb-12">Mengapa Memilih Lembaga Bahasa UM Metro?</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                    <div class="text-center">
                        <div class="w-16 h-16 lg:w-20 lg:h-20 bg-um-blue rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-award text-white text-xl lg:text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-lg mb-2">Terakreditasi</h4>
                        <p class="text-gray-600">Standar internasional dengan sertifikasi resmi</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 lg:w-20 lg:h-20 bg-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-white text-xl lg:text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-lg mb-2">Tim Ahli</h4>
                        <p class="text-gray-600">Profesional berpengalaman 15+ tahun</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 lg:w-20 lg:h-20 bg-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-white text-xl lg:text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-lg mb-2">Cepat & Tepat</h4>
                        <p class="text-gray-600">Layanan efisien dengan hasil berkualitas</p>
                    </div>
                    <div class="text-center">
                        <div class="w-16 h-16 lg:w-20 lg:h-20 bg-um-gold rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-star text-white text-xl lg:text-2xl"></i>
                        </div>
                        <h4 class="font-bold text-lg mb-2">Terpercaya</h4>
                        <p class="text-gray-600">Ribuan klien puas dengan layanan kami</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section - Enhanced -->
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

    <!-- Registration Section -->
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

    <!-- Contact Section - Enhanced -->
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
                    <p class="text-gray-600">WhatsApp: 085269813879</p>
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
                    <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-blue-600 text-white rounded-xl flex items-center justify-center hover:bg-blue-700 transition-colors shadow-lg">
                        <i class="fab fa-facebook-f text-xl lg:text-2xl"></i>
                    </a>
                    <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-pink-500 text-white rounded-xl flex items-center justify-center hover:bg-pink-600 transition-colors shadow-lg">
                        <i class="fab fa-instagram text-xl lg:text-2xl"></i>
                    </a>
                    <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-green-500 text-white rounded-xl flex items-center justify-center hover:bg-green-600 transition-colors shadow-lg">
                        <i class="fab fa-whatsapp text-xl lg:text-2xl"></i>
                    </a>
                    <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-blue-400 text-white rounded-xl flex items-center justify-center hover:bg-blue-500 transition-colors shadow-lg">
                        <i class="fab fa-twitter text-xl lg:text-2xl"></i>
                    </a>
                    <a href="#" class="w-12 h-12 lg:w-16 lg:h-16 bg-red-600 text-white rounded-xl flex items-center justify-center hover:bg-red-700 transition-colors shadow-lg">
                        <i class="fab fa-youtube text-xl lg:text-2xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="bg-gradient-to-r from-blue-900 to-purple-900 text-white py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                <!-- About -->
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

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Tautan Cepat</h4>
                    <ul class="space-y-2">
                        <li><a href="#beranda" class="text-blue-100 hover:text-um-gold transition-colors">Beranda</a></li>
                        <li><a href="#layanan" class="text-blue-100 hover:text-um-gold transition-colors">Layanan</a></li>
                        <li><a href="#tentang" class="text-blue-100 hover:text-um-gold transition-colors">Tentang</a></li>
                        <li><a href="#kontak" class="text-blue-100 hover:text-um-gold transition-colors">Kontak</a></li>
                    </ul>
                </div>

                <!-- Service Hours -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Jam Pelayanan</h4>
                    <p class="text-blue-100 text-sm mb-2">Senin-Ahad: 08:00-16:00 WIB</p>
                    <p class="text-blue-100 text-sm">Pendaftaran Online: 24 Jam</p>
                </div>

                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-bold mb-4">Kontak Kami</h4>
                    <p class="text-blue-100 text-sm mb-2">Jalan Gatot Subroto No. 100 Yosodadi Kota Metro</p>
                    <p class="text-blue-100 text-sm mb-2">WhatsApp: 085269813879</p>
                    <p class="text-blue-100 text-sm">Email: lembagabahasa@ummetro.ac.id</p>
                </div>
            </div>
            <div class="border-t border-blue-800 mt-8 pt-6 text-center">
                <p class="text-blue-200 text-sm">
                    © 2025 Lembaga Bahasa UM Metro. Hak cipta dilindungi.
                </p>
            </div>
        </div>
    </footer>

    <!-- Floating Action Button -->
    <a href="https://wa.me/6285269813879" target="_blank" class="fixed bottom-6 right-6 w-14 h-14 lg:w-16 lg:h-16 bg-green-500 text-white rounded-full flex items-center justify-center shadow-lg hover:bg-green-600 transition-colors z-50">
        <i class="fab fa-whatsapp text-xl lg:text-2xl"></i>
    </a>

    <!-- JavaScript for Interactivity -->
    <script>
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        
        menuToggle.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const target = document.querySelector(targetId);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    // Close mobile menu if open
                    mobileMenu.classList.add('hidden');
                }
            });
        });

        // Hide mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!menuToggle.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>

    {{-- Script untuk AOS --}}
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
      AOS.init({
        duration: 800,
        once: false,
      });
    </script>

</body>
</html>