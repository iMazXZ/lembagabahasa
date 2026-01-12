{{-- resources/views/ept/certificate.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>EPT Certificate</title>
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Times New Roman', serif;
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
            color: #1e293b;
        }
        
        .certificate {
            width: 100%;
            height: 100%;
            position: relative;
            padding: 40px;
        }
        
        .inner-border {
            border: 3px solid #d4af37;
            padding: 30px;
            height: 100%;
            background: #fff;
            position: relative;
        }
        
        .corner-ornament {
            position: absolute;
            width: 80px;
            height: 80px;
            border: 2px solid #d4af37;
        }
        
        .top-left { top: 10px; left: 10px; border-right: none; border-bottom: none; }
        .top-right { top: 10px; right: 10px; border-left: none; border-bottom: none; }
        .bottom-left { bottom: 10px; left: 10px; border-right: none; border-top: none; }
        .bottom-right { bottom: 10px; right: 10px; border-left: none; border-top: none; }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo {
            width: 80px;
            margin-bottom: 10px;
        }
        
        .institution {
            font-size: 14px;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .title {
            text-align: center;
            margin: 30px 0;
        }
        
        .title h1 {
            font-size: 36px;
            color: #1e3a5f;
            font-weight: bold;
            letter-spacing: 3px;
            text-transform: uppercase;
        }
        
        .title h2 {
            font-size: 18px;
            color: #64748b;
            margin-top: 5px;
            font-weight: normal;
        }
        
        .content {
            text-align: center;
            margin: 30px 0;
        }
        
        .label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .name {
            font-size: 28px;
            font-weight: bold;
            color: #1e293b;
            margin: 10px 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #d4af37;
            display: inline-block;
        }
        
        .srn {
            font-size: 14px;
            color: #475569;
        }
        
        .scores {
            display: table;
            width: 100%;
            margin: 30px 0;
        }
        
        .score-row {
            display: table-row;
        }
        
        .score-section {
            display: table-cell;
            text-align: center;
            padding: 15px 20px;
            width: 25%;
        }
        
        .score-section.main {
            background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);
            color: white;
            border-radius: 10px;
        }
        
        .score-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
        }
        
        .score-section.main .score-label {
            color: #94a3b8;
        }
        
        .score-value {
            font-size: 28px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .score-section.main .score-value {
            font-size: 36px;
            color: #fbbf24;
        }
        
        .level {
            font-size: 12px;
            color: #d4af37;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        
        .footer {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        
        .footer-row {
            display: table-row;
        }
        
        .footer-cell {
            display: table-cell;
            text-align: center;
            padding: 20px;
            vertical-align: bottom;
        }
        
        .date {
            font-size: 12px;
            color: #64748b;
        }
        
        .signature-line {
            border-top: 1px solid #1e293b;
            width: 200px;
            margin: 0 auto;
            padding-top: 5px;
        }
        
        .signatory {
            font-size: 12px;
            color: #475569;
        }
        
        .signatory-title {
            font-size: 10px;
            color: #64748b;
        }
        
        .cert-id {
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="inner-border">
            <div class="corner-ornament top-left"></div>
            <div class="corner-ornament top-right"></div>
            <div class="corner-ornament bottom-left"></div>
            <div class="corner-ornament bottom-right"></div>
            
            <div class="header">
                <p class="institution">Universitas Islam Negeri Antasari Banjarmasin</p>
                <p class="institution" style="color: #1e3a5f; font-weight: bold;">Pusat Pengembangan Bahasa</p>
            </div>
            
            <div class="title">
                <h1>Certificate</h1>
                <h2>English Proficiency Test (EPT)</h2>
            </div>
            
            <div class="content">
                <p class="label">This is to certify that</p>
                <p class="name">{{ $user->name }}</p>
                <p class="srn">{{ $user->srn }}</p>
            </div>
            
            <p style="text-align: center; color: #475569; font-size: 13px; margin-bottom: 20px;">
                has successfully completed the English Proficiency Test with the following scores:
            </p>
            
            <div class="scores">
                <div class="score-row">
                    <div class="score-section">
                        <p class="score-label">Listening</p>
                        <p class="score-value">{{ $attempt->scaled_listening }}</p>
                    </div>
                    <div class="score-section">
                        <p class="score-label">Structure</p>
                        <p class="score-value">{{ $attempt->scaled_structure }}</p>
                    </div>
                    <div class="score-section">
                        <p class="score-label">Reading</p>
                        <p class="score-value">{{ $attempt->scaled_reading }}</p>
                    </div>
                    <div class="score-section main">
                        <p class="score-label">Total Score</p>
                        <p class="score-value">{{ $attempt->total_score }}</p>
                        <p class="level">{{ $interpretation }}</p>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <div class="footer-row">
                    <div class="footer-cell">
                        <p class="date">Banjarmasin, {{ $attempt->submitted_at->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="footer-cell">
                        <div class="signature-line">
                            <p class="signatory">Dr. Name, M.Pd.</p>
                            <p class="signatory-title">Kepala Pusat Pengembangan Bahasa</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <p class="cert-id">
                Certificate ID: EPT-{{ str_pad($attempt->id, 6, '0', STR_PAD_LEFT) }}-{{ $attempt->submitted_at->format('Ymd') }}
            </p>
        </div>
    </div>
</body>
</html>
