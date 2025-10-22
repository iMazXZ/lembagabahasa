{{-- resources/views/verification/show.blade.php --}}
@extends('layouts.front')

@section('title', $vm['title'] ?? 'Verifikasi Dokumen')

@push('styles')
<style>
  .hero-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
  }
  .hero-gradient::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: 
      radial-gradient(circle at 20% 50%, rgba(255,255,255,0.1) 0%, transparent 50%),
      radial-gradient(circle at 80% 80%, rgba(255,255,255,0.1) 0%, transparent 50%);
    animation: pulse 8s ease-in-out infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
  }
  
  .status-card {
    background: white;
    border-radius: 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
  }
  
  .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 700;
    border-radius: 999px;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  
  .badge-valid {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
  }
  
  .badge-pending {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
  }
  
  .badge-invalid {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
  }
  
  .info-grid {
    display: grid;
    gap: 1rem;
  }
  
  .info-row {
    display: grid;
    grid-template-columns: 160px 1fr;
    gap: 1rem;
    padding: 1rem;
    border-radius: 12px;
    background: linear-gradient(to right, #f9fafb 0%, #ffffff 100%);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
  }
  
  .info-row:hover {
    background: linear-gradient(to right, #f3f4f6 0%, #ffffff 100%);
    transform: translateX(4px);
  }
  
  .info-label {
    font-weight: 600;
    color: #64748b;
    display: flex;
    align-items: center;
  }
  
  .info-value {
    color: #1e293b;
    font-weight: 500;
    display: flex;
    align-items: center;
  }
  
  .verification-code {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 2px dashed #3b82f6;
    border-radius: 16px;
    padding: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
  }
  
  .code-text {
    font-family: 'Monaco', 'Courier New', monospace;
    font-size: 1.125rem;
    font-weight: 700;
    color: #1e40af;
    letter-spacing: 1px;
  }
  
  .btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }
  
  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
  }
  
  .btn-secondary {
    background: white;
    color: #64748b;
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 2px solid #e2e8f0;
    transition: all 0.3s ease;
  }
  
  .btn-secondary:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    transform: translateY(-2px);
  }
  
  .sidebar-card {
    background: white;
    border-radius: 20px;
    padding: 1.5rem;
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
  }
  
  .input-code {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-family: 'Monaco', 'Courier New', monospace;
    font-size: 0.875rem;
    transition: all 0.3s ease;
  }
  
  .input-code:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
  }
  
  .table-modern {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }
  
  .table-modern thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
  }
  
  .table-modern th {
    padding: 1rem;
    font-weight: 600;
    text-align: left;
  }
  
  .table-modern td {
    padding: 1rem;
    border-bottom: 1px solid #f1f5f9;
  }
  
  .table-modern tbody tr:last-child td {
    border-bottom: none;
  }
  
  .table-modern tbody tr:hover {
    background: #f8fafc;
  }
  
  @keyframes shake {
    0%, 100% { transform: translateX(0); }
    10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
    20%, 40%, 60%, 80% { transform: translateX(8px); }
  }
  
  .animate-shake {
    animation: shake 0.5s ease-in-out;
  }
  
  @media (max-width: 768px) {
    .info-row {
      grid-template-columns: 120px 1fr;
      gap: 0.75rem;
      padding: 0.75rem;
    }
    
    .status-badge {
      padding: 0.625rem 1.25rem;
      font-size: 0.875rem;
    }
    
    .code-text {
      font-size: 0.875rem;
    }
  }
</style>
@endpush

@section('content')
{{-- Hero Section --}}
<section class="hero-gradient py-12 md:py-16">
  <div class="max-w-6xl mx-auto px-4">
    <div class="text-center text-white mb-8">
      <h1 class="text-3xl md:text-5xl font-bold mb-4 tracking-tight">
        {{ $vm['title'] ?? 'Verifikasi Dokumen' }}
      </h1>
      <p class="text-lg md:text-xl opacity-90">
        Sistem Verifikasi Digital Terpercaya
      </p>
    </div>
    
    {{-- Status Badge --}}
    @php
      $status = $vm['status'] ?? 'INVALID';
      $badgeClass = $status === 'VALID' ? 'badge-valid' : ($status === 'PENDING' ? 'badge-pending' : 'badge-invalid');
      $label = $status === 'VALID' ? 'Terverifikasi' : ($status === 'PENDING' ? 'Menunggu Verifikasi' : 'Tidak Valid');
    @endphp
    
    <div class="flex flex-col items-center gap-3">
      <span class="status-badge {{ $badgeClass }}">
        @if($status === 'VALID')
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        @elseif($status === 'PENDING')
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        @else
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        @endif
        {{ $label }}
      </span>
      
      @if(!empty($vm['reason']))
        <p class="text-white/90 text-base md:text-lg max-w-2xl">{{ $vm['reason'] }}</p>
      @endif
    </div>
  </div>
