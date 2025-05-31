<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembaga Bahasa UM Metro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
<body class="bg-gray-50 text-gray-900 min-h-screen overflow-x-hidden">

    <!-- Animated Background -->
    <div class="fixed inset-0 z-0">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 via-purple-500/5 to-teal-500/10"></div>
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
            <div class="shape shape-5"></div>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="relative z-50 bg-white/80 backdrop-blur-md shadow-lg border-b border-white/20">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-um-blue to-purple-600 rounded-xl flex items-center justify-center transform hover:scale-110 transition-all duration-300">
                        <i class="fas fa-language text-white text-xl"></i>
                    </div>
                    <div>
                        <div class="text-xl font-bold bg-gradient-to-r from-um-blue to-purple-600 bg-clip-text text-transparent">
                            Lembaga Bahasa
                        </div>
                        <div class="text-xs text-gray-500 font-medium">UM Metro</div>
                    </div>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="hidden md:flex space-x-6 text-sm font-medium">
                        <a href="#" class="text-gray-700 hover:text-um-blue transition-colors duration-300 relative group">
                            Beranda
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-um-blue transition-all duration-300 group-hover:w-full"></span>
                        </a>
                        <a href="#layanan" class="text-gray-700 hover:text-um-blue transition-colors duration-300 relative group">
                            Layanan
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-um-blue transition-all duration-300 group-hover:w-full"></span>
                        </a>
                        <a href="#tentang" class="text-gray-700 hover:text-um-blue transition-colors duration-300 relative group">
                            Tentang
                            <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-um-blue transition-all duration-300 group-hover:w-full"></span>
                        </a>
                    </div>
                    @guest
                    <a href="{{ route('filament.admin.auth.login') }}" class="bg-gradient-to-r from-um-blue to-purple-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all duration-300 font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                    @else
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="flex items-center space-x-2 bg-gradient-to-r from-um-green to-teal-600 text-white px-6 py-2.5 rounded-full hover:shadow-lg hover:scale-105 transition-all duration-300 font-medium">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ Auth::user()->name }}</span>
                    </a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative z-10 min-h-screen flex items-center justify-center px-4 pt-20">
        <div class="max-w-6xl mx-auto text-center">
            <div class="hero-content">
                <div class="mb-8">
                    <div class="inline-block p-4 bg-white/10 backdrop-blur-sm rounded-2xl mb-6 animate-float">
                        <i class="fas fa-university text-6xl text-um-blue"></i>
                    </div>
                </div>
                
                <h1 class="text-5xl md:text-7xl font-black mb-6 leading-tight">
                    <span class="bg-gradient-to-r from-um-blue via-purple-600 to-teal-500 bg-clip-text text-transparent animate-gradient">
                        Lembaga Bahasa
                    </span>
                    <br>
                    <span class="text-3xl md:text-4xl font-semibold text-gray-700">
                        Universitas Muhammadiyah Metro
                    </span>
                </h1>
                
                <p class="text-xl md:text-2xl text-gray-600 mb-8 max-w-3xl mx-auto leading-relaxed">
                    Pusat unggulan untuk <span class="font-semibold text-um-blue">English Proficiency Test (EPT)</span>, 
                    layanan penerjemahan profesional, dan pengembangan kemampuan bahasa
                </p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    @guest
                    <a href="{{ route('filament.admin.auth.login') }}" class="group bg-gradient-to-r from-um-blue to-purple-600 text-white px-8 py-4 rounded-2xl hover:shadow-2xl hover:scale-105 transition-all duration-300 font-semibold text-lg flex items-center space-x-3">
                        <i class="fas fa-rocket group-hover:animate-bounce"></i>
                        <span>Mulai Sekarang</span>
                        <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <a href="#layanan" class="group bg-white/80 backdrop-blur-sm text-um-blue px-8 py-4 rounded-2xl hover:shadow-xl hover:scale-105 transition-all duration-300 font-semibold text-lg border border-um-blue/20 flex items-center space-x-3">
                        <i class="fas fa-info-circle"></i>
                        <span>Pelajari Lebih Lanjut</span>
                    </a>
                    @else
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="group bg-gradient-to-r from-um-green to-teal-600 text-white px-8 py-4 rounded-2xl hover:shadow-2xl hover:scale-105 transition-all duration-300 font-semibold text-lg flex items-center space-x-3">
                        <i class="fas fa-tachometer-alt group-hover:animate-pulse"></i>
                        <span>Dashboard Saya</span>
                        <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    @endguest
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 hover:bg-white/30 transition-all duration-300 border border-white/30">
                        <div class="text-3xl font-bold text-um-blue mb-2">1000+</div>
                        <div class="text-gray-600 font-medium">Peserta EPT</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 hover:bg-white/30 transition-all duration-300 border border-white/30">
                        <div class="text-3xl font-bold text-purple-600 mb-2">500+</div>
                        <div class="text-gray-600 font-medium">Dokumen Diterjemahkan</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-6 hover:bg-white/30 transition-all duration-300 border border-white/30">
                        <div class="text-3xl font-bold text-teal-600 mb-2">98%</div>
                        <div class="text-gray-600 font-medium">Tingkat Kepuasan</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="layanan" class="relative z-10 py-20 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6">
                    <span class="bg-gradient-to-r from-um-blue to-purple-600 bg-clip-text text-transparent">
                        Layanan Kami
                    </span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Solusi terpadu untuk kebutuhan bahasa dan sertifikasi Anda
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="group bg-white/80 backdrop-blur-sm rounded-3xl p-8 hover:shadow-2xl hover:scale-105 transition-all duration-500 border border-white/30 hover:border-um-blue/30">
                    <div class="w-16 h-16 bg-gradient-to-br from-um-blue to-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-clipboard-check text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">English Proficiency Test</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Uji kemampuan bahasa Inggris Anda dengan standar internasional yang diakui secara luas.
                    </p>
                    <a href="#" class="inline-flex items-center text-um-blue font-semibold group-hover:text-purple-600 transition-colors">
                        Daftar Sekarang
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

                <div class="group bg-white/80 backdrop-blur-sm rounded-3xl p-8 hover:shadow-2xl hover:scale-105 transition-all duration-500 border border-white/30 hover:border-purple-600/30">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-pink-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-language text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">Penerjemahan Dokumen</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Layanan penerjemahan profesional dengan akurasi tinggi untuk berbagai jenis dokumen.
                    </p>
                    <a href="#" class="inline-flex items-center text-purple-600 font-semibold group-hover:text-pink-500 transition-colors">
                        Mulai Terjemahkan
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>

                <div class="group bg-white/80 backdrop-blur-sm rounded-3xl p-8 hover:shadow-2xl hover:scale-105 transition-all duration-500 border border-white/30 hover:border-teal-500/30">
                    <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-chalkboard-teacher text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-800">Kursus Bahasa</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Program pembelajaran bahasa yang dirancang khusus untuk meningkatkan kemampuan komunikasi Anda.
                    </p>
                    <a href="#" class="inline-flex items-center text-teal-500 font-semibold group-hover:text-cyan-500 transition-colors">
                        Lihat Kursus
                        <i class="fas fa-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="tentang" class="relative z-10 py-20 px-4 bg-gradient-to-r from-um-blue/5 to-purple-600/5">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="order-2 lg:order-1">
                    <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-white/30">
                        <div class="w-20 h-20 bg-gradient-to-br from-um-blue to-purple-600 rounded-3xl flex items-center justify-center mb-8 mx-auto lg:mx-0">
                            <i class="fas fa-university text-white text-3xl"></i>
                        </div>
                        <h2 class="text-4xl font-bold mb-6 text-center lg:text-left">
                            <span class="bg-gradient-to-r from-um-blue to-purple-600 bg-clip-text text-transparent">
                                Tentang Kami
                            </span>
                        </h2>
                        <p class="text-gray-600 text-lg leading-relaxed mb-6">
                            Lembaga Bahasa Universitas Muhammadiyah Metro adalah pusat unggulan yang berkomitmen 
                            untuk memberikan layanan bahasa berkualitas tinggi dengan standar internasional.
                        </p>
                        <p class="text-gray-600 text-lg leading-relaxed mb-8">
                            Dengan tim profesional berpengalaman dan teknologi terdepan, kami siap membantu 
                            Anda mencapai tujuan akademik dan profesional melalui penguasaan bahasa yang optimal.
                        </p>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center space-x-2 bg-um-blue/10 px-4 py-2 rounded-full">
                                <i class="fas fa-certificate text-um-blue"></i>
                                <span class="text-sm font-medium text-gray-700">Tersertifikasi</span>
                            </div>
                            <div class="flex items-center space-x-2 bg-purple-100 px-4 py-2 rounded-full">
                                <i class="fas fa-users text-purple-600"></i>
                                <span class="text-sm font-medium text-gray-700">Tim Profesional</span>
                            </div>
                            <div class="flex items-center space-x-2 bg-teal-100 px-4 py-2 rounded-full">
                                <i class="fas fa-clock text-teal-600"></i>
                                <span class="text-sm font-medium text-gray-700">Layanan 24/7</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-um-blue/20 to-purple-600/20 rounded-3xl transform rotate-6"></div>
                        <div class="relative bg-white/90 backdrop-blur-sm rounded-3xl p-8 shadow-2xl border border-white/50">
                            <div class="grid grid-cols-2 gap-6">
                                <div class="text-center p-4 bg-gradient-to-br from-um-blue/10 to-purple-600/10 rounded-2xl">
                                    <div class="text-3xl font-bold text-um-blue mb-2">15+</div>
                                    <div class="text-sm text-gray-600 font-medium">Tahun Pengalaman</div>
                                </div>
                                <div class="text-center p-4 bg-gradient-to-br from-purple-600/10 to-pink-500/10 rounded-2xl">
                                    <div class="text-3xl font-bold text-purple-600 mb-2">50+</div>
                                    <div class="text-sm text-gray-600 font-medium">Instruktur Ahli</div>
                                </div>
                                <div class="text-center p-4 bg-gradient-to-br from-teal-500/10 to-cyan-500/10 rounded-2xl">
                                    <div class="text-3xl font-bold text-teal-500 mb-2">10k+</div>
                                    <div class="text-sm text-gray-600 font-medium">Alumni Sukses</div>
                                </div>
                                <div class="text-center p-4 bg-gradient-to-br from-um-gold/10 to-orange-500/10 rounded-2xl">
                                    <div class="text-3xl font-bold text-um-gold mb-2">A+</div>
                                    <div class="text-sm text-gray-600 font-medium">Akreditasi</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 bg-gradient-to-br from-gray-900 to-gray-800 text-white py-16">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-um-blue to-purple-600 rounded-xl flex items-center justify-center">
                            <i class="fas fa-language text-white text-xl"></i>
                        </div>
                        <div>
                            <div class="text-xl font-bold">Lembaga Bahasa</div>
                            <div class="text-sm text-gray-400">Universitas Muhammadiyah Metro</div>
                        </div>
                    </div>
                    <p class="text-gray-300 leading-relaxed mb-6">
                        Membangun masa depan yang lebih baik melalui penguasaan bahasa dan komunikasi global yang efektif.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-um-blue transition-colors duration-300 rounded-lg flex items-center justify-center">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-um-blue transition-colors duration-300 rounded-lg flex items-center justify-center">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-um-blue transition-colors duration-300 rounded-lg flex items-center justify-center">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-white/10 hover:bg-um-blue transition-colors duration-300 rounded-lg flex items-center justify-center">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Layanan</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><a href="#" class="hover:text-white transition-colors">English Proficiency Test</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Penerjemahan Dokumen</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Kursus Bahasa</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Konsultasi Bahasa</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4">Kontak</h4>
                    <ul class="space-y-3 text-gray-300">
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-map-marker-alt text-um-blue"></i>
                            <span>Jl. Ki Hajar Dewantara No. 116, Metro</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-phone text-um-blue"></i>
                            <span>(0725) 42445</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-envelope text-um-blue"></i>
                            <span>info@ummetro.ac.id</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Lembaga Bahasa Universitas Muhammadiyah Metro. Hak cipta dilindungi.</p>
            </div>
        </div>
    </footer>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            background: linear-gradient(45deg, rgba(30, 64, 175, 0.1), rgba(147, 51, 234, 0.1));
            border-radius: 50%;
            animation: float 20s infinite linear;
        }

        .shape-1 {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: -5s;
        }

        .shape-3 {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: -10s;
        }

        .shape-4 {
            width: 100px;
            height: 100px;
            top: 10%;
            right: 30%;
            animation-delay: -15s;
        }

        .shape-5 {
            width: 140px;
            height: 140px;
            top: 70%;
            left: 70%;
            animation-delay: -7s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.1;
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
                opacity: 0.3;
            }
        }

        .animate-float {
            animation: float-simple 3s ease-in-out infinite;
        }

        @keyframes float-simple {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .animate-gradient {
            background-size: 200% 200%;
            animation: gradient-shift 3s ease infinite;
        }

        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .hero-content {
            animation: hero-fade-in 1.2s ease-out forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes hero-fade-in {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #1e40af, #9333ea);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #1d4ed8, #a855f7);
        }
    </style>

</body>
</html>