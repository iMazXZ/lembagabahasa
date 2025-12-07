{{-- resources/views/verification/show.blade.php --}}
@extends('layouts.front')

@section('title', $vm['title'] ?? 'Verifikasi Dokumen')

@push('styles')
{{-- Tidak butuh custom CSS lagi karena sudah full Tailwind + Glassmorphism --}}
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

{{-- Hero Removed as per request --}}


{{-- Main --}}
<section class="py-12 md:py-16 bg-gradient-to-b from-gray-50 to-white">
  <div class="max-w-6xl mx-auto px-4">
    {{-- Header Sederhana --}}
    <div class="mb-10 text-center">
      <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4 tracking-tight">
        {{ $vm['title'] ?? 'Verifikasi Dokumen' }}
      </h1>
      
      <div class="flex flex-col items-center gap-4">
        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full font-bold text-sm shadow-sm border {{ $status === 'VALID' ? 'bg-green-100 text-green-700 border-green-200' : ($status === 'PENDING' ? 'bg-yellow-100 text-yellow-700 border-yellow-200' : 'bg-red-100 text-red-700 border-red-200') }}">
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
          <p class="text-gray-600 text-sm md:text-base max-w-2xl text-center bg-white rounded-lg px-4 py-2 border border-gray-200 shadow-sm">
            {{ $vm['reason'] }}
          </p>
        @endif
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      {{-- Kartu Utama --}}
      <div class="lg:col-span-2 space-y-8">
        {{-- Detail Dokumen --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
          <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-800">Detail Dokumen</h2>
          </div>
          
          <div class="p-6 md:p-8 space-y-6">
            {{-- Info Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
              <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Nama Pemohon</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ $vm['applicant_name'] ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">NPM</dt>
                <dd class="text-lg font-semibold text-gray-900 font-mono">{{ $vm['srn'] ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Program Studi</dt>
                <dd class="text-lg font-semibold text-gray-900">{{ $vm['prody'] ?? '-' }}</dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500 mb-1">Status Dokumen</dt>
                <dd class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $status === 'VALID' ? 'bg-green-100 text-green-700' : ($status === 'PENDING' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                  {{ $vm['status_text'] ?? '-' }}
                </dd>
              </div>

               @if(($vm['type'] ?? null) === 'penerjemahan')
                 @php $done = $vm['done_at'] ?? null; @endphp
                 <div class="md:col-span-2">
                   <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Selesai</dt>
                   <dd class="text-gray-900 font-medium">@if($done) {{ $done->translatedFormat('d F Y, H:i') }} WIB @else - @endif</dd>
                 </div>
               @endif

               @if(($vm['type'] ?? null) === 'ept')
                 <div>
                   <dt class="text-sm font-medium text-gray-500 mb-1">Nomor Surat</dt>
                   <dd class="text-gray-900 font-medium">{{ $vm['nomor_surat'] ?? '-' }}</dd>
                 </div>
                 <div>
                   <dt class="text-sm font-medium text-gray-500 mb-1">Tanggal Surat</dt>
                   <dd class="text-gray-900 font-medium">{{ optional($vm['tanggal_surat'])->translatedFormat('d F Y') ?? '-' }}</dd>
                 </div>
               @endif
            </div>

            {{-- Kode Verifikasi --}}
            <div class="bg-blue-50/50 rounded-xl p-5 border border-blue-100">
               <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-2">Kode Verifikasi</h3>
               <div class="flex items-center justify-between gap-4">
                 <code class="text-xl md:text-2xl font-bold text-blue-900 font-mono tracking-wide">{{ $vm['verification_code'] ?? '-' }}</code>
                 @if(!empty($vm['verification_code']))
                   <button type="button" class="group relative inline-flex items-center justify-center p-2 rounded-lg text-blue-600 hover:bg-blue-100 transition" data-copy="{{ $vm['verification_code'] }}" title="Salin Kode">
                     <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                   </button>
                 @endif
               </div>
               <span class="text-xs text-blue-400 mt-2 block" id="copy-status"></span>
            </div>

            {{-- Tautan PDF --}}
            @if(!empty($pdfViewUrl))
              <div>
                <a href="{{ $pdfViewUrl }}" target="_blank" class="inline-flex items-center justify-center w-full md:w-auto px-6 py-3 bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-800 transition shadow-lg shadow-gray-200 hover:shadow-xl transform hover:-translate-y-0.5">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586L15 6v4z"/></svg>
                  Unduh / Lihat Dokumen Asli (PDF)
                </a>
              </div>
            @endif
          </div>
        </div>

        {{-- Tabel Nilai EPT --}}
        @if(($vm['type'] ?? null) === 'ept' && is_array($vm['scores']))
          <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
              </div>
              <h3 class="text-xl font-bold text-gray-800">Rincian Nilai EPT</h3>
            </div>
            <div class="overflow-x-auto">
              <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-900 font-semibold uppercase tracking-wider text-xs">
                  <tr>
                    <th class="px-6 py-4">Jenis Tes</th>
                    <th class="px-6 py-4">Tanggal Tes</th>
                    <th class="px-6 py-4 text-right">Skor</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @foreach($vm['scores'] as $row)
                  @php
                    $raw = $row['tanggal'] ?? null;
                    $tgl = '-';

                    if ($raw instanceof \Carbon\CarbonInterface) {
                        $tgl = $raw->format('d/m/Y');
                    } elseif (is_string($raw) && !empty($raw)) {
                        try {
                            $tgl = \Carbon\Carbon::parse($raw)->format('d/m/Y');
                        } catch (\Throwable $e) {}
                    }
                  @endphp
                  <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $row['label'] ?? '-' }}</td>
                    <td class="px-6 py-4">{{ $tgl }}</td>
                    <td class="px-6 py-4 text-right font-bold text-blue-600 text-base">{{ $row['nilai'] ?? '-' }}</td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endif
      </div>

      {{-- Sidebar --}}
      <aside class="space-y-6">
        {{-- Cek Kode Lain --}}
        <div class="bg-white rounded-2xl shadow-lg shadow-gray-200/50 border border-gray-100 p-6">
          <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Cek Dokumen Lain
          </h3>
          <div class="bg-gray-50 rounded-xl p-1.5 border border-gray-200 focus-within:ring-2 focus-within:ring-blue-100 focus-within:border-blue-400 transition flex items-center" id="check-another">
            <input id="inp-code" type="text" class="bg-transparent border-none focus:ring-0 w-full px-3 py-2 text-gray-800 font-mono font-bold placeholder-gray-400" placeholder="Kode..." autocapitalize="off" autocomplete="off">
            <button id="btn-go" class="bg-white text-gray-600 hover:text-blue-600 p-2 rounded-lg shadow-sm border border-gray-200 hover:border-blue-200 transition">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
          </div>
          <p class="text-xs text-center text-gray-400 mt-3">Input kode verifikasi dari dokumen.</p>
        </div>

        {{-- Tautan Verifikasi --}}
        @if(!empty($vm['verification_url']))
          <div class="bg-blue-50/50 rounded-2xl border border-blue-100 p-6">
            <h3 class="font-bold text-blue-900 mb-2 text-sm uppercase tracking-wide">Public Link</h3>
            <a href="{{ $vm['verification_url'] }}" target="_blank" class="block w-full break-all text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline transition">
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
  // Copy code
  document.addEventListener('click', function(e){
    const btn = e.target.closest('button[data-copy]'); if(!btn) return;
    const value = btn.getAttribute('data-copy') || ''; if(!value) return;
    
    // Feedback visual
    const originalIcon = btn.innerHTML;
    
    navigator.clipboard.writeText(value).then(()=>{
      const live = document.getElementById('copy-status');
      btn.innerHTML = `<svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>`;
      if(live) live.textContent = 'Berhasil disalin!';
      
      setTimeout(()=>{ 
        btn.innerHTML = originalIcon; 
        if(live) live.textContent=''; 
      }, 1500);
    });
  });

  // Normalisasi kode
  function normalizeCode(raw){ return (raw || '').toString().trim().replace(/\s+/g,'').toLowerCase(); }

  // Navigasi cek kode
  function goToVerification(){
    const input = document.getElementById('inp-code');
    const box = document.getElementById('check-another');
    const code = normalizeCode(input?.value);
    
    if(!code){
      // Shake effect pakai tailwind classes native jika mau, atau biarkan focus saja
      input?.focus();
      input?.classList.add('ring-2', 'ring-red-300');
      setTimeout(() => input?.classList.remove('ring-2', 'ring-red-300'), 500);
      return;
    }
    
    // Ganti placeholder URL
    const tpl = @json(route('verification.show', ['code' => 'CODE_PLACEHOLDER']));
    const url = tpl.replace('CODE_PLACEHOLDER', encodeURIComponent(code));
    window.location.href = url;
  }

  document.getElementById('btn-go')?.addEventListener('click', goToVerification);
  document.getElementById('inp-code')?.addEventListener('keydown', (e)=>{ if(e.key==='Enter') goToVerification(); });
</script>
@endpush
