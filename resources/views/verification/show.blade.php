@extends('layouts.front')

@section('title', $vm['title'] ?? 'Verifikasi Dokumen')

@push('styles')
<style>
  .backdrop-gradient{
    background:
      radial-gradient(1200px 500px at 20% -10%, rgba(99,102,241,.22), transparent 55%),
      radial-gradient(1000px 600px at 90% 10%, rgba(14,165,233,.22), transparent 55%),
      linear-gradient(180deg, #ffffff 0%, #f8fafc 60%, #ffffff 100%);
  }
  .content-card{ background: rgba(255,255,255,.92); backdrop-filter: blur(8px); border: 1px solid #e5e7eb; border-radius: 24px; box-shadow: 0 10px 30px rgba(2,6,23,.06); }
  .badge{ display:inline-flex;align-items:center;gap:.5rem;font-weight:800;border-radius:999px;padding:.5rem .9rem; box-shadow:0 1px 0 rgba(0,0,0,.04) inset, 0 1px 2px rgba(0,0,0,.08); }
  .ok{background:#e8f7ef;color:#127a42}.warn{background:#fff7e6;color:#8a5b00}.err{background:#fdeaea;color:#a61b1b}
  .kvs{display:grid;grid-template-columns:180px 12px 1fr;gap:10px 12px}
  @media (max-width:640px){.kvs{grid-template-columns:130px 12px 1fr}}
  .tbl{border-collapse:collapse;width:100%}
  .tbl th,.tbl td{border:1px solid #cbd5e1;padding:8px 10px;text-align:left}
</style>
@endpush

@section('content')
<section class="backdrop-gradient">
  {{-- Header judul + badge --}}
  <div class="max-w-6xl mx-auto px-4 pt-10 pb-6 text-center">
    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-800">
      {{ $vm['title'] ?? 'Verifikasi Dokumen' }}
    </h1>
    @php
      $status = $vm['status'] ?? 'INVALID';
      $klass  = $status === 'VALID' ? 'ok' : ($status === 'PENDING' ? 'warn' : 'err');
      $label  = $status === 'VALID' ? 'Terverifikasi' : ($status === 'PENDING' ? 'Menunggu Verifikasi' : 'Tidak Valid');
    @endphp
    <div class="mt-3 flex flex-col items-center">
      <span class="badge {{ $klass }} text-base">
        @if($status === 'VALID')
          <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        @elseif($status === 'PENDING')
          <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>
        @else
          <svg class="w-5 h-5 mr-2 inline-block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6m0-6l6 6"/></svg>
        @endif
        {{ $label }}
      </span>
      <p class="text-slate-600 mt-2">{{ $vm['reason'] ?? '' }}</p>
    </div>
  </div>

  {{-- KARTU KONTEN --}}
  <div class="max-w-6xl mx-auto px-4 pb-14">
    <div class="content-card p-5 md:p-7">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Detail utama --}}
        <div class="lg:col-span-2 bg-white/80 rounded-2xl border border-slate-200 p-5 shadow-sm">
          <div class="kvs text-slate-800">
            <div class="font-semibold">Nama Pemohon</div><div>:</div><div>{{ $vm['applicant_name'] ?? '-' }}</div>
            <div class="font-semibold">NPM</div><div>:</div><div>{{ $vm['srn'] ?? '-' }}</div>
            <div class="font-semibold">Program Studi</div><div>:</div><div>{{ $vm['prody'] ?? '-' }}</div>
            <div class="font-semibold">Status</div><div>:</div><div>{{ $vm['status_text'] ?? '-' }}</div>

            {{-- Khusus penerjemahan: tanggal selesai --}}
            @if(($vm['type'] ?? null) === 'penerjemahan')
              <div class="font-semibold">Tanggal Selesai</div><div>:</div>
              <div>
                {{ optional($vm['done_at'])->translatedFormat('d F Y, H:i') ?? '-' }} WIB
              </div>
            @endif

            {{-- Khusus EPT: nomor & tanggal surat --}}
            @if(($vm['type'] ?? null) === 'ept')
              <div class="font-semibold">Nomor Surat</div><div>:</div><div>{{ $vm['nomor_surat'] ?? '-' }}</div>
              <div class="font-semibold">Tanggal Surat</div><div>:</div>
              <div>{{ optional($vm['tanggal_surat'])->translatedFormat('d F Y') ?? '-' }}</div>
            @endif

            <div class="font-semibold">Kode Verifikasi</div><div>:</div>
            <div>
              <span class="inline-flex items-center gap-2 border border-dashed border-blue-300 bg-blue-50/60 rounded-xl px-3 py-2">
                <span class="font-mono tracking-wide">{{ $vm['verification_code'] ?? '-' }}</span>
                @if(!empty($vm['verification_code']))
                  <button class="px-2 py-1 rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition"
                          data-copy="{{ $vm['verification_code'] }}">Copy</button>
                @endif
              </span>
            </div>
          </div>

          {{-- Khusus EPT: tampilkan ringkasan nilai --}}
          @if(($vm['type'] ?? null) === 'ept' && is_array($vm['scores']))
            <div class="mt-6">
              <div class="font-semibold mb-2">Rincian Nilai EPT</div>
              <table class="tbl">
                <thead><tr><th>Ulangan</th><th>Tanggal</th><th>Nilai</th></tr></thead>
                <tbody>
                  @foreach($vm['scores'] as $row)
                    <tr>
                      <td>{{ $row['label'] }}</td>
                      <td>{{ optional($row['tanggal'])->format('d/m/Y') ?? '-' }}</td>
                      <td>{{ $row['nilai'] ?? '-' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif

          {{-- Aksi --}}
          <div class="flex flex-wrap gap-3 mt-6">
            @if(!empty($vm['pdf_url']))
              <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-white bg-blue-600 hover:bg-blue-700 border border-blue-500 shadow-sm"
                 href="{{ $vm['pdf_url'] }}" target="_blank" rel="noopener">
                Download PDF Resmi
              </a>
            @endif
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50"
               href="{{ route('front.home') }}">
              Kembali ke Beranda
            </a>
            @if(!empty($vm['verification_code']))
            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50"
               href="mailto:info@lembagabahasa.site?subject=Klarifikasi%20Verifikasi%20Dokumen&body=Kode:%20{{ $vm['verification_code'] }}">
              Laporkan Masalah
            </a>
            @endif
          </div>
        </div>

        {{-- Sidebar --}}
        <aside class="bg-white/80 rounded-2xl border border-slate-200 p-5 shadow-sm">
          <div class="font-semibold mb-2">Cek Kode Lain</div>
          <div class="flex gap-2">
            <input id="inp-code" type="text"
                   class="w-full border border-slate-300 rounded-xl px-3 py-2 focus:outline-none focus:ring-4 focus:ring-blue-100"
                   placeholder="Masukkan kode verifikasi">
            <button id="btn-go" class="px-3 py-2 rounded-xl border border-slate-300 hover:bg-slate-50">Cek</button>
          </div>

          @if(!empty($vm['verification_url']))
            <hr class="my-4">
            <div class="font-semibold mb-2">Tautan Verifikasi</div>
            <a class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-dashed border-blue-300 text-blue-700 hover:bg-blue-50"
               href="{{ $vm['verification_url'] }}" target="_blank" rel="noopener">
              Buka Tautan
            </a>
          @endif
        </aside>
      </div>
    </div>

    <div class="h-12 md:h-16"></div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  // Copy kode
  document.addEventListener('click', function(e){
    const btn = e.target.closest('button[data-copy]');
    if(!btn) return;
    const v = btn.getAttribute('data-copy') || '';
    if(!v) return;
    navigator.clipboard.writeText(v).then(()=>{
      const old = btn.textContent; btn.textContent = 'Copied!';
      setTimeout(()=> btn.textContent = 'Copy', 1200);
    });
  });
  // Cek kode lain
  function go(){
    const el = document.getElementById('inp-code');
    const code = (el?.value || '').trim();
    if(!code) return;
    window.location.href = '{{ url('/verification') }}/' + encodeURIComponent(code);
  }
  document.getElementById('btn-go')?.addEventListener('click', go);
  document.getElementById('inp-code')?.addEventListener('keydown', e => { if(e.key === 'Enter') go(); });
</script>
@endpush
