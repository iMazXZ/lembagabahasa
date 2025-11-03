{{-- resources/views/bl/profile_complete.blade.php --}}
@extends('layouts.front')
@section('title','Lengkapi Biodata')

@push('styles')
<style>
  /* ==== Hero Gradient Animation ==== */
  .hero-profile {
    background: linear-gradient(-45deg, #2563eb, #3b82f6, #6366f1, #4f46e5);
    background-size: 400% 400%;
    animation: gradientFlow 15s ease infinite;
    position: relative;
    overflow: hidden;
  }
  @keyframes gradientFlow {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }

  /* ==== Floating Shapes ==== */
  .hero-profile::before {
    content: '';
    position: absolute;
    inset: 0;
    background: 
      radial-gradient(circle at 20% 30%, rgba(255,255,255,.12), transparent 45%),
      radial-gradient(circle at 80% 70%, rgba(255,255,255,.1), transparent 45%);
    animation: pulse 10s ease-in-out infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
  }

  /* ==== Glassmorphism Card ==== */
  .glass-card {
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 
      0 20px 60px rgba(0, 0, 0, 0.08),
      0 0 0 1px rgba(255,255,255,0.5) inset;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  /* ==== Alert Box ==== */
  .alert-warning {
    border-radius: 0.875rem;
    padding: 1rem;
    font-size: 0.875rem;
    border: 2px solid #f59e0b;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    display: flex;
    gap: 0.75rem;
    align-items: start;
    animation: slideDown 0.4s ease-out;
  }
  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-15px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* ==== Form Group ==== */
  .form-group {
    position: relative;
  }
  .form-label {
    font-weight: 600;
    font-size: 0.875rem;
    color: #374151;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .form-label i {
    color: #3b82f6;
  }
  .required-mark {
    color: #ef4444;
    margin-left: 0.25rem;
  }

  /* ==== Custom Input ==== */
  .custom-input, .custom-select {
    width: 100%;
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 0.875rem 1rem;
    font-size: 0.875rem;
    color: #111827;
    transition: all 0.3s ease;
    font-family: inherit;
  }
  .custom-input:hover, .custom-select:hover {
    border-color: #bfdbfe;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }
  .custom-input:focus, .custom-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
  }
  .custom-input::placeholder {
    color: #9ca3af;
  }

  /* ==== Custom Select ==== */
  .custom-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 0.75rem center;
    background-repeat: no-repeat;
    background-size: 1.25rem;
    padding-right: 2.5rem;
  }

  /* ==== Error Text ==== */
  .error-text {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: #dc2626;
    margin-top: 0.5rem;
  }

  /* ==== Buttons ==== */
  .btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.875rem 1.75rem;
    border-radius: 0.75rem;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }
  .btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
  }
  .btn:hover::before {
    width: 300px;
    height: 300px;
  }
  .btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  }
  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
  }
  .btn-link {
    background: transparent;
    color: #2563eb;
    border: 2px solid #bfdbfe;
    padding: 0.75rem 1.5rem;
  }
  .btn-link:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    transform: translateY(-2px);
  }

  /* ==== Icon Wrapper ==== */
  .icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #2563eb;
    font-size: 1.25rem;
    flex-shrink: 0;
  }

  /* ==== Progress Steps ==== */
  .progress-steps {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.2);
    backdrop-blur-sm;
    border: 1px solid rgba(255,255,255,0.3);
    border-radius: 9999px;
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
  }

  /* ==== Fade In Animation ==== */
  .fade-in {
    animation: fadeInUp 0.6s ease-out forwards;
    opacity: 0;
  }
  .fade-delay-1 { animation-delay: 0.1s; }
  .fade-delay-2 { animation-delay: 0.2s; }
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* ==== Info Banner ==== */
  .info-banner {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 2px solid #bfdbfe;
    border-radius: 0.875rem;
    padding: 1rem;
    display: flex;
    gap: 0.75rem;
    align-items: start;
  }
  .info-banner-text {
    font-size: 0.875rem;
    color: #1e40af;
    line-height: 1.6;
  }
</style>
@endpush

