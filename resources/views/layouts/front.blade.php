<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title', 'Lembaga Bahasa UM Metro')</title>

  {{-- CSS/Libs global --}}
  <script src="https://cdn.tailwindcss.com?plugins=typography,line-clamp"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  {{-- Tailwind custom color --}}
  <script>
    tailwind.config = {
      theme: { extend: { colors: { 'um-blue':'#1e40af','um-green':'#059669','um-gold':'#f59e0b' } } }
    }
  </script>

  @stack('styles')
  <style>
    /* bikin elemen melebar sampai tepi viewport */
    .full-bleed {
      position: relative;
      left: 50%;
      right: 50%;
      margin-left: -50vw;
      margin-right: -50vw;
      width: 100vw;
    }

    /* --- Perbaikan khusus mobile --- */
    @media (max-width: 640px) {
      /* Biar tabel tidak “dipipihkan” — paksa lebar minimum sehingga muncul scroll */
      .prose .tbl-wrap table {
        min-width: 1000px;       /* jumlah kolom kamu banyak, 900–1100px enak */
        table-layout: auto;
      }

      /* Default: jangan pecah per huruf, jangan bungkus baris */
      .prose .tbl-wrap th,
      .prose .tbl-wrap td {
        white-space: nowrap;
        word-break: keep-all;
      }

      /* Khusus kolom NAMA (kolom ke-2) & PRODI (kolom ke-6):
        boleh membungkus kata agar tidak kepanjangan */
      .prose .tbl-wrap th:nth-child(2),
      .prose .tbl-wrap td:nth-child(2),
      .prose .tbl-wrap th:nth-child(6),
      .prose .tbl-wrap td:nth-child(6) {
        white-space: normal;
        word-break: normal;
        max-width: 260px;        /* opsional, biar tetap rapi saat di-zoom */
      }

      /* Header sticky tidak terlalu penting di mobile, tapi kalau mau:
        kecilkan padding biar ringkas */
      .prose .tbl-wrap thead th,
      .prose .tbl-wrap tbody td {
        padding: 10px 12px;
        font-size: .95rem;
      }
    }

    /* Sedikit bantu desktop kecil (<= 768px) juga */
    @media (max-width: 768px) {
      .prose .tbl-wrap table { min-width: 920px; }
    }
  </style>
</head>
<body class="bg-white text-gray-900">

  {{-- Navbar global --}}
  @include('partials.navbar')

  {{-- Konten halaman --}}
  <main>
    @yield('content')
  </main>

  {{-- Footer global --}}
  @include('partials.footer')

  {{-- JS global --}}
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    AOS.init({ duration: 800, once: false });

    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    if (menuToggle && mobileMenu) {
      menuToggle.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
      document.addEventListener('click', (e) => {
        if (!menuToggle.contains(e.target) && !mobileMenu.contains(e.target)) mobileMenu.classList.add('hidden');
      });
    }
  </script>

  @stack('scripts')
</body>
</html>
