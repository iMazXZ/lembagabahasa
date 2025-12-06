{{-- resources/views/bl/survey_success.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kuesioner Selesai 🎉</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background: #f7f8fb;
    }

    /* ==== Animated Gradient Background ==== */
    .hero-success {
      background: linear-gradient(150deg, #eef2ff 0%, #e0f2fe 50%, #ecfdf3 100%);
      position: relative;
      overflow: hidden;
    }

    /* ==== Floating Shapes ==== */
    .floating-shape {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(20px);
      animation: float 20s ease-in-out infinite;
    }
    .shape-1 { width: 180px; height: 180px; top: -60px; left: -30px; animation-delay: 0s; opacity: 0.15; }
    .shape-2 { width: 220px; height: 220px; top: 55%; right: -60px; animation-delay: 5s; opacity: 0.12; }
    .shape-3 { width: 140px; height: 140px; bottom: -30px; left: 42%; animation-delay: 10s; opacity: 0.12; }
    @keyframes float {
      0%, 100% { transform: translate(0, 0) rotate(0deg); }
      25% { transform: translate(50px, -50px) rotate(90deg); }
      50% { transform: translate(0, -100px) rotate(180deg); }
      75% { transform: translate(-50px, -50px) rotate(270deg); }
    }

    /* ==== Glowing Text ==== */
    .glow-text {
      text-shadow: 0 0 20px rgba(255,255,255,0.5), 0 0 40px rgba(255,255,255,0.3);
      animation: glow 2s ease-in-out infinite alternate;
    }
    @keyframes glow {
      from { text-shadow: 0 0 20px rgba(255,255,255,0.5), 0 0 40px rgba(255,255,255,0.3); }
      to { text-shadow: 0 0 30px rgba(255,255,255,0.8), 0 0 60px rgba(255,255,255,0.5); }
    }

    /* ==== Cards with Glassmorphism ==== */
    .glass-card {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      box-shadow: 0 14px 34px rgba(15, 23, 42, 0.12);
      transition: all 0.2s ease;
    }
    .glass-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
    }

    /* ==== Chips dengan animasi ==== */
    .chip {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-size: 0.875rem;
      font-weight: 600;
      padding: 0.625rem 1rem;
      border-radius: 9999px;
      border: 2px solid;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    .chip::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255,255,255,0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }
    .chip:hover::before {
      width: 300px;
      height: 300px;
    }
    .chip-green { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); color: #065f46; border-color: #10b981; }
    .chip-amber { background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%); color: #92400e; border-color: #f59e0b; }
    .chip-blue { background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%); color: #1e40af; border-color: #3b82f6; }
    .chip-purple { background: linear-gradient(135deg, #e9d5ff 0%, #c4b5fd 100%); color: #6b21a8; border-color: #8b5cf6; }

    /* ==== Confetti ==== */
    .confetti-piece {
      position: fixed;
      top: -20px;
      width: 10px;
      height: 16px;
      opacity: 0.95;
      animation: confetti-fall linear forwards;
      will-change: transform, top, left, opacity;
      z-index: 9999;
    }
    @keyframes confetti-fall {
      0% {
        transform: translateY(-20vh) rotate(0deg);
        opacity: 1;
      }
      100% {
        transform: translateY(120vh) rotate(720deg);
        opacity: 0.9;
      }
    }

    /* ==== Success Icon Animation ==== */
    .success-icon {
      animation: bounceIn 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
    @keyframes bounceIn {
      0% { transform: scale(0) rotate(-180deg); opacity: 0; }
      50% { transform: scale(1.2) rotate(20deg); }
      100% { transform: scale(1) rotate(0deg); opacity: 1; }
    }

    /* ==== Pulse Animation for Badges ==== */
    .pulse-badge {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: .6; }
    }

    /* ==== Button Styles ==== */
    .btn-primary {
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    .btn-primary::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255,255,255,0.3);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }
    .btn-primary:hover::before {
      width: 400px;
      height: 400px;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 40px rgba(99, 102, 241, 0.4);
    }

    .btn-secondary {
      background: white;
      border: 2px solid #e0e7ff;
      transition: all 0.3s ease;
    }
    .btn-secondary:hover {
      background: #f5f3ff;
      border-color: #8b5cf6;
      transform: translateY(-2px);
    }

    /* ==== Fade In Animation ==== */
    .fade-in {
      animation: fadeInUp 0.6s ease-out forwards;
      opacity: 0;
    }
    .fade-in-delay-1 { animation-delay: 0.1s; }
    .fade-in-delay-2 { animation-delay: 0.2s; }
    .fade-in-delay-3 { animation-delay: 0.3s; }
    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* ==== Stats Counter Animation ==== */
    .stat-number {
      font-size: 3rem;
      font-weight: 800;
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .content-wrap{
      width: 100%;
      max-width: 720px;
      margin: 0 auto;
    }
  </style>
</head>
<body class="antialiased">
  
  @php
    use App\Models\User;
    use App\Models\BasicListeningSurvey;
    use App\Models\BasicListeningSurveyResponse;
    use Illuminate\Support\Facades\Route as RouteFacade;

    $uid = auth()->id();

    // URL sertifikat: gunakan route('bl.certificate.download') bila ada
    $downloadUrl = RouteFacade::has('bl.certificate.download') ? route('bl.certificate.download') : null;
    $previewUrl  = $downloadUrl ? ($downloadUrl . '?inline=1') : null;
  @endphp

  {{-- HERO SECTION --}}
  <div class="hero-success min-h-screen flex items-center justify-center relative">
    {{-- Floating Shapes --}}
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>

    <div class="relative z-10 w-full px-4 py-12 content-wrap">
      
      {{-- Success Icon --}}
      <div class="text-center mb-10">
        <div class="success-icon inline-flex items-center justify-center w-20 h-20 md:w-24 md:h-24 bg-white rounded-full shadow-xl mb-4">
          <i class="fa-solid fa-trophy text-4xl md:text-5xl text-yellow-500"></i>
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-3">
          Selamat! 🎉
        </h1>
        <p class="text-base md:text-lg text-slate-700 font-medium max-w-2xl mx-auto">
          Semua kuesioner wajib telah berhasil diselesaikan. Berikut langkah selanjutnya untuk akses sertifikat atau kembali ke Basic Listening.
        </p>
      </div>

      {{-- Main Cards Container --}}
      <div class="grid grid-cols-1 gap-6 mb-10">

        {{-- Certificate Card --}}
        <div class="glass-card rounded-2xl p-6 fade-in fade-in-delay-2">
          @if($canDownloadCertificate && ($meetsPassing ?? false))
            @if($downloadUrl)
              <div class="mb-4 text-center">
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-4">
                  <p class="font-semibold text-green-900">Sertifikat siap diunduh.</p>
                </div>

                <div class="space-y-2">
                  <a href="{{ $downloadUrl }}"
                     class="btn-primary w-full flex items-center justify-center gap-2 rounded-lg px-5 py-3 text-sm font-bold text-white shadow">
                    <i class="fa-solid fa-download text-lg"></i>
                    <span>Unduh Sertifikat</span>
                  </a>
                  <a href="{{ $previewUrl }}"
                     class="btn-secondary w-full flex items-center justify-center gap-2 rounded-lg px-5 py-3 text-sm font-bold text-indigo-700">
                    <i class="fa-regular fa-file-pdf text-lg"></i>
                    <span>Preview di Browser</span>
                  </a>
                </div>

                <p class="text-xs text-gray-500 mt-3">
                  Jika unduhan tidak berjalan, gunakan tombol preview lalu unduh dari viewer PDF.
                </p>
              </div>
            @else
              <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-sm text-amber-800">
                Route <code class="bg-amber-100 px-2 py-1 rounded">bl.certificate.download</code> belum tersedia. Hubungi admin.
              </div>
            @endif
          @else
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-center">
              <p class="font-semibold text-rose-800 mb-1">Sertifikat belum tersedia.</p>
              @if(!($meetsPassing ?? false))
                <p class="text-xs text-rose-600">Kembali ke Halaman Awal</p>
              @else
                <p class="text-xs text-rose-600">Pastikan nilai attendance/final sudah lengkap.</p>
              @endif
              <a href="{{ route('bl.index') }}" class="btn-secondary w-full flex items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-bold text-indigo-700 mt-3">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Dashboard</span>
              </a>
            </div>
          @endif
        </div>

      </div>

      {{-- Action Buttons --}}
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4 fade-in fade-in-delay-3">
        <a href="{{ route('bl.index') }}" 
           class="btn-secondary inline-flex items-center gap-3 rounded-xl px-8 py-4 text-base font-bold">
          <i class="fa-solid fa-arrow-left"></i>
          Kembali ke Basic Listening
        </a>
      </div>

    </div>
  </div>

  {{-- Confetti Script --}}
  <script>
    // Confetti party (heavy)
    (function(){
      const colors = ['#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6','#14b8a6','#f97316','#ec4899'];
      const count = 160;
      const minDur = 5;
      const maxDur = 10;

      function spawnPiece(){
        const el = document.createElement('div');
        el.className = 'confetti-piece';
        const size = Math.random() * 10 + 8;
        el.style.width = (size * 0.6) + 'px';
        el.style.height = size + 'px';
        el.style.left = Math.random() * 100 + 'vw';
        el.style.background = colors[Math.floor(Math.random() * colors.length)];
        el.style.animationDuration = (Math.random() * (maxDur - minDur) + minDur) + 's';
        el.style.animationDelay = (Math.random() * 1) + 's';
        el.style.transform = `rotate(${Math.random() * 360}deg)`;
        el.style.borderRadius = (Math.random() > 0.5 ? '50%' : '2px');
        document.body.appendChild(el);
        el.addEventListener('animationend', () => el.remove());
      }

      // Initial burst
      for(let i = 0; i < count; i++) {
        setTimeout(() => spawnPiece(), i * 15);
      }

      // Ongoing bursts
      setInterval(() => {
        for(let i = 0; i < 40; i++) {
          setTimeout(() => spawnPiece(), i * 25);
        }
      }, 2500);
    })();
  </script>

</body>
</html>
