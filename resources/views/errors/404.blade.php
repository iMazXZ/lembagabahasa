<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
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
<body class="bg-gradient-to-r from-blue-900 via-blue-800 to-purple-900 flex items-center justify-center min-h-screen text-white">

    <div class="text-center p-8">
        <h1 class="text-8xl lg:text-9xl font-bold text-um-gold mb-4">404</h1>
        <h2 class="text-2xl lg:text-4xl font-semibold mb-6">Halaman Tidak Ditemukan</h2>
        <p class="text-lg text-blue-200 mb-8 max-w-md mx-auto">
            Maaf, halaman yang Anda cari tidak ada atau mungkin telah dipindahkan.
        </p>
        <a href="{{ url('/') }}" 
           class="inline-block bg-um-gold hover:bg-yellow-500 text-white px-8 py-3 rounded-full font-bold text-lg transition-colors">
            <i class="fas fa-home mr-2"></i>Kembali ke Halaman Utama
        </a>
    </div>

</body>
</html>