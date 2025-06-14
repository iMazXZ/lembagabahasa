<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 - Halaman Kadaluwarsa</title>
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
<body class="bg-gradient-to-r from-gray-700 via-gray-800 to-gray-900 flex items-center justify-center min-h-screen text-white">

    <div class="text-center p-8">
        <div class="w-24 h-24 text-yellow-300 mx-auto mb-6">
            <i class="fas fa-hourglass-half fa-5x"></i>
        </div>
        <h1 class="text-8xl lg:text-9xl font-bold text-gray-200 mb-4">419</h1>
        <h2 class="text-2xl lg:text-4xl font-semibold mb-6">Halaman Kadaluwarsa</h2>
        <p class="text-lg text-gray-300 mb-8 max-w-lg mx-auto">
            Maaf, sesi Anda telah berakhir karena tidak ada aktivitas. Ini adalah langkah keamanan untuk melindungi data Anda.
        </p>
        
        <button 
           onclick="window.history.back();"
           class="inline-block bg-um-gold hover:bg-yellow-500 text-white px-8 py-3 rounded-full font-bold text-lg transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>Kembali & Coba Lagi
        </button>
    </div>

</body>
</html>