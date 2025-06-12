<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
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
<body class="bg-gradient-to-r from-red-900 via-red-800 to-yellow-900 flex items-center justify-center min-h-screen text-white">

    <div class="text-center p-8">
        <div class="w-24 h-24 text-red-300 mx-auto mb-6">
            <i class="fas fa-shield-halved fa-5x"></i>
        </div>
        <h1 class="text-8xl lg:text-9xl font-bold text-gray-200 mb-4">403</h1>
        <h2 class="text-2xl lg:text-4xl font-semibold mb-6">Akses Ditolak</h2>
        <p class="text-lg text-red-200 mb-8 max-w-md mx-auto">
            Maaf, Anda tidak memiliki izin yang cukup untuk mengakses halaman ini.
        </p>
        
        {{-- Tombol ini akan mengarahkan pengguna yang sudah login ke dasbor mereka --}}
        <a href="{{ route('filament.admin.pages.2') }}" 
           class="inline-block bg-white hover:bg-gray-200 text-red-800 px-8 py-3 rounded-full font-bold text-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dasbor
        </a>
    </div>

</body>
</html>