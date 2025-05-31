<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lembaga Bahasa UM Metro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-white to-white text-gray-900 min-h-screen flex flex-col">

    <!-- Navbar -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="text-2xl font-bold text-blue-700">
                Lembaga Bahasa
            </div>
            <div class="space-x-4 text-sm">
                @guest
                <a href="{{ route('filament.admin.auth.login') }}" class="text-blue-600 hover:text-blue-800 transition">Login</a>
                @else
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="text-blue-600 hover:text-blue-800 transition">
                        {{ Auth::user()->name }}
                    </a>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="flex-grow flex items-center justify-center text-center px-4">
        <div class="max-w-2xl">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-blue-800 mb-6 animate-fade-in">
                Selamat Datang di Lembaga Bahasa
            </h1>
            <p class="text-lg text-gray-700 mb-6">
                Tempat pendaftaran EPT, penerjemahan dokumen, dan layanan bahasa lainnya di UM Metro
            </p>
            @guest
                <a href="{{ route('filament.admin.auth.login') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Login atau Register</a>
            @else
                <a href="{{ route('filament.admin.pages.dashboard') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Masuk ke Dashboard
                </a>
            @endguest
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white shadow-inner py-4 text-center text-gray-500 text-sm">
        &copy; {{ date('Y') }} Lembaga Bahasa UM Metro. All rights reserved.
    </footer>

    <style>
        .animate-fade-in {
            animation: fade-in 1s ease-out forwards;
            opacity: 0;
        }

        @keyframes fade-in {
            to {
                opacity: 1;
            }
        }
    </style>
</body>
</html>
