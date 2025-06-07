<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Auth Page' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js']) {{-- kalau pakai Vite --}}
    @filamentStyles
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>

    @filamentScripts
</body>
</html>
