{{-- resources/views/verification/index.blade.php --}}
@extends('layouts.front')

@section('title', 'Cek Verifikasi Dokumen')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center px-4 py-12">
  <div class="w-full max-w-md">
    {{-- Header Card --}}
    <div class="bg-white rounded-t-2xl shadow-xl p-8 border-b-4 border-blue-600">
      <div class="text-center mb-6">
        {{-- Icon --}}
        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4M7.5 4.21a2 2 0 011.4-.58h6.2a2 2 0 011.4.58l2.8 2.8A2 2 0 0120 8.4V17a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h.5z" />
          </svg>
        </div>

        {{-- Title --}}
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Cek Keaslian Dokumen</h1>

        {{-- Subtext --}}
        <div class="ext-center">
          <div class="text-sm text-gray-700">
            <p class="font-medium">Masukkan <strong>kode verifikasi</strong></p>
            <p class="text-gray-600">Penerjemahan dan Surat Rekomendasi</p>
          </div>
        </div>
      </div>
    {{-- Form Card --}}
    <div class="bg-white rounded-b-2xl shadow-xl p-8">
      {{-- Error flash (opsional: tampilkan dari session) --}}
      @if (session('verification_error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg animate-shake" role="alert" aria-live="assertive">
          <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
              <path fill-rule="evenodd"
                d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l5.516 9.81c.75 1.334-.213 3.091-1.742 3.091H4.483c-1.53 0-2.492-1.757-1.743-3.091l5.517-9.81zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-6a1 1 0 00-1 1v2a1 1 0 002 0V8a1 1 0 00-1-1z"
                clip-rule="evenodd" />
            </svg>
            <p class="text-sm font-medium text-red-800">
              {{ session('verification_error') }}
            </p>
          </div>
        </div>
      @endif

      {{-- Form (fallback non-JS: kirim sebagai query, JS akan mengubah ke /verification/{code}) --}}
      <form id="verify-form" method="GET" action="{{ url('/verification') }}" class="space-y-5" novalidate>
        <div>
          <label for="code" class="block text-sm font-semibold text-gray-700 mb-2">
            Kode Verifikasi
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 11c0 1.105-.895 2-2 2H8a2 2 0 110-4h2c1.105 0 2 .895 2 2zm-2 6a2 2 0 110-4h4a2 2 0 110 4h-4z" />
              </svg>
            </div>
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
              class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 font-mono text-lg tracking-wider placeholder-gray-400"
              value="{{ request('code') }}"
              aria-describedby="code-help"
            />

          </div>
          <p id="code-help" class="mt-2 text-xs text-gray-500">
            Tekan <kbd class="px-1 py-0.5 border rounded">Enter</kbd> atau klik tombol di bawah.
          </p>
        </div>

        <button
          type="submit"
          id="go"
          class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-[1.02] active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          Cek Sekarang
        </button>
      </form>
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
  // Utility: normalisasi kode (hapus spasi/dash, uppercase)
  function normalizeCode(raw) {
    return (raw || '')
      .replace(/\s+/g, '')   // hapus spasi, tab, newline
      .trim();
  }

  const input = document.getElementById('code');
  const btn   = document.getElementById('go');
  const form  = document.getElementById('verify-form');

  // Prefill dari ?code=... (kalau ada)
  try {
    const params = new URLSearchParams(window.location.search);
    const pre = params.get('code');
    if (pre && input && !input.value) input.value = normalizeCode(pre);
  } catch(e){/* noop */}

  function redirectWith(code) {
    if (!code) return;
    // Arahkan ke /verification/{code} seperti implementasi sebelumnya
    window.location.href = '{{ url('/verification') }}/' + encodeURIComponent(code);
  }

  function handleSubmit(e) {
    if (e) e.preventDefault();
    const val = normalizeCode(input?.value);
    if (!val) {
      form?.classList.remove('animate-shake');
      // trigger reflow untuk restart animasi
      void form?.offsetWidth;
      form?.classList.add('animate-shake');
      input?.focus();
      return;
    }
    redirectWith(val);
  }

  // Enter & klik
  form?.addEventListener('submit', handleSubmit);
  btn?.addEventListener('click', handleSubmit);

  // Saat mengetik: auto-normalisasi saat blur / paste
  input?.addEventListener('blur', () => input.value = normalizeCode(input.value));
  input?.addEventListener('paste', (e) => {
    setTimeout(() => input.value = normalizeCode(input.value), 0);
  });
</script>
@endpush
