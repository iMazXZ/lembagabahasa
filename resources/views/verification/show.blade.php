{{-- resources/views/verification/show.blade.php --}}
@extends('layouts.front')

@section('title', $vm['title'] ?? 'Verifikasi Dokumen')

@push('styles')
<style>
  /* ========== STYLE RINGAN & MOBILE-FIRST (REPLACE YOUR <style>) ========== */
  @media (prefers-reduced-motion: reduce) {
    * { animation: none !important; transition: none !important; }
  }

  /* Hero sederhana */
  .hero-gradient { background: linear-gradient(135deg,#6d7ae0 0%,#7a59b6 100%); }

  /* Badge status */
  .status-badge{ display:inline-flex; align-items:center; gap:.5rem; font-weight:700; border-radius:999px; padding:.5rem 1rem; font-size:.95rem; }
  .badge-valid{ background:#10b981; color:#fff; }
  .badge-pending{ background:#f59e0b; color:#fff; }
  .badge-invalid{ background:#ef4444; color:#fff; }

  /* Card generik */
  .card{ background:#fff; border-radius:12px; border:1px solid #e5e7eb; box-shadow:0 4px 12px rgba(0,0,0,.05); }

  .info-label{ font-weight:600; color:#64748b; }
  .info-value{ color:#1f2937; font-weight:500; }

  /* Kode verifikasi */
  .verification-code{ background:#eef2ff; border:1px solid #c7d2fe; border-radius:10px; padding:.75rem; display:flex; align-items:center; justify-content:space-between; gap:.75rem; }
  .code-text{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; font-size:1rem; font-weight:700; color:#1e3a8a; letter-spacing:.3px; }

  /* Tombol */
  .btn{ display:inline-flex; align-items:center; gap:.5rem; padding:.625rem 1rem; border-radius:.75rem; font-weight:600; transition:transform .15s ease, box-shadow .15s ease; }
  .btn:focus{ outline:none; box-shadow:0 0 0 3px rgba(99,102,241,.2); }
  .btn-secondary{ background:#fff; color:#475569; border:1px solid #e2e8f0; }
  .btn-secondary:hover{ background:#f8fafc; }

  /* Input code */
  .input-code{ width:100%; padding:.65rem .9rem; border:1px solid #e2e8f0; border-radius:.65rem; font: 500 .9rem ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace; }
  .input-code:focus{ outline:none; border-color:#6d7ae0; box-shadow:0 0 0 3px rgba(109,122,224,.15); }

  /* Tabel minimal */
  .table-modern { border-radius:10px; border:1px solid #e5e7eb; overflow:hidden; }
  .table-modern thead { background:#6d7ae0; color:#fff; }
  .table-modern th, .table-modern td { padding:.8rem; }
  .table-modern tbody tr:hover{ background:#f8fafc; }

  /* Shake kecil untuk error input */
  @keyframes shake { 0%,100%{transform:translateX(0)} 20%,60%{transform:translateX(-6px)} 40%,80%{transform:translateX(6px)} }
  .animate-shake{ animation:shake .35s; }

  /* =================== MOBILE TUNING =================== */
  /* 1) Skala font global turun sedikit di HP agar semua elemen mengecil */
  @media (max-width: 480px){
    html { font-size: 15px; } /* default ~16px → 15px = -6% semua rem Tailwind mengecil */

    /* 2) Hero lebih ringkas */
    .hero-gradient { padding-top: 1.75rem !important; padding-bottom: 1.75rem !important; }
    .hero-gradient h1 { 
      /* paksa lebih kecil dari text-3xl bawaan */
      font-size: clamp(1.35rem, 5.2vw, 1.75rem); /* ~21–28px */
      line-height: 1.2;
      margin-bottom: .5rem !important;
    }
    .hero-gradient p { font-size: .95rem; }

    /* 3) Badge & ikon lebih kecil */
    .status-badge{ padding:.4rem .75rem; font-size:.85rem; }
    .status-badge svg{ width:1rem; height:1rem; }

    /* 4) Card & section padding dipangkas */
    .card{ border-radius:10px; }
    .card.p-6 { padding: 1rem !important; }
    .card.p-8 { padding: 1.25rem !important; }

    /* 5) Grid label-value lebih sempit */
    .grid[class*="grid-cols-[140px_1fr]"]{ grid-template-columns:110px 1fr !important; }
    .grid[class*="md:grid-cols-[160px_1fr]"]{ grid-template-columns:110px 1fr !important; }
    .grid.p-3{ padding: .6rem !important; }
    .info-label{ font-size:.9rem; }
    .info-value{ font-size:.95rem; }

    /* 6) Kode verifikasi & tombol copy */
    .verification-code{ padding:.6rem .65rem; border-radius:.6rem; }
    .code-text{ font-size:.95rem; letter-spacing:.2px; }
    .btn.btn-secondary{ padding:.45rem .7rem; border-radius:.6rem; }
    .btn svg{ width:1rem; height:1rem; }

    /* 7) Tabel */
    .table-modern th, .table-modern td { padding:.6rem; font-size:.95rem; }

    /* 8) Sidebar gap lebih rapat */
    .space-y-6 > * + * { margin-top: 1rem !important; }
  }

  /* Extra kecil (<=360px): kompres lagi sedikit */
  @media (max-width: 360px){
    html { font-size: 14px; } /* -12% dari default */
    .grid[class*="grid-cols-[140px_1fr]"],
    .grid[class*="md:grid-cols-[160px_1fr]"]{ grid-template-columns:100px 1fr !important; }
    .status-badge{ font-size:.8rem; }
  }
</style>
@endpush

@section('content')
@php
  // Tentukan URL PDF inline (publik) berdasarkan type + code
  $type = $vm['type'] ?? null;
  $code = $vm['verification_code'] ?? null;

  $pdfViewUrl = $vm['pdf_url']
      ?? (
        $code
          ? ($type === 'ept'
                ? route('verification.ept.pdf', ['code' => $code])
                : route('verification.penerjemahan.pdf', ['code' => $code]))
          : null
      );

  $status = $vm['status'] ?? 'INVALID';
  $badgeClass = $status === 'VALID' ? 'badge-valid' : ($status === 'PENDING' ? 'badge-pending' : 'badge-invalid');
  $label = $status === 'VALID' ? 'Terverifikasi' : ($status === 'PENDING' ? 'Menunggu Verifikasi' : 'Tidak Valid');
@endphp

{{-- Hero --}}
<section class="hero-gradient py-10 md:py-14">
  <div class="max-w-6xl mx-auto px-4">
    <div class="text-center text-white mb-2">
      <h1 class="text-3xl md:text-5xl font-bold mb-3 tracking-tight">
        {{ $vm['title'] ?? 'Verifikasi Dokumen' }}
      </h1>
    </div>

    <div class="flex flex-col items-center gap-2">
      <span class="status-badge {{ $badgeClass }}">
        @if($status === 'VALID')
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @elseif($status === 'PENDING')
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @else
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @endif
        {{ $label }}
      </span>

      @if(!empty($vm['reason']))
        <p class="text-white/90 text-sm md:text-base max-w-2xl text-center">{{ $vm['reason'] }}</p>
      @endif
    </div>
  </div>
</section>

{{-- Main --}}
<section class="py-8 md:py-12 bg-gradient-to-b from-gray-50 to-white">
  <div class="max-w-6xl mx-auto px-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      {{-- Kartu Utama --}}
      <div class="lg:col-span-2">
        <div class="card p-6 md:p-8">
          <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Detail Dokumen
          </h2>

          {{-- Info Grid --}}
          <div class="space-y-3 mb-6">
            <div class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-3 p-3 rounded-lg border border-gray-200 bg-white">
              <div class="info-label">Nama Pemohon</div><div class="info-value">{{ $vm['applicant_name'] ?? '-' }}</div>
            </div>
            <div class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-3 p-3 rounded-lg border border-gray-200 bg-white">
              <div class="info-label">NPM</div><div class="info-value">{{ $vm['srn'] ?? '-' }}</div>
            </div>
            <div class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-3 p-3 rounded-lg border border-gray-200 bg-white">
              <div class="info-label">Program Studi</div><div class="info-value">{{ $vm['prody'] ?? '-' }}</div>
            </div>
            <div class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-3 p-3 rounded-lg border border-gray-200 bg-white">
              <div class="info-label">Status</div><div class="info-value">{{ $vm['status_text'] ?? '-' }}</div>
            </div>

            @if(($vm['type'] ?? null) === 'penerjemahan')
              @php $done = $vm['done_at'] ?? null; @endphp
              <div class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-3 p-3 rounded-lg border border-gray-200 bg-white">
                <div class="info-label">Tanggal Selesai</div>
                <div class="info-value">@if($done) {{ $done->translatedFormat('d F Y, H:i') }} WIB @else - @endif</div>
              </div>
            @endif

            @if(($vm['type'] ?? null) === 'ept')
              <div class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-3 p-3 rounded-lg border border-gray-200 bg-white">
                <div class="info-label">Nomor Surat</div><div class="info-value">{{ $vm['nomor_surat'] ?? '-' }}</div>
              </div>
              <div class="grid grid-cols-[140px_1fr] md:grid-cols-[160px_1fr] gap-3 p-3 rounded-lg border border-gray-200 bg-white">
                <div class="info-label">Tanggal Surat</div><div class="info-value">{{ optional($vm['tanggal_surat'])->translatedFormat('d F Y') ?? '-' }}</div>
              </div>
            @endif
          </div>

          {{-- Kode Verifikasi --}}
          <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Kode Verifikasi</h3>
            <div class="verification-code">
              <span class="code-text" id="code-value">{{ $vm['verification_code'] ?? '-' }}</span>
              @if(!empty($vm['verification_code']))
                <button type="button" class="btn btn-secondary py-2 px-3 text-sm" data-copy="{{ $vm['verification_code'] }}">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                  Copy
                </button>
              @endif
            </div>
            <span class="sr-only" aria-live="polite" id="copy-status"></span>
          </div>

          {{-- Tautan PDF (inline) --}}
          @if(!empty($pdfViewUrl))
            <div class="mb-6">
              <a class="btn btn-secondary" href="{{ $pdfViewUrl }}" target="_blank" rel="noopener">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553 1.301A2 2 0 0121 13.224V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h5.776a2 2 0 011.414.586L15 6v4z"/>
                </svg>
                Lihat PDF
              </a>
            </div>
          @endif

          {{-- Tabel Nilai EPT --}}
          @if(($vm['type'] ?? null) === 'ept' && is_array($vm['scores']))
            <div class="mb-2">
              <h3 class="text-xl font-bold text-gray-800 mb-3">Rincian Nilai EPT</h3>
              <div class="table-modern">
                <table class="w-full">
                  <thead><tr><th>Tes Ke</th><th>Tanggal</th><th>Nilai</th></tr></thead>
                  <tbody>
                  @foreach($vm['scores'] as $row)
                    @php
                      $raw = $row['tanggal'] ?? null;

                      if ($raw instanceof \Carbon\CarbonInterface) {
                          // Sudah Carbon
                          $tgl = $raw->format('d/m/Y');
                      } elseif ($raw instanceof \Illuminate\Support\Optional) {
                          // Optional akan mem-proxy ke nilai dalamnya jika ada
                          $tgl = $raw->format('d/m/Y') ?? '-';
                      } elseif (is_string($raw) || is_int($raw) || is_float($raw)) {
                          // String/epoch → parse aman
                          try {
                              $tgl = \Carbon\Carbon::parse($raw)->format('d/m/Y');
                          } catch (\Throwable $e) {
                              $tgl = '-';
                          }
                      } else {
                          // null atau tipe lain
                          $tgl = '-';
                      }
                  @endphp
                    <tr>
                      <td>{{ $row['label'] ?? '-' }}</td>
                      <td>{{ $tgl }}</td>
                      <td><strong>{{ $row['nilai'] ?? '-' }}</strong></td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- Sidebar --}}
      <aside class="space-y-6">
        {{-- Cek Kode Lain --}}
        <div class="card p-4 md:p-6">
          <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Cek Kode Lain
          </h3>
          <div id="check-another" class="flex gap-2">
            <input id="inp-code" type="text" class="input-code" placeholder="Masukkan kode..." autocapitalize="off"
                   autocomplete="one-time-code" spellcheck="false" maxlength="64">
            <button id="btn-go" class="btn btn-secondary px-4" aria-label="Periksa kode">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
          </div>
          <p class="text-xs text-gray-500 mt-3">
            <strong>Tidak case-sensitive:</strong> Huruf besar/kecil tidak berpengaruh.
          </p>
        </div>

        {{-- Tautan Verifikasi (jika ada) --}}
        @if(!empty($vm['verification_url']))
          <div class="card p-4 md:p-6">
            <h3 class="font-bold text-gray-800 mb-3 flex items-center gap-2">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
              Tautan Verifikasi
            </h3>
            <a class="text-sm text-purple-600 hover:text-purple-700 break-all block p-3 bg-purple-50 rounded-lg border border-purple-200 transition"
               href="{{ $vm['verification_url'] }}" target="_blank" rel="noopener noreferrer">
              {{ $vm['verification_url'] }}
            </a>
          </div>
        @endif
      </aside>

    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  // Copy code (ringan)
  document.addEventListener('click', function(e){
    const btn = e.target.closest('button[data-copy]'); if(!btn) return;
    const value = btn.getAttribute('data-copy') || ''; if(!value) return;
    navigator.clipboard.writeText(value).then(()=>{
      const live = document.getElementById('copy-status');
      const t = btn.innerHTML;
      btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Copied!`;
      if(live) live.textContent = 'Kode verifikasi disalin';
      setTimeout(()=>{ btn.innerHTML = t; if(live) live.textContent=''; }, 1500);
    });
  });

  // Normalisasi kode (lowercase). Jika backend case-sensitive, hapus .toLowerCase()
  function normalizeCode(raw){ return (raw || '').toString().trim().replace(/\s+/g,'').toLowerCase(); }

  // Navigasi cek kode pakai route placeholder agar aman ke perubahan path
  function goToVerification(){
    const input = document.getElementById('inp-code');
    const box = document.getElementById('check-another');
    const code = normalizeCode(input?.value);
    if(!code){
      if(box){ box.classList.remove('animate-shake'); void box.offsetWidth; box.classList.add('animate-shake'); }
      input?.focus();
      return;
    }
    const tpl = @json(route('verification.show', ['code' => 'CODE_PLACEHOLDER']));
    const url = tpl.replace('CODE_PLACEHOLDER', encodeURIComponent(code));
    window.location.href = url;
  }

  document.getElementById('btn-go')?.addEventListener('click', goToVerification);
  document.getElementById('inp-code')?.addEventListener('keydown', (e)=>{ if(e.key==='Enter') goToVerification(); });
</script>
@endpush
