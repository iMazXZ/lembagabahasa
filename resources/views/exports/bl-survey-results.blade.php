@php
    $barColor = [
        'success' => '#10b981',
        'warning' => '#f59e0b',
        'danger'  => '#ef4444',
    ];
@endphp
@php
    $firstMeta = $segments[0]['meta'] ?? [
        'surveyTitle' => 'Hasil Kuesioner',
    ];
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Hasil Kuesioner - {{ $firstMeta['surveyTitle'] ?? 'Hasil Kuesioner' }}</title>
  <style>
    @page {
      size: A4;
      margin: 15mm;
    }
    *{ box-sizing:border-box; margin:0; padding:0; }
    body{
      font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
      font-size: 11px;
      color: #1a1a1a;
      margin: 20px;
      padding: 0;
      background: #fff;
      line-height: 1.5;
    }
    .card{
      padding:0;
      margin-bottom:20px;
      background:#fff;
    }
    .card.header-card{
      border-bottom:2px solid #e0e0e0;
      padding:0 0 20px 0;
      margin-bottom:20px;
    }
    .card.content-card{
      padding:20px;
      border:1px solid #e0e0e0;
    }
    
    /* Header 2 Kolom */
    .header-container{
      display:table;
      width:100%;
      table-layout:fixed;
    }
    .header-left{
      display:table-cell;
      width:55%;
      vertical-align:top;
      padding-right:25px;
    }
    .header-right{
      display:table-cell;
      width:45%;
      vertical-align:middle;
      padding-left:25px;
      border-left:2px solid #e0e0e0;
    }
    h1{ 
      margin:0 0 6px 0; 
      font-size:20px; 
      color:#1a1a1a; 
      font-weight:700; 
      letter-spacing: -0.3px;
    }
    .muted{ 
      color:#666; 
      font-size:10.5px; 
      margin-bottom:15px;
      display:block;
      line-height:1.4;
    }
    .tutor-label{
      font-size:10.5px;
      color:#666;
      margin-bottom:5px;
      display:block;
      margin-top:5px;
    }
    .tutor-name{
      font-size:22px;
      font-weight:700;
      color:#1a1a1a;
      letter-spacing: -0.5px;
      margin:0;
      line-height:1.2;
    }
    .meta-item{
      font-size:11px;
      line-height:1.8;
      margin-bottom:2px;
      color:#333;
    }
    .meta-item strong{
      font-weight:600;
      color:#1a1a1a;
    }
    
    h2{ 
      margin:0 0 14px 0; 
      font-size:13px; 
      color:#444; 
      font-weight:600; 
    }
    
    .row{
      margin-bottom:18px;
      padding-bottom:14px;
      border-bottom:1px solid #e5e5e5;
      position:relative;
    }
    .row:last-child{
      border-bottom:none;
      margin-bottom:0;
      padding-bottom:0;
    }
    
    /* Question Header dengan Badge */
    .question-header{
      display:table;
      width:100%;
      margin-bottom:6px;
    }
    .question-text{
      display:table-cell;
      font-weight:500;
      color:#1a1a1a;
      font-size:11px;
      line-height:1.5;
      padding-right:10px;
      vertical-align:top;
    }
    .question-badge{
      display:table-cell;
      text-align:right;
      vertical-align:top;
      white-space:nowrap;
    }
    .badge{
      display:inline-block;
      padding:3px 10px;
      border-radius:12px;
      font-size:9px;
      font-weight:600;
      color:#fff;
      letter-spacing: 0.2px;
    }
    .badge-success{ background:#34a853; }
    .badge-warning{ background:#fbbc04; color:#202124; }
    .badge-danger{ background:#ea4335; }
    
    .stat-line{
      font-size:10px;
      color:#666;
      margin-bottom:10px;
    }
    .stat-line span{
      margin-right:12px;
    }
    
    /* Vertical Bar Chart - READABLE */
    .vertical-chart{
      margin-top:8px;
      clear:both;
    }
    .chart-container{
      display:table;
      width:100%;
      height:85px;
      border-bottom:2px solid #d0d0d0;
      table-layout:fixed;
    }
    .chart-column{
      display:table-cell;
      vertical-align:bottom;
      text-align:center;
      width:20%;
      position:relative;
      padding:0 4px;
    }
    .bar-wrapper{
      display:block;
      width:100%;
      height:85px;
      position:relative;
    }
    .vertical-bar{
      position:absolute;
      bottom:0;
      left:50%;
      transform:translateX(-50%);
      width:38px;
      border-radius:4px 4px 0 0;
      min-height:4px;
    }
    .vertical-bar.score-1,
    .vertical-bar.score-2{
      background:#ea4335;
    }
    .vertical-bar.score-3{
      background:#fbbc04;
    }
    .vertical-bar.score-4{
      background:#93c47d;
    }
    .vertical-bar.score-5{
      background:#34a853;
    }
    .bar-count{
      position:absolute;
      bottom:100%;
      left:50%;
      transform:translateX(-50%);
      font-size:10px;
      font-weight:700;
      color:#1a1a1a;
      white-space:nowrap;
      margin-bottom:3px;
    }
    .bar-label{
      display:block;
      margin-top:4px;
      font-size:9.5px;
      color:#555;
      font-weight:600;
    }
    .bar-percentage{
      display:block;
      font-size:9px;
      color:#777;
      margin-top:2px;
    }
    
    .page-break{
      page-break-after: always;
    }
  </style>
</head>
<body>
  @foreach($segments as $segmentIndex => $segment)
    @php
      $meta = $segment['meta'];
      $rows = $segment['rows'];
    @endphp

    <div class="card header-card">
      <div class="header-container">
        <div class="header-left">
          <h1>Hasil Kuesioner</h1>
          <span class="muted">{{ $meta['category'] }} • {{ $meta['surveyTitle'] }}</span>
          <span class="tutor-label">Nama Asisten:</span>
          <div class="tutor-name">{{ $meta['tutor'] }}</div>
        </div>
        <div class="header-right">
          <div class="meta-item"><strong>Lembaga:</strong> {{ $meta['supervisor'] }}</div>
          <div class="meta-item"><strong>Rata-rata:</strong> {{ $meta['avg'] }}</div>
          <div class="meta-item"><strong>Responden:</strong> {{ $meta['respondents'] }}</div>
          <div class="meta-item"><strong>Dibuat:</strong> {{ $meta['generatedAt'] }}</div>
        </div>
      </div>
    </div>

    <div class="card content-card">
      <h2>Ringkasan Per Pertanyaan</h2>
      @forelse($rows as $row)
        @php
          $avg = (float) $row->avg_score;
          $tone = $avg >= 4.5 ? 'success' : ($avg >= 3.5 ? 'warning' : 'danger');
          $counts = [
              1 => (int) ($row->c1 ?? 0),
              2 => (int) ($row->c2 ?? 0),
              3 => (int) ($row->c3 ?? 0),
              4 => (int) ($row->c4 ?? 0),
              5 => (int) ($row->c5 ?? 0),
          ];
          $totalVotes = array_sum($counts) ?: 1;
          $maxCount = max($counts) ?: 1;
        @endphp
        <div class="row">
          <div class="question-header">
            <div class="question-text">{{ $row->question_text }}</div>
            <div class="question-badge">
              <span class="badge badge-{{ $tone }}">
                @if($tone === 'success')
                  Sangat Baik
                @elseif($tone === 'warning')
                  Baik
                @else
                  Perlu Perbaikan
                @endif
              </span>
            </div>
          </div>
          
          <div class="stat-line">
            <span><strong>{{ $row->responses_count }}</strong> responden</span>
            <span>•</span>
            <span>Rata-rata: <strong>{{ number_format($avg, 2) }}</strong></span>
          </div>
          
          <div class="vertical-chart">
            <div class="chart-container">
              @foreach($counts as $value => $count)
                @php
                  $percentage = round(($count / $totalVotes) * 100);
                  $heightPercent = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                  $heightPx = max(4, ($heightPercent / 100) * 72); // max 72px for A4
                @endphp
                <div class="chart-column">
                  <div class="bar-wrapper">
                    <div class="vertical-bar score-{{ $value }}" style="height: {{ $heightPx }}px;">
                      <div class="bar-count">{{ $count }}</div>
                    </div>
                  </div>
                  <span class="bar-label">Skor {{ $value }}</span>
                  <span class="bar-percentage">{{ $percentage }}%</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      @empty
        <p class="muted">Belum ada data untuk filter ini.</p>
      @endforelse
    </div>

    @if(!empty($segment['suggestions']) && count($segment['suggestions']) > 0)
      <div class="card">
        <h2>Top 3 Saran (teks terpanjang)</h2>
        <p class="muted" style="margin:0 0 6px 0;">Diambil hingga 3 saran paling panjang untuk kategori & filter ini.</p>
        <div style="display:flex; flex-direction:column; gap:10px;">
          @foreach($segment['suggestions'] as $s)
            <div>
              <div style="font-weight:700; margin-bottom:4px;">{{ $s['question'] }}</div>
              <div style="line-height:1.5; color:#1f2937;">“{{ $s['text'] }}”</div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    @if(!$loop->last)
      <div class="page-break"></div>
    @endif
  @endforeach
</body>
</html>
