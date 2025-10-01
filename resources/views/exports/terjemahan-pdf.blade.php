{{-- resources/views/exports/terjemahan-pdf.blade.php --}}
@php
    /** @var \App\Models\Penerjemahan $record */
    $pemohon = $record->users;

    // Aset lokal (pakai file:// agar Dompdf konsisten)
    $logoPath  = is_file(public_path('images/logo-um.png')) ? 'file://' . realpath(public_path('images/logo-um.png')) : null;
    $stampPath = is_file(public_path('images/stempel.png'))   ? 'file://' . realpath(public_path('images/stempel.png'))   : null;
    $signPath  = is_file(public_path('images/ttd_ketua.png')) ? 'file://' . realpath(public_path('images/ttd_ketua.png')) : null;

    // Verifikasi
    $verifyCode = $record->verification_code ?: null;
    $verifyUrl  = $record->verification_url
        ?: ($verifyCode ? route('verification.show', ['code' => $verifyCode], true) : null);

    // Tanggal tanda tangan (English)
    $ttdDate = optional($record->completion_date)
        ? $record->completion_date->locale('en')->translatedFormat('F j, Y')
        : now()->locale('en')->translatedFormat('F j, Y');

    // Penandatangan
    $chairName = 'Drs. H. Bambang Eko Siagiyanto M.Pd';
    $chairNip  = '196607161994031002';

    // Richtext: hilangkan <script>, render sisanya apa adanya
    $rich = $record->translated_text ?? '';
    $rich = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $rich);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Hasil Penerjemahan — Lembaga Bahasa UM Metro</title>
    <style>
        /* ===== Theme (aman Dompdf) ===== */
        :root{
            --primary:#1e40af;         /* biru utama */
            --primary-dark:#1e3a8a;
            --accent:#3b82f6;
            --soft:#eff6ff;            /* latar lembut */
            --line:#dfe6ff;            /* garis */
            --ink:#0f172a;
            --muted:#475569;
        }

        @page { margin: 20mm 16mm; size: A4 portrait; }
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:"DejaVu Sans", Arial, Helvetica, sans-serif; font-size:10.5px; color:var(--ink); line-height:1.6; background:#fff; }

        /* ===== Header (kop) ===== */
        .header{
            border:1px solid var(--line);
            border-radius:10px;
            padding:5px 5px;
            margin-bottom:5px;
            background: #ffffff;
        }
        .h-tbl{ width:100%; border-collapse:collapse; }
        .h-tbl td{ vertical-align:middle; }
        .logo-cell{ width:110px; padding-left:25px; }
        .logo{ width:95px; height:95px; object-fit:contain; display:block; margin-left:auto; margin-right:auto; }
        .kop{ text-align:center; }
        .univ{ font-size:25px; font-weight:800; color:var(--primary-dark); text-transform:uppercase; letter-spacing:.5px; }
        .dept{ font-size:20px; font-weight:800; color:var(--primary); letter-spacing:5px;}
        .addr{ font-size:12px; color:var(--muted); }
        .rib{ height:6px; border-radius:4px; background: linear-gradient(90deg,#d9e6ff 0%, #eff6ff 100%); }

        /* ===== Section header ===== */
        .section{
            margin:14px 0 10px;
            border-left:4px solid var(--primary);
            background: linear-gradient(90deg, var(--soft) 0%, #ffffff 100%);
            border-radius:6px;
            padding:8px 12px;
        }
        .section .title{ font-size:13px; font-weight:700; color:var(--primary-dark); text-transform:uppercase; letter-spacing:.4px; }
        .section .sub{ font-size:10px; color:var(--muted); margin-top:2px; }

        /* ===== Info card ===== */
        .card{ border:1.6px solid var(--line); border-radius:10px; overflow:hidden; }
        .info{ width:100%; border-collapse:collapse; }
        .info tr + tr{ border-top:1px solid var(--line); }
        .info .lab{ width:185px; background:var(--soft); color:var(--primary-dark); font-weight:700; padding:9px 12px; }
        .info .col{ width:16px; text-align:center; color:var(--primary); font-weight:700; background:var(--soft); }
        .info .val{ padding:9px 12px; color:var(--ink); }

        /* ===== Konten terjemahan (rich text) ===== */
        .content{ border:1.6px solid var(--line); border-radius:10px; padding:14px; page-break-inside:auto; }
        .rich *{ max-width:100%; }
        .rich p{ margin:9px 0; text-align:justify; line-height:1.7; }
        .break-word{ word-wrap:break-word; word-break:break-word; }

        /* ===== Panels: Verification & Signature ===== */
        .panels{ margin-top:14px; }
        .p-grid{ width:100%; border-collapse:collapse; table-layout:fixed; }
        .p-grid td{ width:50%; vertical-align:top; }
        .p-l{ padding-right:7px; } .p-r{ padding-left:7px; }
        .panel{ border:1.6px solid var(--line); border-radius:10px; padding:12px; background:#fff; page-break-inside:avoid; }
        .p-title{ font-size:11px; font-weight:700; color:var(--primary-dark); text-transform:uppercase; letter-spacing:.3px; margin-bottom:6px; padding-bottom:6px; border-bottom:1.6px solid var(--line); }

        .v-text{ font-size:10px; color:var(--muted); line-height:1.5; }
        .v-url{ display:inline-block; margin-top:6px; padding:7px 9px; background:var(--soft); border:1px dashed var(--accent); border-radius:6px; color:var(--primary); font-size:9.5px; text-decoration:none; word-break:break-all; }

        .sig-date{ text-align:right; font-size:10px; color:var(--ink); }
        .sig-role{ text-align:right; font-size:10px; font-weight:700; color:var(--primary-dark); margin-bottom:6px; }
        .sig-pad{ position:relative; height:68px; margin:6px 0 4px; }
        .sig-stamp{ position:absolute; right:6px; top:50%; transform:translateY(-50%); width:105px; opacity:.32; }
        .sig-sign{ position:relative; height:48px; text-align:right; }
        .sig-sign img{ height:48px; }
        .sig-name{ text-align:right; font-weight:700; color:var(--ink); margin-top:3px; }
        .sig-nip{ text-align:right; color:var(--muted); font-size:10px; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <table class="h-tbl">
            <tr>
                <td class="logo-cell">
                    @if($logoPath)
                        <img class="logo" src="{{ $logoPath }}" alt="Logo UM">
                    @endif
                </td>
                <td class="kop">
                    <div class="univ">Universitas Muhammadiyah Metro</div>
                    <div class="dept">Lembaga Bahasa</div>
                    <div class="addr">Jl. Gatot Subroto No.100, Yosodadi, Kec. Metro Timur, Kota Metro, Lampung 34124</div>
                </td>
            </tr>
        </table>
        <div class="rib"></div>
    </div>

    {{-- INFORMASI PEMOHON --}}
    <div class="card avoid-break">
        <table class="info">
            <tr>
                <td class="lab">Nama / NPM</td>
                <td class="col">:</td>
                <td class="val break-word">{{ $pemohon?->name ?? '-' }} / {{ $pemohon?->srn ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lab">Program Studi</td>
                <td class="col">:</td>
                <td class="val break-word">{{ $pemohon?->prody?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lab">Tanggal Penyelesaian</td>
                <td class="col">:</td>
                <td class="val">{{ optional($record->completion_date)->translatedFormat('d F Y, H:i') ?? '-' }} WIB</td>
            </tr>
            @if($verifyCode)
            <tr>
                <td class="lab">Kode Verifikasi</td>
                <td class="col">:</td>
                <td class="val"><strong>{{ $verifyCode }}</strong></td>
            </tr>
            @endif
        </table>
    </div>

    {{-- HASIL TERJEMAHAN --}}
    <div class="section">
        <div class="title">Hasil Terjemahan</div>
        <div class="sub">Translation Result</div>
    </div>
    <div class="content">
        {{-- render rich text apa adanya, sudah disterilkan dari <script> --}}
        <div class="rich">{!! $rich ?: '<p><em>Tidak ada konten terjemahan.</em></p>' !!}</div>
    </div>

    {{-- VERIFICATION & SIGNATURE --}}
    <div class="panels">
        <table class="p-grid">
            <tr>
                <td class="p-l">
                    <div class="panel">
                        <div class="p-title">Verifikasi Dokumen</div>
                        @if($verifyUrl)
                            <div class="v-text">Buka tautan berikut untuk memverifikasi keaslian dokumen ini:</div>
                            <a class="v-url" href="{{ $verifyUrl }}" target="_blank" rel="noopener noreferrer">{{ $verifyUrl }}</a>
                        @else
                            <div class="v-text">Tidak ada tautan verifikasi tersedia untuk dokumen ini.</div>
                        @endif
                    </div>
                </td>
                <td class="p-r">
                    <div class="panel">
                        <div class="sig-date">Metro, {{ $ttdDate }}</div>
                        <div class="sig-role">Chair, Language Institute</div>
                        <div class="sig-pad">
                            @if($stampPath)
                                <img class="sig-stamp" src="{{ $stampPath }}" alt="Stempel">
                            @endif
                            <div class="sig-sign">
                                @if($signPath)
                                    <img src="{{ $signPath }}" alt="Tanda Tangan">
                                @endif
                            </div>
                        </div>
                        <div class="sig-name">{{ $chairName }}</div>
                        <div class="sig-nip">NIP. {{ $chairNip }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
