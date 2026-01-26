@extends('layouts.front')

@section('title', 'Cek Verifikasi Dokumen')

@section('content')
{{-- Hero Section with Verification Form --}}
<div class="relative min-h-[80vh] flex items-center overflow-hidden">
    {{-- Background matching hero --}}
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 to-indigo-900"></div>
        {{-- Dot pattern --}}
        <div class="absolute inset-0" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 30px 30px; opacity: 0.1;"></div>
        {{-- Glow effects --}}
        <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-blue-400 rounded-full blur-3xl opacity-20"></div>
        <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-80 h-80 bg-indigo-400 rounded-full blur-3xl opacity-20"></div>
    </div>

    <div class="relative z-10 w-full max-w-6xl mx-auto px-4 py-8 lg:py-16">
        <div class="flex flex-col lg:flex-row items-center gap-6 lg:gap-20">
            
            {{-- Left: Info --}}
            <div class="flex-1 text-white text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-blue-100 text-xs font-medium mb-4 backdrop-blur-md">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    Sistem Verifikasi Resmi
                </div>
                <h1 class="text-2xl md:text-4xl lg:text-5xl font-black tracking-tight mb-2 lg:mb-4">
                    Cek <span class="text-blue-300">Keaslian</span> Dokumen
                </h1>
                <p class="text-blue-100 text-sm lg:text-lg leading-relaxed mb-4 lg:mb-8 max-w-lg">
                    Verifikasi keaslian sertifikat, surat rekomendasi, dan hasil terjemahan dari Lembaga Bahasa UM Metro.
                </p>
                
                {{-- Features --}}
                <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
                    <div class="flex items-center gap-1.5 text-xs text-blue-200">
                        <i class="fa-solid fa-check-circle text-emerald-400 text-[10px]"></i>
                        Sertifikat EPT
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-blue-200">
                        <i class="fa-solid fa-check-circle text-emerald-400 text-[10px]"></i>
                        Surat Rekomendasi
                    </div>
                    <div class="flex items-center gap-1.5 text-xs text-blue-200">
                        <i class="fa-solid fa-check-circle text-emerald-400 text-[10px]"></i>
                        Hasil Terjemahan
                    </div>
                </div>
            </div>
            
            {{-- Right: Form Card --}}
            <div class="w-full max-w-sm lg:max-w-md">
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl lg:rounded-3xl p-5 lg:p-8 border border-white/20 shadow-2xl">
                    
                    {{-- Icon --}}
                    <div class="w-12 h-12 lg:w-16 lg:h-16 mx-auto mb-4 lg:mb-6 bg-white/20 backdrop-blur rounded-xl lg:rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-qrcode text-xl lg:text-3xl text-white"></i>
                    </div>
                    
                    <div class="text-center mb-4 lg:mb-6">
                        <h2 class="text-lg lg:text-xl font-bold text-white mb-1">Masukkan Kode</h2>
                        <p class="text-blue-200 text-xs lg:text-sm">Kode verifikasi tertera pada dokumen Anda</p>
                    </div>
                    
                    {{-- Error --}}
                    @if (session('verification_error'))
                        <div class="mb-6 bg-red-500/20 border border-red-400/30 rounded-xl p-4 animate-shake">
                            <div class="flex items-center gap-3 text-red-200">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <p class="text-sm font-medium">{{ session('verification_error') }}</p>
                            </div>
                        </div>
                    @endif
                    
                    {{-- Form --}}
                    <form id="verify-form" method="GET" action="{{ url('/verification') }}" novalidate>
                        <div class="mb-4">
                            <input
                                type="text"
                                id="code"
                                name="code"
                                maxlength="64"
                                required
                                autofocus
                                autocapitalize="off"
                                autocomplete="one-time-code"
                                spellcheck="false"
                                inputmode="text"
                                pattern="[A-Za-z0-9\-]{1,64}"
                                value="{{ request('code') }}"
                                class="w-full px-4 py-4 bg-white/10 border border-white/20 rounded-xl text-white text-center font-mono text-lg tracking-wider placeholder-blue-300/50 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-transparent transition-all"
                            />
                        </div>
                        
                        <button
                            type="submit"
                            id="go"
                            class="w-full flex items-center justify-center gap-2 px-6 py-4 bg-white text-blue-700 font-bold rounded-xl shadow-lg hover:bg-blue-50 transition-all transform hover:-translate-y-0.5 active:scale-[0.98]"
                        >
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Verifikasi Sekarang
                        </button>
                    </form>
                    
                    <p class="text-center text-blue-200/70 text-xs mt-4">
                        Tekan <kbd class="px-1.5 py-0.5 bg-white/10 rounded text-xs">Enter</kbd> atau klik tombol di atas
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Custom Styles --}}
<style>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}
.animate-shake { animation: shake 0.5s ease-in-out; }
</style>
@endsection

@push('scripts')
<script>
  function normalizeCode(raw) {
    return (raw || '').replace(/\s+/g, '').trim();
  }

  const input = document.getElementById('code');
  const btn   = document.getElementById('go');
  const form  = document.getElementById('verify-form');

  try {
    const params = new URLSearchParams(window.location.search);
    const pre = params.get('code');
    if (pre && input && !input.value) input.value = normalizeCode(pre);
  } catch(e){}

  function redirectWith(code) {
    if (!code) return;
    window.location.href = '{{ url('/verification') }}/' + encodeURIComponent(code);
  }

  function handleSubmit(e) {
    if (e) e.preventDefault();
    const val = normalizeCode(input?.value);
    if (!val) {
      form?.classList.remove('animate-shake');
      void form?.offsetWidth;
      form?.classList.add('animate-shake');
      input?.focus();
      return;
    }
    redirectWith(val);
  }

  form?.addEventListener('submit', handleSubmit);
  btn?.addEventListener('click', handleSubmit);
  input?.addEventListener('blur', () => input.value = normalizeCode(input.value));
  input?.addEventListener('paste', (e) => {
    setTimeout(() => input.value = normalizeCode(input.value), 0);
  });
</script>
@endpush
