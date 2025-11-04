@php
    /**
     * Dompdf friendly: gambar lokal pakai file://
     */
    $toFileUrl = function (?string $absPath): ?string {
        if (!$absPath) return null;
        $real = realpath($absPath);
        return ($real && is_file($real)) ? ('file://' . $real) : null;
    };

    $logoSrc  = $toFileUrl($logoPath ?? null);
    $signSrc  = $toFileUrl($signPath ?? null);
    $stampSrc = $toFileUrl($stampPath ?? null);

    // Fallback variabel penandatangan bila belum dipass dari controller
    $ttdDate  = $ttdDate  ?? now()->timezone(config('app.timezone','Asia/Jakarta'))->format('d F Y');
    $chairName= $chairName?? 'Drs. H Bambang Eko Siagiyanto, M. Pd.';
    $chairNip = $chairNip ?? '196607161994031002';

    // Peta warna grade diperluas — TIDAK mengubah teks huruf dari DB
    $gradeColor = match (trim($finalLetter)) {
        'A+', 'A' => '#27ae60',
        'A-', 'B+' => '#2ecc71',
        'B' => '#3498db',
        'B-', 'C+' => '#5dade2',
        'C' => '#f39c12',
        'C-', 'D+', 'D' => '#e67e22',
        default => '#e74c3c',
    };
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sertifikat Basic Listening</title>
  <style>
    /* ---------- HALAMAN A4 ---------- */
    @page { size: A4; margin: 0; }
    html, body { margin:0; padding:0; }
    body {
        font-family: DejaVu Sans, 'Helvetica', 'Arial', sans-serif;
        font-size: 10px;
        color: #2c3e50;
        line-height: 1.2;
        background: #fff;
    }

    :root{
        --ink:#2c3e50;
        --muted:#7f8c8d;
        --shadow: rgba(2,6,23,.12);
        --brand:#3498db;
    }

    /* ---------- KANVAS UTAMA (lebih pendek agar pasti 1 halaman) ---------- */
    .certificate-container{
        width: 210mm;
        height: 287mm;               /* bleed 10mm dari A4 untuk aman */
        position: relative;
        background: #fff;
        overflow: hidden;
        box-sizing: border-box;
    }
    .content-wrapper{ padding: 8mm 12mm; }

    /* ---------- WATERMARK ---------- */
    .watermark{
        position:absolute; top:50%; left:50%;
        transform: translate(-50%, -50%);
        font-size: 58px;
        font-weight: 900;
        color: rgba(52,152,219,0.06);
        z-index:1;
        white-space: nowrap;
        pointer-events: none;
        user-select: none;
    }

    /* ---------- HEADER ---------- */
    .header{
        position: relative;
        display: table;
        width: 100%;
        table-layout: fixed;
        padding-bottom: 2mm;
        border-bottom: 2px solid var(--brand);
        z-index:3;
    }
    .institution-info{ display: table-cell; width: 60%; vertical-align: top; }
    .logo{ width: 35px; height: 35px; margin-right: 2mm; float:left; display:block; }
    .institution-text{ overflow:hidden; }
    .institution-name{ font-size:14px; font-weight:700; }
    .institution-department{ font-size:9px; color:var(--muted); }

    .verification-header{
        display: table-cell; width: 40%;
        vertical-align: top; text-align:right;
        font-size: 7px; color:var(--muted); line-height:1;
        word-break: break-word;
    }
    .verification-header strong{ color:var(--ink); }

    /* ---------- UTAMA ---------- */
    .main-content{ text-align:center; position:relative; z-index:3; }

    .certificate-title{ margin: 12mm 0 7mm 0; }
    .title-main{ font-size:28px; font-weight:800; color:var(--brand); letter-spacing:1.6px; margin-bottom:1mm; }
    .title-sub{ font-size:12px; }

    .participant-name-container{ margin: 7mm 0; }
    .participant-name{ font-size:24px; font-weight:900; color:var(--ink); margin-bottom:3.5mm; }
    .participant-details{ font-size:10px; color:#6c757d; line-height:1.4; }
    .certificate-number{ font-size:12px; color:#6c757d; line-height:1; }
    .participant-details strong{ color:var(--ink); font-weight:700; }

    .title-description{ font-size:10px; color:#6c757d; margin-top:7mm; }

    .scores-section{ margin:5mm auto 0 auto; width:82%; }
    table{ border-collapse:collapse; }
    .scores-table{ width:100%; font-size:12px; }
    .scores-table th, .scores-table td{ padding:3px 5px; border-bottom:1px dashed #dee2e6; }
    .scores-table th{ color:#6c757d; background:#f8f9fa; text-align:left; }
    .scores-table td.num-center{ text-align:center; }
    .scores-table td.num-right{ text-align:right; }

    .grade-row td{
        font-weight:800; font-size:12px; padding-top:4px;
        border-top:1px solid var(--brand);
    }
    .grade-value{ font-size:14px; font-weight:900; color: {{ $gradeColor }}; }

    /* ---------- FOOTER: SIG-CARD (sesuai referensi kamu) ---------- */
    .footer{
        position:absolute;
        left: 12mm; right: 20mm;
        margin-top: 8mm;
        z-index:5;
    }

    /* Signature Card */
    .sig-card {
        background: #fff;
        padding: 14px 16px;
        border-radius: 10px;
        margin-left: auto;
    }
    .sig-location {
        text-align: right;
        font-size: 9px;
        color: var(--ink);
    }
    .sig-area {
        position: relative;
        height: 52px;
        margin: 8px 0;
    }
    .sig-stamp {
        position: absolute;
        top: 30%;
        left: 80%;
        transform: translate(-50%, -50%) rotate(-10deg);
        width: 30mm;
        height: auto;
        z-index: 1;
        opacity: 0.95;
    }
    .sig-sign {
        position: absolute;
        top: 40%;
        left: 90%;
        transform: translate(-50%, -50%) rotate(-2deg);
        z-index: 2;
    }
    .sig-sign img {
        display: block;
        width: 40mm;
        height: auto;
    }
    .sig-name {
        text-align: right;
        font-weight: 700;
        font-size: 9.5px;
        margin-top: -10px;
        line-height: 1;
        color: var(--ink);
    }
    .sig-nip {
        text-align: right;
        font-size: 8.5px;
        color: var(--muted);
        margin-top: 1px;
    }

    /* Helper */
    .avoid-break{ page-break-inside: avoid; }
  </style>
</head>
<body>
  <div class="certificate-container">
    <div class="watermark">CERTIFICATE</div>

    <div class="content-wrapper">
      {{-- HEADER --}}
      <div class="header avoid-break">
        <div class="institution-info">
          @if($logoSrc)
            <img class="logo" src="{{ $logoSrc }}" alt="Logo Institusi">
          @endif
          <div class="institution-text">
            <div class="institution-name">Universitas Muhammadiyah Metro</div>
            <div class="institution-department">Lembaga Bahasa</div>
          </div>
        </div>

        <div class="verification-header">
          Dokumen ini sah dan terverifikasi elektronik.<br>
          <strong>Kode Verifikasi:</strong> {{ $verificationCode }}<br>
          <strong>URL:</strong> {{ $verificationUrl }}
        </div>
      </div>

      {{-- KONTEN UTAMA --}}
      <div class="main-content avoid-break">
        <div class="certificate-title">
          <div class="title-main">Certificate of Completion</div>
          <div class="certificate-number" style="margin-bottom:4mm;">
            <strong>Nomor :</strong> {{ $certificateNumber }}
          </div>
          <div class="title-sub" style="margin-top:8mm;">Diberikan Kepada</div>
        </div>

        <div class="participant-name-container">
          <div class="participant-name">{{ $user->name }}</div>

          <div class="participant-details">
            <strong>NPM:</strong> {{ $user->srn }}
            &nbsp;|&nbsp; <strong>Angkatan:</strong> {{ $user->year ?? '-' }}
            &nbsp;|&nbsp; <strong>Tanggal Terbit:</strong> {{ $issuedAt->timezone(config('app.timezone','Asia/Jakarta'))->format('d F Y') }}
          </div>

          <div class="title-description">
            Atas keberhasilannya menyelesaikan <strong>Basic Listening Course</strong> dengan nilai:
          </div>
        </div>

        <div class="scores-section avoid-break">
          <table class="scores-table">
            <thead>
              <tr>
                <th style="width: 50%;">Komponen Penilaian</th>
                <th style="width: 25%; text-align:center;">Nilai</th>
                <th style="width: 25%; text-align:right;">Bobot (%)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Attendance</td>
                <td class="num-center">{{ number_format($attendance, 0) }}</td>
                <td class="num-right">20%</td>
              </tr>
              <tr>
                <td>Daily Average</td>
                <td class="num-center">{{ is_numeric($daily) ? number_format($daily, 2) : '—' }}</td>
                <td class="num-right">40%</td>
              </tr>
              <tr>
                <td>Final Test</td>
                <td class="num-center">{{ number_format($finalTest, 0) }}</td>
                <td class="num-right">40%</td>
              </tr>

              <tr class="grade-row">
                <td>FINAL SCORE</td>
                <td class="num-center">{{ number_format($finalNumeric, 2) }}</td>
                <td class="num-right">100%</td>
              </tr>
              <tr class="grade-row">
                <td>GRADE</td>
                <td colspan="2" class="grade-value" style="text-align:center;">
                  {{ $finalLetter }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      {{-- FOOTER: SIG-CARD --}}
      <div class="footer avoid-break">
        <div class="sig-card">
          <div class="sig-location">Metro, {{ $ttdDate }}</div>
          <div class="sig-area">
            @if($stampSrc)
              <img class="sig-stamp" src="{{ $stampSrc }}" alt="Stempel">
            @endif
            @if($signSrc)
              <div class="sig-sign">
                <img src="{{ $signSrc }}" alt="Tanda Tangan">
              </div>
            @endif
          </div>
          <div class="sig-name">{{ $chairName }}</div>
          <div class="sig-nip">NIP. {{ $chairNip }}</div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
