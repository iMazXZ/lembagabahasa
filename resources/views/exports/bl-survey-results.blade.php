@php
    $barColor = [
        'success' => '#10b981',
        'warning' => '#f59e0b',
        'danger'  => '#ef4444',
    ];
@endphp
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Hasil Kuesioner - {{ $meta['surveyTitle'] }}</title>
  <style>
    *{ box-sizing:border-box; }
    body{
      font-family: DejaVu Sans, 'Helvetica', 'Arial', sans-serif;
      font-size: 11px;
      color: #1f2937;
      margin: 18px;
    }
    h1{ margin:0 0 4px 0; font-size:18px; }
    h2{ margin:0 0 6px 0; font-size:14px; }
    .card{
      border:1px solid #e5e7eb;
      border-radius:10px;
      padding:12px 14px;
      margin-bottom:12px;
      background:#fff;
    }
    .muted{ color:#6b7280; }
    .meta-grid{
      display:grid;
      grid-template-columns: repeat(2, minmax(0,1fr));
      gap:6px 10px;
      margin-top:6px;
      font-size:10.5px;
    }
    .bar-wrap{
      background:#f3f4f6;
      border-radius:8px;
      height:12px;
      overflow:hidden;
    }
    .bar{
      height:100%;
      border-radius:8px;
    }
    .row{
      margin-bottom:12px;
      padding-bottom:8px;
      border-bottom:1px solid #f1f5f9;
    }
    .row:last-child{
      border-bottom:none;
      margin-bottom:0;
      padding-bottom:0;
    }
    .question{
      font-weight:700;
      margin-bottom:4px;
    }
    .stat-line{
      font-size:10.5px;
      color:#475569;
      display:flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom:4px;
    }
  </style>
</head>
<body>
  <div class="card" style="margin-bottom:14px;">
    <h1>Hasil Kuesioner</h1>
    <div class="muted">Kategori: {{ $meta['category'] }} • {{ $meta['surveyTitle'] }}</div>
    <div class="meta-grid">
      <div><strong>Tutor</strong>: {{ $meta['tutor'] }}</div>
      <div><strong>Lembaga</strong>: {{ $meta['supervisor'] }}</div>
      <div><strong>Rata-rata</strong>: {{ $meta['avg'] }}</div>
      <div><strong>Responden</strong>: {{ $meta['respondents'] }}</div>
      <div><strong>Dibuat</strong>: {{ $meta['generatedAt'] }}</div>
    </div>
  </div>

  <div class="card">
    <h2>Ringkasan Per Pertanyaan</h2>
    @forelse($rows as $row)
      @php
        $avg = (float) $row->avg_score;
        $pct = min(100, max(0, ($avg / 5) * 100));
        $tone = $avg >= 4.5 ? 'success' : ($avg >= 3.5 ? 'warning' : 'danger');
      @endphp
      <div class="row">
        <div class="question">{{ $row->question_text }}</div>
        <div class="stat-line">
          <span>Rata-rata: {{ number_format($avg, 2) }}</span>
          <span>Responden: {{ $row->responses_count }}</span>
        </div>
        <div class="bar-wrap">
          <div class="bar" style="width: {{ $pct }}%; background: {{ $barColor[$tone] }}"></div>
        </div>
      </div>
    @empty
      <p class="muted">Belum ada data untuk filter ini.</p>
    @endforelse
  </div>
</body>
</html>
