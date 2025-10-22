@php
    /** @var \App\Models\Penerjemahan $record */

    // Relasi utama
    $pemohon    = $record->users;
    $translator = $record->translator;

    // Logo / Stempel / TTD — langsung pakai file lokal
    $logoSrc  = is_file(public_path('images/logo-um.png')) ? public_path('images/logo-um.png') : null;
    $stampSrc = is_file(public_path('images/stempel.png')) ? public_path('images/stempel.png') : null;
    $signSrc  = is_file(public_path('images/ttd_ketua.png')) ? public_path('images/ttd_ketua.png') : null;

    // Tanggal tanda tangan (fallback completion_date → updated_at → now)
    $ttdDate = $ttdDate
        ?? \Illuminate\Support\Carbon::parse($record->completion_date ?? $record->updated_at ?? now())
            ->locale('id')
            ->translatedFormat('d F Y');

    // Verifikasi (link & kode)
    $verifyCode = $record->verification_code ?: null;
    $verifyUrl  = $record->verification_url
        ?: ($verifyCode ? route('verification.show', ['code' => $verifyCode], true) : null);

    // Penandatangan
    $chairName = 'Drs. H. Bambang Eko Siagiyanto M.Pd';
    $chairNip  = '196607161994031002';

    // Konten terjemahan: sanitasi HTML
    $rich = (string) ($record->translated_text ?? '');
    $rich = preg_replace('#<script\b[^>]*>(.*?)</script>#is', '', $rich);
    $rich = preg_replace('/\s+on\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/i', '', $rich);
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Hasil Penerjemahan — Lembaga Bahasa UM Metro</title>
    <style>
        :root{
            --primary:#1e40af; --primary-dark:#1e3a8a; --accent:#3b82f6;
            --soft:#eff6ff; --line:#dfe6ff; --ink:#0f172a; --muted:#475569;
        }

        @page { margin: 20mm 16mm; size: A4 portrait; }
        *{ margin:0; padding:0; box-sizing:border-box; }
        body{ font-family:"DejaVu Sans", Arial, Helvetica, sans-serif; font-size:10.5px; color:var(--ink); line-height:1.6; background:#fff; }

        /* ===== Header (kop) ===== */
        .header{ border:1px solid var(--line); border-radius:10px; padding:2px; margin-bottom:2px; background:#fff; }
        .h-tbl{ width:100%; border-collapse:collapse; }
        .h-tbl td{ vertical-align:middle; }
        .logo-cell{ width:110px; padding-left:25px; }
        .logo{ width:95px; height:95px; object-fit:contain; display:block; margin-left:auto; margin-right:auto; image-rendering:auto; }
        .kop{ text-align:center; }
        .univ{ font-size:25px; font-weight:800; color:var(--primary-dark); text-transform:uppercase; letter-spacing:.5px; }
        .dept{ font-size:20px; font-weight:800; color:var(--primary); letter-spacing:10px;}
        .addr{ font-size:12px; color:var(--muted); }
        .rib{ height:6px; border-radius:4px; background: linear-gradient(90deg,#d9e6ff 0%, #eff6ff 100%); }

        /* ===== Section header ===== */
        .section{ margin:14px 0 10px; border-left:4px solid var(--primary); background: linear-gradient(90deg, var(--soft) 0%, #ffffff 100%); border-radius:6px; padding:8px 12px; }
        .section .title{ font-size:13px; font-weight:700; color:var(--primary-dark); text-transform:uppercase; letter-spacing:.4px; }
        .section .sub{ font-size:10px; color:var(--muted); margin-top:2px; }

        /* ===== Info card ===== */
        .card{ border:1.6px solid var(--line); border-radius:10px; overflow:hidden; }
        .info{ width:100%; border-collapse:collapse; }
        .info tr + tr{ border-top:1px solid var(--line); }
        .info .lab{ width:185px; background:var(--soft); color:var(--primary-dark); font-weight:700; padding:5px 6px; }
        .info .col{ width:16px; text-align:center; color:var(--primary); font-weight:700; background:var(--soft); }
        .info .val{ padding:9px 12px; color:var(--ink); }

        /* ===== Konten terjemahan ===== */
        .content{ border:1.6px solid var(--line); border-radius:10px; padding:14px; page-break-inside:auto; }
        .rich *{ max-width:100%; }
        .rich p{ margin:9px 0; text-align:justify; line-height:1.7; }
        .break-word{ word-wrap:break-word; word-break:break-word; }

        /* ===== Panels: Verification & Signature ===== */
        .panels{ margin-top:10px; }
        .p-grid{ width:100%; border-collapse:collapse; table-layout:fixed; }
        .p-grid td{ width:50%; vertical-align:top; }
        .p-l{ padding-right:7px; } .p-r{ padding-left:7px; }
        .panel{ border:1.6px solid var(--line); border-radius:10px; padding:12px; background:#fff; page-break-inside:avoid; }
        .p-title{ font-size:11px; font-weight:700; color:var(--primary-dark); text-transform:uppercase; letter-spacing:.3px; margin-bottom:6px; padding-bottom:6px; border-bottom:1.6px solid var(--line); }

        .v-text{ font-size:10px; color:var(--muted); line-height:1.5; }
        .v-url{ display:inline-block; margin-top:6px; padding:7px 9px; background:var(--soft); border:1px dashed var(--accent); border-radius:6px; color:var(--primary); font-size:9.5px; text-decoration:none; word-break:break-all; }

        /* ===== Signature block (compact seperti referensi) ===== */
        .sig-date{ text-align:right; font-size:10px; color:var(--ink); }

        /* === Signature area (compact, inline-like) === */
        .sig-pad{
        position: relative;
        height: 20mm;       
        margin: 2px auto 6px;
        width: 100%;
        overflow: visible;
        }

        /* STAMP: di kiri, ukuran lebih besar */
        .sig-stamp{
        position: absolute;
        top: 50%;
        left: 65%;
        transform: translate(-50%, -50%) rotate(-10deg);
        width: 28mm;        /* lebih besar seperti referensi */
        height: auto;
        z-index: 1;
        }

        /* SIGN: overlap lebih banyak dengan stempel */
        .sig-sign{
        position: absolute;
        top: 50%;
        left: 80%;
        transform: translate(-50%, -50%) rotate(-2deg);
        z-index: 2;
        }
        .sig-sign img{
        display: block;
        width: 35mm;
        height: auto;
        }

        /* Nama & NIP: right, rapat */
        .sig-name{ margin-top: 0; text-align: right; font-weight: 700; font-size:10px; line-height:1.3; }
        .sig-nip{  margin-top: 0; text-align: right; font-size: 9px; color: var(--muted); }
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
                        <div class="sig-pad">
                            @if($stampPath)
                                <img class="sig-stamp" src="{{ $stampPath }}" alt="Stempel">
                            @endif
                            @if($signPath)
                                <div class="sig-sign">
                                    <img src="{{ $signPath }}" alt="Tanda Tangan">
                                </div>
                            @endif
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