</section>

{{-- Main Content --}}
<section class="py-8 md:py-12 bg-gradient-to-b from-gray-50 to-white">
  <div class="max-w-6xl mx-auto px-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      {{-- Main Information Card --}}
      <div class="lg:col-span-2">
        <div class="status-card p-6 md:p-8">
          <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Detail Dokumen
          </h2>
          
          {{-- Information Grid --}}
          <div class="info-grid mb-6">
            <div class="info-row">
              <div class="info-label">Nama Pemohon</div>
              <div class="info-value">{{ $vm['applicant_name'] ?? '-' }}</div>
            </div>
            
            <div class="info-row">
              <div class="info-label">NPM</div>
              <div class="info-value">{{ $vm['srn'] ?? '-' }}</div>
            </div>
            
            <div class="info-row">
              <div class="info-label">Program Studi</div>
              <div class="info-value">{{ $vm['prody'] ?? '-' }}</div>
            </div>
            
            <div class="info-row">
              <div class="info-label">Status</div>
              <div class="info-value">{{ $vm['status_text'] ?? '-' }}</div>
            </div>
            
            @if(($vm['type'] ?? null) === 'penerjemahan')
              <div class="info-row">
                <div class="info-label">Tanggal Selesai</div>
                <div class="info-value">{{ optional($vm['done_at'])->translatedFormat('d F Y, H:i') ?? '-' }} WIB</div>
              </div>
            @endif
            
            @if(($vm['type'] ?? null) === 'ept')
              <div class="info-row">
                <div class="info-label">Nomor Surat</div>
                <div class="info-value">{{ $vm['nomor_surat'] ?? '-' }}</div>
              </div>
              
              <div class="info-row">
                <div class="info-label">Tanggal Surat</div>
                <div class="info-value">{{ optional($vm['tanggal_surat'])->translatedFormat('d F Y') ?? '-' }}</div>
              </div>
            @endif
          </div>
          
          {{-- Verification Code --}}
          <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Kode Verifikasi</h3>
            <div class="verification-code">
              <span class="code-text" id="code-value">{{ $vm['verification_code'] ?? '-' }}</span>
              @if(!empty($vm['verification_code']))
                <button type="button" class="btn-secondary py-2 px-4 text-sm" data-copy="{{ $vm['verification_code'] }}">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                  </svg>
                  Copy
                </button>
              @endif
            </div>
          </div>
          
          {{-- EPT Scores Table --}}
          @if(($vm['type'] ?? null) === 'ept' && is_array($vm['scores']))
            <div class="mb-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800">Rincian Nilai EPT</h3>
                @if(!empty($vm['pdf_url']))
                  <a class="text-purple-600 hover:text-purple-700 font-semibold text-sm" href="{{ $vm['pdf_url'] }}" target="_blank" rel="noopener">
                    Lihat PDF →
                  </a>
                @endif
              </div>
              
              <div class="table-modern">
                <table class="w-full">
                  <thead>
                    <tr>
                      <th>Ulangan</th>
                      <th>Tanggal</th>
                      <th>Nilai</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($vm['scores'] as $row)
                      <tr>
                        <td>{{ $row['label'] }}</td>
                        <td>{{ optional($row['tanggal'])->format('d/m/Y') ?? '-' }}</td>
                        <td><strong>{{ $row['nilai'] ?? '-' }}</strong></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif
          
          {{-- Action Buttons --}}
          <div class="flex flex-wrap gap-3">
            @if(!empty($vm['pdf_url']))
              <a class="btn-primary" href="{{ $vm['pdf_url'] }}" target="_blank" rel="noopener">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download PDF
              </a>
            @endif
            
            @if(!empty($vm['verification_url']))
              <button type="button" id="btn-share" class="btn-secondary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
                Bagikan
              </button>
            @endif
            
            <a class="btn-secondary" href="{{ route('front.home') }}">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
              Beranda
            </a>
            
            @if(!empty($vm['verification_code']))
              <a class="btn-secondary" href="mailto:info@lembagabahasa.site?subject=Klarifikasi%20Verifikasi%20Dokumen&body=Kode:%20{{ $vm['verification_code'] }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Laporkan
              </a>
            @endif
          </div>
        </div>
      </div>
      
      {{-- Sidebar --}}
      <aside class="space-y-6">
        {{-- Check Another Code --}}
        <div class="sidebar-card">
          <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Cek Kode Lain
          </h3>
          <div id="check-another" class="flex gap-2">
            <input
              id="inp-code"
              type="text"
              class="input-code"
              placeholder="Masukkan kode..."
              autocapitalize="off"
              autocomplete="one-time-code"
              spellcheck="false"
              maxlength="64">
            <button id="btn-go" class="btn-secondary px-4">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
          <p class="text-xs text-gray-500 mt-3">
            <strong>Case-sensitive:</strong> Perhatikan huruf besar/kecil dan tanda minus (-)
          </p>
        </div>
        
        {{-- Verification Link --}}
        @if(!empty($vm['verification_url']))
          <div class="sidebar-card">
            <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
              </svg>
              Tautan Verifikasi
            </h3>
            <a class="text-sm text-purple-600 hover:text-purple-700 break-all block p-3 bg-purple-50 rounded-lg border-2 border-dashed border-purple-200 hover:bg-purple-100 transition"
               href="{{ $vm['verification_url'] }}" target="_blank" rel="noopener">
              {{ $vm['verification_url'] }}
            </a>
          </div>
        @endif
        
        {{-- Info Box --}}
        <div class="sidebar-card bg-gradient-to-br from-purple-50 to-blue-50 border-2 border-purple-100">
          <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-purple-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
              <h4 class="font-semibold text-gray-800 mb-1">Bantuan</h4>
              <p class="text-sm text-gray-600">
                Jika Anda mengalami masalah dengan verifikasi dokumen, silakan hubungi tim kami untuk bantuan lebih lanjut.
              </p>
            </div>
          </div>
        </div>
      </aside>
      
    </div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  // Copy code functionality
  document.addEventListener('click', function(e) {
    const btn = e.target.closest('button[data-copy]');
    if (!btn) return;
    
    const value = btn.getAttribute('data-copy') || '';
    if (!value) return;
    
    navigator.clipboard.writeText(value).then(() => {
      const originalText = btn.innerHTML;
      btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>Copied!`;
      setTimeout(() => btn.innerHTML = originalText, 1500);
    });
  });

  // Share functionality
  (function() {
    const shareBtn = document.getElementById('btn-share');
    const url = @json($vm['verification_url'] ?? null);
    const title = @json($vm['title'] ?? 'Verifikasi Dokumen');
    
    if (shareBtn && url) {
      shareBtn.addEventListener('click', async () => {
        try {
          if (navigator.share) {
            await navigator.share({ title, url });
          } else {
            await navigator.clipboard.writeText(url);
            const originalText = shareBtn.innerHTML;
            shareBtn.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>Disalin!`;
            setTimeout(() => shareBtn.innerHTML = originalText, 1500);
          }
        } catch (err) {
          // User cancelled
        }
      });
    }
  })();

  // Check another code
  function normalizeCode(raw) {
    return (raw || '').replace(/\s+/g, '').trim();
  }
  
  function goToVerification() {
    const input = document.getElementById('inp-code');
    const code = normalizeCode(input?.value);
    
    if (!code) {
      const box = document.getElementById('check-another');
      box?.classList.remove('animate-shake');
      void box?.offsetWidth;
      box?.classList.add('animate-shake');
      input?.focus();
      return;
    }
    
    window.location.href = '{{ url('/verification') }}/' + encodeURIComponent(code);
  }
  
  document.getElementById('btn-go')?.addEventListener('click', goToVerification);
  document.getElementById('inp-code')?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') goToVerification();
  });
</script>
@endpush