@section('content')
  {{-- Hero Section --}}
  <div class="hero-profile text-white">
    <div class="max-w-3xl mx-auto px-4 py-10 md:py-12 relative z-10">
      <div class="mb-4">
        <div class="progress-steps inline-flex">
          <i class="fa-solid fa-user-circle text-sm"></i>
          <span>Langkah Penting</span>
        </div>
      </div>
      
      <h1 class="text-3xl md:text-4xl font-extrabold mb-3">
        Lengkapi Biodata Anda
      </h1>
      <p class="text-lg text-blue-100 font-medium">
        Isi data berikut dengan lengkap agar dapat mengerjakan quiz dan mengakses semua fitur.
      </p>
    </div>
  </div>

  {{-- Main Content --}}
  <div class="max-w-3xl mx-auto px-4 py-8">
    
    {{-- Warning Message --}}
    @if (session('warning'))
      <div class="alert-warning mb-6 fade-in">
        <div class="icon-wrapper flex-shrink-0" style="width: 2rem; height: 2rem; font-size: 1rem; background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e;">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="flex-1">
          <div class="font-semibold mb-1">Perhatian!</div>
          <div>{!! session('warning') !!}</div>
        </div>
      </div>
    @endif

    {{-- Main Form Card --}}
    <div class="glass-card rounded-2xl fade-in fade-delay-1">
      <div class="px-6 py-8 md:px-8 md:py-10">

        {{-- Form --}}
        <form method="POST" action="{{ route('bl.profile.complete.submit') }}" class="space-y-6">
          @csrf
          <input type="hidden" name="next" value="{{ $next }}">

          {{-- Program Studi --}}
          <div class="form-group">
            <label for="prody_id" class="form-label">
              <i class="fa-solid fa-graduation-cap"></i>
              Program Studi
              <span class="required-mark">*</span>
            </label>
            <select name="prody_id" id="prody_id" required class="custom-select">
              <option value="">— Pilih Program Studi —</option>
              @foreach ($prodis as $p)
                <option value="{{ $p->id }}" @selected(old('prody_id', $user->prody_id)==$p->id)>
                  {{ $p->name }}
                </option>
              @endforeach
            </select>
            @error('prody_id')
              <p class="error-text">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $message }}
              </p>
            @enderror
          </div>

          {{-- SRN --}}
          <div class="form-group">
            <label for="srn" class="form-label">
              <i class="fa-solid fa-id-card"></i>
              NPM (Nomor Pokok Mahasiswa)
              <span class="required-mark">*</span>
            </label>
            <input 
              type="text" 
              name="srn" 
              id="srn"
              value="{{ old('srn', $user->srn) }}" 
              required
              placeholder="Contoh: 123456789"
              class="custom-input"
            >
            @error('srn')
              <p class="error-text">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $message }}
              </p>
            @enderror
          </div>

          {{-- Tahun Angkatan --}}
          <div class="form-group">
            <label for="year" class="form-label">
              <i class="fa-solid fa-calendar-days"></i>
              Tahun Angkatan
              <span class="required-mark">*</span>
            </label>
            <input 
              type="number" 
              name="year" 
              id="year"
              value="{{ old('year', $user->year) }}" 
              required 
              min="2015" 
              max="{{ now()->year }}"
              placeholder="Contoh: {{ now()->year }}"
              class="custom-input"
            >
            @error('year')
              <p class="error-text">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $message }}
              </p>
            @enderror
          </div>

          {{-- Info Banner --}}
          <div class="info-banner">
            <div class="icon-wrapper flex-shrink-0" style="width: 2rem; height: 2rem; font-size: 0.875rem;">
              <i class="fa-solid fa-info-circle"></i>
            </div>
            <div class="info-banner-text">
              <strong>Catatan:</strong> Data yang Anda isi akan digunakan untuk keperluan administrasi dan evaluasi.
            </div>
          </div>

          {{-- Action Buttons --}}
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-4 border-t border-gray-100">
            <button type="submit" class="btn btn-primary flex-1 sm:flex-initial justify-center">
              <i class="fa-solid fa-check relative z-10"></i>
              <span class="relative z-10">Simpan & Lanjut</span>
              <i class="fa-solid fa-arrow-right relative z-10"></i>
            </button>

            <a href="{{ route('filament.admin.pages.biodata') }}" class="btn btn-link justify-center">
              <i class="fa-solid fa-pen-to-square relative z-10"></i>
              <span class="relative z-10">Edit Lengkap via Biodata</span>
            </a>
          </div>
        </form>

      </div>
    </div>
  </div>
@endsection