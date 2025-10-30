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
    body { font-family: 'Inter', sans-serif; }

    /* ==== Animated Gradient Background ==== */
    .hero-success {
      background: linear-gradient(-45deg, #059669, #10b981, #0ea5e9, #3b82f6, #8b5cf6);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
      position: relative;
      overflow: hidden;
    }
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* ==== Floating Shapes ==== */
    .floating-shape {
      position: absolute;
      border-radius: 50%;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(20px);
      animation: float 20s ease-in-out infinite;
    }
    .shape-1 { width: 300px; height: 300px; top: -100px; left: -50px; animation-delay: 0s; }
    .shape-2 { width: 400px; height: 400px; top: 50%; right: -100px; animation-delay: 5s; }
    .shape-3 { width: 200px; height: 200px; bottom: -50px; left: 40%; animation-delay: 10s; }
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
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255,255,255,0.5) inset;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .glass-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(255,255,255,0.7) inset;
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

    <div class="relative z-10 w-full max-w-6xl mx-auto px-4 py-12">
      
      {{-- Success Icon --}}
      <div class="text-center mb-8">
        <div class="success-icon inline-flex items-center justify-center w-32 h-32 bg-white rounded-full shadow-2xl mb-6">
          <i class="fa-solid fa-trophy text-6xl text-yellow-500"></i>
        </div>
        <h1 class="text-5xl md:text-7xl font-extrabold text-white glow-text mb-4">
          Selamat! 🎉
        </h1>
        <p class="text-xl md:text-2xl text-white/90 font-medium">
          Semua kuesioner wajib telah berhasil diselesaikan
        </p>
      </div>

      {{-- Main Cards Container --}}
      <div class="grid grid-cols-1 gap-6 mb-8">

        {{-- Certificate Card --}}
        <div class="glass-card rounded-3xl p-8 fade-in fade-in-delay-2">
          <div class="flex items-center gap-3 mb-6">
            <i class="fa-solid fa-certificate text-3xl text-yellow-500"></i>
            <h2 class="text-2xl font-bold text-gray-900">Sertifikat Kamu</h2>
          </div>

          @if($canDownloadCertificate)
            @if($downloadUrl)
              <div class="mb-6">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-4 mb-4">
                  <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-check text-2xl text-green-600"></i>
                    <div>
                      <p class="font-bold text-green-900 mb-1">Selamat! 🎊</p>
                      <p class="text-sm text-green-700">
                        Kamu telah memenuhi syarat untuk mengunduh sertifikat Basic Listening.
                      </p>
                    </div>
                  </div>
                </div>

                {{-- Certificate Preview Image --}}
                <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 mb-4 border-2 border-indigo-100">
                  <div class="text-center">
                    <i class="fa-solid fa-award text-6xl text-indigo-600 mb-3"></i>
                    <p class="text-sm font-semibold text-gray-700">Sertifikat Basic Listening</p>
                    <p class="text-xs text-gray-500 mt-1">Siap untuk diunduh</p>
                  </div>
                </div>

                <div class="space-y-3">
                  <a href="{{ $downloadUrl }}"
                     class="btn-primary w-full flex items-center justify-center gap-3 rounded-xl px-6 py-4 text-base font-bold text-white shadow-lg relative z-10">
                    <i class="fa-solid fa-download text-xl"></i>
                    <span class="relative z-10">Unduh Sertifikat</span>
                  </a>
                  <a href="{{ $previewUrl }}"
                     class="btn-secondary w-full flex items-center justify-center gap-3 rounded-xl px-6 py-4 text-base font-bold text-indigo-700">
                    <i class="fa-regular fa-file-pdf text-xl"></i>
                    Lihat Preview di Browser
                  </a>
                </div>

                <p class="text-xs text-gray-500 mt-4 text-center">
                  <i class="fa-solid fa-info-circle"></i>
                  Jika download tidak otomatis, gunakan tombol preview lalu unduh dari viewer PDF
                </p>
              </div>
            @else
              <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-4">
                <div class="flex items-start gap-3">
                  <i class="fa-solid fa-triangle-exclamation text-2xl text-amber-600"></i>
                  <div>
                    <p class="font-bold text-amber-900 mb-1">Route Belum Tersedia</p>
                    <p class="text-sm text-amber-700">
                      Route <code class="bg-amber-100 px-2 py-1 rounded">bl.certificate.download</code> belum dikonfigurasi. 
                      Hubungi administrator untuk mengaktifkan fitur download.
                    </p>
                  </div>
                </div>
              </div>
            @endif
          @else
            <div class="bg-orange-50 border-2 border-orange-200 rounded-2xl p-4">
              <div class="flex items-start gap-3">
                <i class="fa-solid fa-lock text-2xl text-orange-600"></i>
                <div>
                  <p class="font-bold text-orange-900 mb-1">Sertifikat Belum Tersedia</p>
                  <p class="text-sm text-orange-700">
                    Nilai attendance atau final belum lengkap. Pastikan semua persyaratan terpenuhi untuk mendapatkan sertifikat.
                  </p>
                </div>
              </div>
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
    // ===== Confetti Animation =====
    (function(){
      const colors = ['#f59e0b','#10b981','#3b82f6','#ef4444','#8b5cf6','#14b8a6','#f97316','#ec4899'];
      const count = 150;
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

      // Spawn confetti on load
      for(let i = 0; i < count; i++) {
        setTimeout(() => spawnPiece(), i * 30);
      }

      // Add more confetti periodically
      setInterval(() => {
        for(let i = 0; i < 20; i++) {
          setTimeout(() => spawnPiece(), i * 50);
        }
      }, 3000);
    })();

    // ===== Stats Counter Animation =====
    document.addEventListener('DOMContentLoaded', () => {
      const statNumbers = document.querySelectorAll('.stat-number');
      statNumbers.forEach(stat => {
        const target = parseInt(stat.textContent);
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
          current += increment;
          if (current >= target) {
            stat.textContent = target;
            clearInterval(timer);
          } else {
            stat.textContent = Math.floor(current);
          }
        }, 20);
      });
    });
  </script>

</body>
</html>