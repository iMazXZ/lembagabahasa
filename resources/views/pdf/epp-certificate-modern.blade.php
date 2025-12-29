@php
    $toFileUrl = function (?string $absPath): ?string {
        if (!$absPath) return null;
        $real = realpath($absPath);
        return ($real && is_file($real)) ? ('file://' . $real) : null;
    };

    $logoSrc  = $toFileUrl($logoPath ?? null);
    $signSrc  = $toFileUrl($signPath ?? null);
    $stampSrc = $toFileUrl($stampPath ?? null);

    $ttdDate   = $certificate->issued_at->format('d F Y');
    $chairName = $chairName ?? 'Drs. H Bambang Eko Siagiyanto, M. Pd.';
    $chairNip  = $chairNip ?? '196607161994031002';

    $gradeColor = match (true) {
        str_starts_with($certificate->grade ?? '', 'A') => '#166534',
        str_starts_with($certificate->grade ?? '', 'B') => '#1d4ed8',
        default => '#b91c1c',
    };

    $scoreFields = $category?->score_fields ?? [];
    $scores = $certificate->scores ?? [];
@endphp
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sertifikat {{ $certificate->name }}</title>
    <style>
        /* REMOVED @page size constraint as requested */
        @page {
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Times New Roman', serif;
            background: #fff;
            color: #000;
        }

        /* Container flows naturally */
        .page-container {
            position: relative;
            box-sizing: border-box;
            background: #fff;
            padding: 15mm;
            width: 100%; 
            /* No fixed height */
        }

        /* SIMPLE BORDER FRAME */
        .border-frame {
            position: absolute;
            top: 10mm;
            left: 10mm;
            right: 10mm;
            bottom: 10mm;
            border: 5px double #1e3a8a; /* Navy Double */
            z-index: 10;
            pointer-events: none;
        }

        /* CONTENT */
        .header {
            text-align: center;
            margin-top: 10mm;
        }
        .logo { width: 70px; margin-bottom: 5mm; }
        .inst-name { font-size: 16pt; font-weight: bold; text-transform: uppercase; color: #1e3a8a; }
        .inst-sub { font-size: 10pt; margin-top: 2px; }

        .title {
            text-align: center;
            font-size: 36pt;
            font-weight: bold;
            color: #1e3a8a;
            margin: 10mm 0 2mm 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .cert-no { text-align: center; font-size: 10pt; }

        .recipient { text-align: center; margin: 8mm 0; }
        .awarded-to { font-size: 12pt; font-style: italic; margin-bottom: 3mm; }
        .name {
            font-size: 26pt;
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            padding-bottom: 2mm;
            margin-bottom: 3mm;
        }
        .meta { font-size: 12pt; font-weight: bold; }

        .desc {
            text-align: center;
            font-size: 12pt;
            margin: 5mm 0;
            line-height: 1.5;
        }

        /* SCORES - Simple Centered Table */
        .scores-table {
            margin: 5mm auto;
            width: 70%;
            border-collapse: collapse;
            font-family: 'Arial', sans-serif; /* Readable scores */
        }
        .scores-table td {
            border-bottom: 1px solid #ddd;
            padding: 4px;
            font-size: 10pt;
        }
        .lbl { text-align: left; width: 60%; }
        .val { text-align: right; width: 40%; font-weight: bold; }

        /* SUMMARY ROW */
        .summary {
            text-align: center;
            margin-top: 5mm;
            font-family: 'Arial', sans-serif;
            border: 2px solid #1e3a8a;
            padding: 3mm;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            background: #f0f4ff;
        }
        .sum-item { display: inline-block; width: 30%; vertical-align: middle; }
        .sum-lbl { font-size: 8pt; text-transform: uppercase; }
        .sum-val { font-size: 14pt; font-weight: bold; }

        /* FOOTER */
        .footer {
            margin-top: 10mm;
            width: 100%;
            text-align: right;
            padding-right: 15mm;
        }
        .sig-block {
            display: inline-block;
            text-align: center;
            width: 60mm;
        }
        .sig-img-area { height: 25mm; position: relative; margin: 0 auto; width: 100%; }
        .stamp { position: absolute; right: 10mm; top: 0; width: 25mm; opacity: 0.8; }
        .sign { position: absolute; right: 0; top: 0; width: 35mm; z-index: 5; }

        .auth {
            position: absolute;
            bottom: 12mm;
            left: 15mm;
            font-size: 8pt;
            font-family: 'Arial', sans-serif;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="page-container">
        
        <div class="header">
            @if($logoSrc) <img src="{{ $logoSrc }}" class="logo"> @endif
            <div class="inst-name">Lembaga Bahasa</div>
            <div class="inst-sub">Universitas Muhammadiyah Metro</div>
        </div>

        <div class="title">Certificate</div>
        <div class="cert-no">No. {{ $certificate->certificate_number }}</div>

        <div class="recipient">
            <div class="awarded-to">This is to certify that</div>
            <div class="name">{{ strtoupper($certificate->name) }}</div>
            <div class="meta">{{ $certificate->study_program ?? '' }} | {{ $certificate->srn ?? '' }}</div>
        </div>

        <div class="desc">
            Has successfully completed the<br>
            <strong>{{ $category?->name ?? 'English Practice Program' }}</strong>
            @if($certificate->semester) <br>Semester {{ $certificate->semester }} @endif
        </div>

        <table class="scores-table">
            @foreach($scoreFields as $field)
                <tr>
                    <td class="lbl">{{ ucfirst($field) }}</td>
                    <td class="val">{{ $scores[$field] ?? '-' }}</td>
                </tr>
            @endforeach
        </table>

        <div class="summary">
            <div class="sum-item">
                <div class="sum-lbl">Total Score</div>
                <div class="sum-val">{{ $certificate->total_score ?? '-' }}</div>
            </div>
            <div class="sum-item" style="border-left:1px solid #ccc; border-right:1px solid #ccc">
                <div class="sum-lbl">Average</div>
                <div class="sum-val">{{ number_format($certificate->average_score ?? 0, 2) }}</div>
            </div>
            <div class="sum-item">
                <div class="sum-lbl">Grade</div>
                <div class="sum-val" style="color: {{ $gradeColor }}">{{ $certificate->grade ?? '-' }}</div>
            </div>
        </div>

        <div class="footer">
            <div class="sig-block">
                <div style="font-size: 11pt; margin-bottom: 2mm;">Metro, {{ $ttdDate }}</div>
                <div class="sig-img-area">
                    @if($stampSrc) <img src="{{ $stampSrc }}" class="stamp"> @endif
                    @if($signSrc) <img src="{{ $signSrc }}" class="sign"> @endif
                </div>
                <div style="font-weight: bold; text-decoration: underline; margin-top: 2px;">{{ $chairName }}</div>
                <div style="font-size: 10pt;">NIP. {{ $chairNip }}</div>
            </div>
        </div>

        <div class="auth">
            Verification: {{ url('/verification/' . $certificate->verification_code) }}<br>
            Code: <b>{{ $certificate->verification_code }}</b>
        </div>
    </div>
</body>
</html>
