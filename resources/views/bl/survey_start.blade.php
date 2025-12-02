{{-- resources/views/bl/survey_start.blade.php --}}
@extends('layouts.front')
@section('title','Mulai Kuesioner Basic Listening')

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css">
  <style>
    /* Hide global navbar & footer for this flow */
    body > nav,
    body > footer{
      display: none !important;
    }
    .intro-block{
      text-align: center;
    }

    /* ==== Minimal Layout ==== */
    body {
      background: radial-gradient(circle at 10% 20%, #eef2ff 0%, transparent 25%),
                  radial-gradient(circle at 90% 10%, #e0f2fe 0%, transparent 22%),
                  #f8fafc;
    }
    .page-wrap{
      max-width: 760px;
      margin: 0 auto;
      padding: 48px 16px 48px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      gap: 28px;
    }

    /* ==== Compact Card ==== */
    .glass-card {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      box-shadow: 0 18px 40px rgba(79, 70, 229, 0.08);
      border-radius: 18px;
      transition: transform .2s ease, box-shadow .2s ease;
      width: min(520px, 100%);
      padding: 32px 28px;
    }
    .glass-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 22px 48px rgba(79, 70, 229, 0.12);
    }

    /* ==== Form Elements Styling ==== */
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
      color: #6366f1;
    }

    /* ==== Tom Select Custom Styling ==== */
    .ts-wrapper.multi .ts-control {
      background: #ffffff;
      border: 2px solid #e5e7eb;
      border-radius: 0.75rem;
      padding: 0.5rem;
      min-height: 3rem;
      transition: all 0.3s ease;
    }
    .ts-wrapper.multi .ts-control:hover {
      border-color: #c7d2fe;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .ts-wrapper.multi.focus .ts-control {
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }
    .ts-wrapper .ts-control .item {
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      color: white;
      border: none;
      border-radius: 0.5rem;
      padding: 0.375rem 0.75rem;
      font-weight: 500;
      font-size: 0.875rem;
      margin: 0.125rem;
    }
    .ts-wrapper .ts-control .item .remove {
      border-left: 1px solid rgba(255,255,255,0.3);
      padding-left: 0.5rem;
      margin-left: 0.5rem;
    }
    .ts-dropdown {
      border: 2px solid #e5e7eb;
      border-radius: 0.75rem;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      margin-top: 0.25rem;
    }
    .ts-dropdown .option.active {
      background: #eef2ff;
      color: #4f46e5;
    }

    /* ==== Select Styling ==== */
    .custom-select {
      background: #ffffff;
      border: 2px solid #e5e7eb;
      border-radius: 0.75rem;
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
      color: #111827;
      transition: all 0.3s ease;
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
      background-position: right 0.75rem center;
      background-repeat: no-repeat;
      background-size: 1.25rem;
      padding-right: 2.5rem;
    }
    .custom-select:hover {
      border-color: #c7d2fe;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .custom-select:focus {
      outline: none;
      border-color: #6366f1;
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }

    /* ==== Alert Boxes ==== */
    .alert-box {
      border-radius: 0.75rem;
      padding: 1rem;
      font-size: 0.875rem;
      border: 2px solid;
      display: flex;
      gap: 0.75rem;
      align-items: start;
      animation: slideDown 0.4s ease-out;
    }
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .alert-success {
      background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
      color: #065f46;
      border-color: #10b981;
    }
    .alert-info {
      background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
      color: #1e40af;
      border-color: #3b82f6;
    }
    .alert-warning {
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      color: #92400e;
      border-color: #f59e0b;
    }
    .alert-error {
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      color: #991b1b;
      border-color: #ef4444;
    }

    /* ==== Buttons ==== */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      font-weight: 600;
      font-size: 0.875rem;
      padding: 0.75rem 1.5rem;
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
      background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
      color: white;
      border: none;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
    }
    .btn-secondary {
      background: white;
      color: #4b5563;
      border: 2px solid #e5e7eb;
    }
    .btn-secondary:hover {
      background: #f9fafb;
      border-color: #d1d5db;
      transform: translateY(-2px);
    }

    /* ==== Confirm Dialog ==== */
    .confirm-backdrop{
      position: fixed;
      inset: 0;
      background: rgba(15,23,42,0.55);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 60;
    }
    .confirm-card{
      background: #fff;
      border-radius: 16px;
      width: min(480px, 92vw);
      padding: 20px 22px;
      box-shadow: 0 18px 48px rgba(15,23,42,0.25);
    }
    .confirm-actions{
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-top: 18px;
    }

    /* ==== Info Badge ==== */
    .info-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
      color: #6b21a8;
      font-size: 0.75rem;
      font-weight: 600;
      padding: 0.375rem 0.75rem;
      border-radius: 9999px;
      border: 1px solid #c4b5fd;
    }

    /* ==== Helper Text ==== */
    .helper-text {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      font-size: 0.75rem;
      color: #6b7280;
      margin-top: 0.375rem;
    }
    .helper-text i {
      color: #9ca3af;
    }

    /* ==== Icon Wrapper ==== */
    .icon-wrapper {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 2.5rem;
      height: 2.5rem;
      border-radius: 0.75rem;
      background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
      color: #6366f1;
      font-size: 1.25rem;
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
  </style>
@endpush

@section('content')
  <div class="page-wrap">
    <div class="mb-6 intro-block">
      <p class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-indigo-600 bg-indigo-50 border border-indigo-100 px-3 py-1 rounded-full">
        <i class="fa-solid fa-clipboard-list text-sm"></i>
        Langkah 1 dari 3
      </p>
      <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 mt-3">Pilih Tutor & Supervisor</h1>
      <p class="text-sm md:text-base text-slate-600 mt-2 max-w-2xl">Tentukan pembimbing Anda sebelum memulai kuesioner Basic Listening. Pilih maksimal 2 tutor dan 1 supervisor.</p>
    </div>
    
    {{-- Flash Messages --}}
    @foreach (['success','info','warning','error'] as $f)
      @if (session($f))
        <div class="alert-box alert-{{ $f }} mb-6 fade-in">
          <div class="icon-wrapper flex-shrink-0" style="width: 2rem; height: 2rem; font-size: 1rem;">
            <i class="fa-solid fa-{{ $f==='success'?'circle-check':($f==='info'?'info-circle':($f==='warning'?'triangle-exclamation':'circle-xmark')) }}"></i>
          </div>
          <div class="flex-1">
            <div class="font-semibold mb-0.5">
              {{ $f==='success'?'Berhasil!':($f==='info'?'Informasi':($f==='warning'?'Perhatian':'Error!')) }}
            </div>
            <div>{{ session($f) }}</div>
          </div>
        </div>
      @endif
    @endforeach

    {{-- Validation Errors --}}
    @if($errors->any())
      <div class="alert-box alert-error mb-6 fade-in">
        <div class="icon-wrapper flex-shrink-0" style="width: 2rem; height: 2rem; font-size: 1rem;">
          <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div class="flex-1">
          <div class="font-bold mb-2">Periksa kembali isian Anda:</div>
          <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    @php
      $hasTutors = isset($tutors) && $tutors->count()>0;
      $hasSupervisors = isset($supervisors) && $supervisors->count()>0;
      $prefillTutorIds = (array) (($prefill['tutor_ids'] ?? []) ?: []);
      $prefillSupervisorId = (int) ($prefill['supervisor_id'] ?? 0);
    @endphp

    {{-- Main Card --}}
    <div class="glass-card rounded-2xl fade-in fade-delay-1">
      <div class="px-6 py-8 md:px-8 md:py-10">
        
        {{-- Header --}}
        <div class="mb-8">
        <div class="flex flex-col items-center text-center gap-3 mb-4">
          <div class="icon-wrapper flex-shrink-0">
            <i class="fa-solid fa-users"></i>
          </div>
          <div class="flex-1">
            <h2 class="text-xl font-bold text-gray-900 mb-1">
              Konfirmasi Pembimbing
            </h2>
            <p class="text-sm text-gray-600 leading-relaxed">Pilih tutor & supervisor Anda.</p>
          </div>
        </div>

          {{-- Warning if no data --}}
          @if(!$hasTutors || !$hasSupervisors)
            <div class="alert-box alert-warning">
              <div class="icon-wrapper flex-shrink-0" style="width: 2rem; height: 2rem; font-size: 1rem;">
                <i class="fa-solid fa-exclamation-triangle"></i>
              </div>
              <div class="flex-1">
                @if(!$hasTutors && !$hasSupervisors)
                  Data <strong>tutor</strong> dan <strong>supervisor</strong> belum tersedia. Silakan hubungi administrator.
                @elseif(!$hasTutors)
                  Data <strong>tutor</strong> belum tersedia. Silakan hubungi administrator.
                @else
                  Data <strong>supervisor</strong> belum tersedia. Silakan hubungi administrator.
                @endif
              </div>
            </div>
          @endif
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('bl.survey.start.submit') }}" class="space-y-6">
          @csrf

          {{-- Tutor Selection --}}
          <div class="form-group">
            <label for="tutorSelect" class="form-label">
              <i class="fa-solid fa-chalkboard-user"></i>
              Tutor
              <span class="info-badge ml-auto">
                <i class="fa-solid fa-info-circle"></i>
                Maks 2
              </span>
            </label>
            
            @if($hasTutors)
              <select id="tutorSelect" name="tutor_ids[]" multiple placeholder="Ketik untuk mencari tutor…" autocomplete="off" class="w-full">
                @foreach($tutors as $t)
                  @php $pre = in_array($t->id, old('tutor_ids', $prefillTutorIds), true); @endphp
                  <option value="{{ $t->id }}" @selected($pre)>{{ $t->name }}</option>
                @endforeach
              </select>
              <div class="helper-text">
                <i class="fa-solid fa-lightbulb"></i>
                Pilih Nama Tutor Basic Listening
              </div>
            @else
              <div class="bg-gray-50 border-2 border-gray-200 rounded-lg px-4 py-3 text-sm text-gray-500">
                <i class="fa-solid fa-inbox mr-2"></i>
                Belum ada data tutor tersedia
              </div>
            @endif
            
            @error('tutor_ids')
              <p class="mt-2 text-xs text-rose-600 flex items-center gap-1">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $message }}
              </p>
            @enderror
          </div>

          {{-- Supervisor Selection --}}
          <div class="form-group">
            <label for="supervisor_id" class="form-label">
              <i class="fa-solid fa-user-tie"></i>
              Supervisor
              <span class="info-badge ml-auto">
                <i class="fa-solid fa-asterisk text-xs"></i>
                Wajib
              </span>
            </label>
            
            <select id="supervisor_id" name="supervisor_id" class="custom-select w-full" required>
              <option value="" disabled @selected((int)old('supervisor_id', $prefillSupervisorId)===0)>
                — Pilih supervisor —
              </option>
              @foreach($supervisors as $s)
                @php $sel = (int) old('supervisor_id', $prefillSupervisorId) === (int) $s->id; @endphp
                <option value="{{ $s->id }}" @selected($sel)>{{ $s->name }}</option>
              @endforeach
            </select>
            
            <div class="helper-text">
              <i class="fa-solid fa-lightbulb"></i>
              *Tanyakan ke Tutor
            </div>
            
            @error('supervisor_id')
              <p class="mt-2 text-xs text-rose-600 flex items-center gap-1">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $message }}
              </p>
            @enderror
          </div>

          {{-- Action Buttons --}}
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
            <button type="submit" class="btn btn-primary">
              <span class="relative z-10">Mulai</span>
              <i class="fa-solid fa-arrow-right relative z-10"></i>
            </button>
          </div>
        </form>

      </div>
    </div>
    
    {{-- Confirm Dialog --}}
    <div class="confirm-backdrop hidden" id="confirmModal">
      <div class="confirm-card">
        <div class="flex flex-col items-center text-center gap-3">
          <div class="icon-wrapper" style="background: linear-gradient(135deg,#eef2ff,#e0e7ff); color:#4f46e5;">
            <i class="fa-solid fa-user-check"></i>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-bold text-slate-900 mb-1">Konfirmasi Pembimbing</h3>
            <p class="text-sm text-slate-600 leading-relaxed">
              Sudah yakin dengan pilihan Anda?
            </p>
            <div class="mt-3 space-y-1 text-sm text-slate-700">
              <div><span class="font-semibold text-slate-900">Tutor:</span> <span id="confirmTutor">-</span></div>
              <div><span class="font-semibold text-slate-900">Supervisor:</span> <span id="confirmSupervisor">-</span></div>
            </div>
          </div>
        </div>
        <div class="confirm-actions">
          <button type="button" class="btn btn-secondary" id="confirmCancel">
            <i class="fa-solid fa-rotate-left"></i>
            <span class="relative z-10">Periksa Lagi</span>
          </button>
          <button type="button" class="btn btn-primary" id="confirmProceed">
            <span class="relative z-10">Ya, Lanjut</span>
            <i class="fa-solid fa-arrow-right relative z-10"></i>
          </button>
        </div>
      </div>
    </div>

  </div>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const confirmModal = document.getElementById('confirmModal');
      const confirmTutor = document.getElementById('confirmTutor');
      const confirmSupervisor = document.getElementById('confirmSupervisor');
      const confirmCancel = document.getElementById('confirmCancel');
      const confirmProceed = document.getElementById('confirmProceed');
      const form = document.querySelector('form[action*="/bl/survey/start"]');
      const supervisorSelect = document.getElementById('supervisor_id');
      let pendingSubmit = false;

      const el = document.getElementById('tutorSelect');
      let ts;
      if (el) {
        ts = new TomSelect(el, {
          maxItems: 2,
          plugins: ['remove_button'],
          create: false,
          persist: false,
          placeholder: 'Ketik untuk mencari tutor…',
          render: {
            no_results: function(data, escape) {
              return '<div class="no-results px-3 py-2 text-sm text-gray-500">Tidak ada hasil untuk "' + escape(data.input) + '"</div>';
            }
          }
        });
      }

      const openConfirm = () => {
        const tutorValues = ts ? ts.getValue() : [];
        const tutorNames = (Array.isArray(tutorValues) ? tutorValues : [tutorValues])
          .filter(Boolean)
          .map(v => ts?.options?.[v]?.text || `Tutor #${v}`);
        const supervisorName = supervisorSelect?.options[supervisorSelect.selectedIndex]?.text || 'Tidak ada';

        confirmTutor.textContent = tutorNames.length ? tutorNames.join(', ') : 'Tidak ada';
        confirmSupervisor.textContent = supervisorName;
        confirmModal?.classList.remove('hidden');
      };

      form?.addEventListener('submit', (e) => {
        if (pendingSubmit) return;
        const val = ts ? ts.getValue() : [];
        const count = Array.isArray(val) ? val.length : (val ? 1 : 0);
        if (count < 1) {
          e.preventDefault();
          alert('⚠️ Pilih minimal 1 tutor sebelum melanjutkan.');
          ts?.focus();
          return;
        }
        e.preventDefault();
        openConfirm();
      });

      confirmCancel?.addEventListener('click', () => {
        confirmModal?.classList.add('hidden');
      });
      confirmModal?.addEventListener('click', (e) => {
        if (e.target === confirmModal) confirmModal.classList.add('hidden');
      });
      confirmProceed?.addEventListener('click', () => {
        pendingSubmit = true;
        confirmModal?.classList.add('hidden');
        form?.submit();
      });
    });
  </script>
@endpush
