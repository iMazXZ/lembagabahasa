@extends('layouts.front')

@section('title', 'Cek Verifikasi Dokumen')

@push('styles')
<style>
  /* Gradien luas menutup hampir seluruh viewport */
  .hero-wide{
    background:
      radial-gradient(1200px 540px at 20% -10%, rgba(99,102,241,.25), transparent 58%),
      radial-gradient(1100px 640px at 90% 10%, rgba(14,165,233,.25), transparent 58%),
      linear-gradient(180deg, #ffffff 0%, #f1f5f9 55%, #eef2ff 100%);
  }
</style>
@endpush

@section('content')
<section class="hero-wide">
  {{-- min-h memastikan area isi tinggi dan tidak menyisakan “putih” di bawah footer --}}
  <div class="max-w-4xl mx-auto px-4 pt-12 pb-16 min-h-[65vh] md:min-h-[72vh] flex flex-col items-center text-center">
    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-800">
      Verifikasi Keaslian Dokumen
    </h1>
    <p class="mt-3 text-slate-600">
      Masukkan <strong>kode verifikasi</strong> dari dokumen untuk memeriksa keasliannya.
    </p>

    <div class="mt-8 w-full max-w-xl bg-white/85 backdrop-blur border border-slate-200 rounded-2xl p-4 md:p-5 shadow-sm">
      <div class="flex gap-2">
        <input id="code" type="text" placeholder="Contoh: D5A2AGVXSU"
               class="flex-1 border border-slate-300 rounded-xl px-4 py-3 text-lg tracking-wide font-mono focus:outline-none focus:ring-4 focus:ring-blue-100">
        <button id="go" class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-lg">
          Cek
        </button>
      </div>
      <p class="mt-2 text-sm text-slate-500">Tekan Enter atau klik “Cek”.</p>
    </div>

    {{-- spacer ekstra agar terasa lega dari footer --}}
    <div class="mt-auto h-6 md:h-10"></div>
  </div>
</section>
@endsection

@push('scripts')
<script>
  function go(){
    const v = (document.getElementById('code').value || '').trim();
    if (!v) return;
    window.location.href = '{{ url('/verification') }}/' + encodeURIComponent(v);
  }
  document.getElementById('go')?.addEventListener('click', go);
  document.getElementById('code')?.addEventListener('keydown', e => { if(e.key === 'Enter') go(); });
</script>
@endpush
