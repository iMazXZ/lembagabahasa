{{-- resources/views/bl/index.blade.php --}}
@extends('layouts.front')
@section('title','Basic Listening')

@section('content')
@php
  $user = auth()->user();
  $groupNumber = $user?->nomor_grup_bl;
  $prodyName = $user?->prody?->name;
@endphp

{{-- HERO with integrated profile --}}
<div class="bg-gradient-to-br from-blue-600 to-indigo-800 text-white">
  <div class="max-w-7xl mx-auto px-4 py-6 md:py-10">
    
    {{-- Header --}}
    <div class="mb-6">
      <div class="inline-block px-2.5 py-1 rounded-full bg-white/20 text-xs mb-2">
        📚 Platform Pembelajaran
      </div>
      <h1 class="text-2xl md:text-4xl font-bold mb-1">Basic Listening</h1>
      <p class="text-blue-100 text-sm md:text-base">Kegiatan Wajib Mahasiswa Semester 1, UM Metro</p>
    </div>

    {{-- Profile Card Integrated --}}
    @auth
      <div class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/20 p-4">
        <div class="flex flex-col gap-4">
          
          {{-- User Info --}}
          <div class="flex-1">
            @php
                /** @var \App\Models\User $user */
                $avatarUrl = \Filament\Facades\Filament::getUserAvatarUrl($user);
            @endphp

            <div class="flex items-center justify-between gap-2.5 mb-3">
              <div class="flex items-center gap-2.5">
                @if($avatarUrl)
                  <img
                    src="{{ $avatarUrl }}"
                    alt="Foto {{ $user->name }}"
                    class="w-10 h-10 rounded-full object-cover border-2 border-white/30"
                    loading="lazy"
                    referrerpolicy="no-referrer"
                  />
                @else
                  <div class="w-10 h-10 rounded-full bg-white/20 border-2 border-white/30 flex items-center justify-center text-white font-bold text-lg">
                    {{ strtoupper(mb_substr($user->name, 0, 1, 'UTF-8')) }}
                  </div>
                @endif

                <div>
                  <p class="text-xs text-blue-200">Halo,</p>
                  <h2 class="text-base md:text-lg font-bold">{{ $user->name }}</h2>
                </div>
              </div>

              <a href="{{ route('filament.admin.pages.biodata') }}"
                class="flex-shrink-0 p-2 bg-white/20 text-white rounded-full hover:bg-white/30 transition-colors"
                title="Edit Biodata">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536M4 20h4l9.768-9.768a2.5 2.5 0 10-3.536-3.536L4 16v4z"/>
                </svg>
              </a>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
              <div class="bg-white/10 rounded-md px-2.5 py-2 border border-white/20">
                <span class="text-blue-200 text-[10px] block mb-0.5">NPM</span>
                <div class="font-semibold text-xs md:text-sm">{{ $user->srn ?? '-' }}</div>
              </div>
              <div class="bg-white/10 rounded-md px-2.5 py-2 border border-white/20">
                <span class="text-blue-200 text-[10px] block mb-0.5">Prodi</span>
                <div class="font-semibold text-xs md:text-sm truncate">{{ $prodyName ?? '-' }}</div>
              </div>
              <div class="bg-white/10 rounded-md px-2.5 py-2 border border-white/20 col-span-2 md:col-span-1">
                <span class="text-blue-200 text-[10px] block mb-0.5">Angkatan</span>
                <div class="font-semibold text-xs md:text-sm">{{ $user->year ?? '-' }}</div>
              </div>
            </div>
          </div>

          {{-- Group & Action Section --}}
          <div class="border-t border-white/20 pt-4 flex flex-col gap-3">
            
            {{-- Nomor Grup Display/Form --}}
            @if (is_null($groupNumber))
              <div class="bg-white/10 rounded-md p-3 border border-white/20">
                <h3 class="font-semibold text-sm mb-1">Isi Nomor Grup</h3>
                <p class="text-xs text-blue-200 mb-2">Pilih grup dari asisten pengajar</p>
                <form action="{{ route('bl.groupNumber.update') }}" method="POST" class="flex gap-2">
                  @csrf
                  <select name="nomor_grup_bl" required
                          class="flex-1 rounded-md border-white/30 bg-white/20 text-white text-sm shadow-sm focus:border-white/50 focus:ring-white/30 backdrop-blur-sm">
                    <option value="" class="text-gray-900">Pilih Grup</option>
                    <option value="1" class="text-gray-900">Grup 1</option>
                    <option value="2" class="text-gray-900">Grup 2</option>
                    <option value="3" class="text-gray-900">Grup 3</option>
                    <option value="4" class="text-gray-900">Grup 4</option>
                  </select>
                  <button type="submit"
                          class="px-3 py-2 bg-white text-blue-600 rounded-md hover:bg-blue-50 transition-colors text-sm font-medium whitespace-nowrap">
                    Simpan
                  </button>
                </form>
                @error('nomor_grup_bl')
                  <div class="text-xs text-red-200 mt-2">{{ $message }}</div>
                @enderror
              </div>
            @else
              <div class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-between">
                <div class="bg-white/10 rounded-md px-3 py-2 border border-white/20 flex-1">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-xs font-semibold text-blue-200">Nomor Grup</h3>
                      <p class="text-base font-bold">Grup {{ $groupNumber }}</p>
                    </div>
                    <form action="{{ route('bl.groupNumber.update') }}" method="POST" class="flex gap-1.5">
                      @csrf
                      <select name="nomor_grup_bl" required
                              class="rounded-md border-white/30 bg-white/20 text-white shadow-sm focus:border-white/50 focus:ring-white/30 backdrop-blur-sm text-xs py-1">
                        <option value="1" {{ $groupNumber == 1 ? 'selected' : '' }} class="text-gray-900">Grup 1</option>
                        <option value="2" {{ $groupNumber == 2 ? 'selected' : '' }} class="text-gray-900">Grup 2</option>
                        <option value="3" {{ $groupNumber == 3 ? 'selected' : '' }} class="text-gray-900">Grup 3</option>
                        <option value="4" {{ $groupNumber == 4 ? 'selected' : '' }} class="text-gray-900">Grup 4</option>
                      </select>
                      <button type="submit"
                              class="px-2.5 py-1 bg-white/20 text-white rounded-md hover:bg-white/30 transition-colors text-xs font-medium">
                        Ubah
                      </button>
                    </form>
                  </div>
                </div>
                
                <a href="{{ route('bl.history') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-white text-blue-600 rounded-md hover:bg-blue-50 transition-colors text-sm font-medium">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Riwayat Skor
                </a>
              </div>
            @endif
          </div>
        </div>
      </div>
    @endauth

    @guest
      <div class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/20 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h3 class="text-lg font-semibold mb-0.5">Silakan Login</h3>
            <p class="text-blue-200 text-sm">
              Login untuk mengerjakan kuis dan melihat riwayat skor.
            </p>
          </div>
          <a href="{{ route('login') }}"
             class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white text-blue-600 rounded-md hover:bg-blue-50 transition-colors text-sm font-semibold whitespace-nowrap">
             Login
          </a>
        </div>
      </div>
    @endguest

    {{-- Nilai & Sertifikat Panel --}}
    @auth
      @php
        $year = (int) ($user->year ?? 0);
        $grade = null;
        if ($year >= 2025) {
            $grade = \App\Models\BasicListeningGrade::query()
                ->where('user_id', $user->id)
                ->where('user_year', $year)
                ->first();
        }

        $attendance = is_numeric($grade?->attendance) ? (float) $grade->attendance : null;
        $finalTest  = is_numeric($grade?->final_test)  ? (float) $grade->final_test  : null;
        $daily = \App\Support\BlCompute::dailyAvgForUser($user->id, $year);
        $daily = is_numeric($daily) ? (float) $daily : null;

        $finalNumeric = $grade?->final_numeric_cached;
        $finalLetter  = $grade?->final_letter_cached;
        if ($finalNumeric === null && is_numeric($daily) && is_numeric($attendance) && is_numeric($finalTest)) {
            $finalNumeric = \App\Support\BlGrading::computeFinalNumeric([
                'attendance' => $attendance,
                'daily'      => $daily,
                'final_test' => $finalTest,
            ]);
            $finalLetter = $finalNumeric !== null ? \App\Support\BlGrading::toLetter($finalNumeric) : null;
        }

        $survey = \App\Models\BasicListeningSurvey::query()
            ->where('require_for_certificate', true)
            ->where('target', 'final')
            ->where('is_active', true)
            ->latest('id')
            ->first();

        $surveyRequired = $survey ? $survey->isOpen() : false;
        $surveyDone = false;
        if ($surveyRequired) {
            $surveyDone = \App\Models\BasicListeningSurveyResponse::where([
                'survey_id'  => $survey->id,
                'user_id'    => $user->id,
                'session_id' => null,
            ])->whereNotNull('submitted_at')->exists();
        }

        $baseEligible = is_numeric($attendance) && is_numeric($finalTest);
        $canDownload  = $baseEligible && (! $surveyRequired || $surveyDone);

        $surveyUrl   = \Illuminate\Support\Facades\Route::has('bl.survey.required') ? route('bl.survey.required') : null;
        $downloadUrl = \Illuminate\Support\Facades\Route::has('bl.certificate.download') ? route('bl.certificate.download') : null;
        $previewUrl  = $downloadUrl ? ($downloadUrl . '?inline=1') : null;

        $showPanel = $year >= 2025 && $grade !== null && $baseEligible;
      @endphp

      @if($showPanel)
        <div class="mt-4 bg-white/10 backdrop-blur-sm rounded-lg border border-white/20 p-4">
          <h3 class="text-base font-semibold mb-1">Nilai & Sertifikat</h3>
          <p class="text-blue-200 text-xs mb-3">
            Lihat rekap nilai dan unduh sertifikat Anda.
          </p>

          {{-- Kartu nilai --}}
          <div class="grid grid-cols-3 gap-2 mb-3">
            <div class="bg-white/10 rounded-md px-2 py-2 border border-white/20">
              <div class="text-[9px] uppercase text-blue-200 mb-0.5">Daily (1-5)</div>
              <div class="text-base font-semibold text-white">
                {{ is_numeric($daily) ? number_format($daily, 2) : '—' }}
              </div>
            </div>
            <div class="bg-white/10 rounded-md px-2 py-2 border border-white/20">
              <div class="text-[9px] uppercase text-blue-200 mb-0.5">Final Test</div>
              <div class="text-base font-semibold text-white">
                {{ is_numeric($finalTest) ? number_format($finalTest, 0) : '—' }}
              </div>
            </div>
            <div class="bg-white/10 rounded-md px-2 py-2 border border-white/20">
              <div class="text-[9px] uppercase text-blue-200 mb-0.5">Total</div>
              @php $letter = $finalLetter ? strtoupper($finalLetter) : null; @endphp
              <div class="text-base font-semibold text-white">
                {{ is_numeric($finalNumeric) ? number_format($finalNumeric, 2) : '—' }}
                {!! $letter ? ' <span class="text-blue-200 text-xs">(' . e($letter) . ')</span>' : '' !!}
              </div>
            </div>
          </div>

          {{-- Survey notice --}}
          @if(($surveyRequired ?? false) && !($surveyDone ?? false))
            <div class="mb-3 border border-amber-200/50 bg-amber-50/20 text-yellow-100 rounded-md p-2.5">
              <div class="flex items-start justify-between gap-2 flex-wrap">
                <div class="space-y-0.5 flex-1">
                  <div class="font-medium text-white text-sm">Kuesioner Wajib</div>
                  <p class="text-xs text-blue-100">
                    Isi kuesioner akhir sebelum mengunduh sertifikat.
                  </p>
                </div>
                @if(!empty($surveyUrl))
                  <a href="{{ $surveyUrl }}"
                     class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-blue-700 rounded-md hover:bg-blue-50 text-xs font-medium whitespace-nowrap">
                    Isi Kuesioner
                  </a>
                @endif
              </div>
            </div>
          @endif

          {{-- Actions --}}
          <div class="flex flex-wrap items-center gap-2">
            @if($canDownload && !empty($previewUrl))
              <a href="{{ $previewUrl }}"
                 class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white/20 text-white rounded-md hover:bg-white/30 text-xs font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 10l4.553-2.276A2 2 0 0122 9.528V16a2 2 0 01-2 2H9m6-8V6a2 2 0 00-2-2H6m9 6l-6 6" />
                </svg>
                Preview
              </a>
            @endif

            @if($canDownload && !empty($downloadUrl))
              <a href="{{ $downloadUrl }}"
                 class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white text-blue-700 rounded-md hover:bg-blue-50 text-xs font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                </svg>
                Download PDF
              </a>
            @else
              <p class="text-xs text-blue-100">
                @if(($surveyRequired ?? false) && !($surveyDone ?? false))
                  Silakan isi kuesioner terlebih dahulu.
                @else
                  Hubungi tutor jika nilai Final Test belum diinput.
                @endif
              </p>
            @endif
          </div>
        </div>
      @endif
    @endauth
  </div>
</div>

{{-- Content Section --}}
<div class="max-w-7xl mx-auto px-4 py-6">
  {{-- Flash Messages --}}
  @if (session('success'))
    <div class="mb-4 rounded-lg border-l-4 border-emerald-500 bg-emerald-50 px-3 py-2.5 text-emerald-800 text-sm">
      {{ session('success') }}
    </div>
  @endif
  @if (session('warning'))
    <div class="mb-4 rounded-lg border-l-4 border-amber-500 bg-amber-50 px-3 py-2.5 text-amber-800 text-sm">
      {{ session('warning') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="mb-4 rounded-lg border-l-4 border-red-500 bg-red-50 px-3 py-2.5 text-red-800 text-sm">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Sessions Grid --}}
  @if(isset($sessions) && count($sessions) > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($sessions as $index => $s)
        @php
          $colors = [
            ['bg' => 'bg-yellow-100', 'border' => 'border-yellow-200', 'shadow' => 'shadow-yellow-200/50'],
            ['bg' => 'bg-pink-100', 'border' => 'border-pink-200', 'shadow' => 'shadow-pink-200/50'],
            ['bg' => 'bg-blue-100', 'border' => 'border-blue-200', 'shadow' => 'shadow-blue-200/50'],
            ['bg' => 'bg-green-100', 'border' => 'border-green-200', 'shadow' => 'shadow-green-200/50'],
            ['bg' => 'bg-purple-100', 'border' => 'border-purple-200', 'shadow' => 'shadow-purple-200/50'],
            ['bg' => 'bg-orange-100', 'border' => 'border-orange-200', 'shadow' => 'shadow-orange-200/50'],
          ];
          $color = $colors[$index % count($colors)];
          $rotation = ['rotate-1', '-rotate-1', 'rotate-2', '-rotate-2'];
          $rotate = $rotation[$index % count($rotation)];
        @endphp

        <a href="{{ route('bl.session.show', $s) }}"
           class="group relative block {{ $color['bg'] }} {{ $color['border'] }} border-2 rounded-lg p-6 {{ $rotate }} hover:rotate-0 transition-all duration-300 hover:scale-105 shadow-md hover:shadow-xl {{ $color['shadow'] }} hover:z-10">

          {{-- Pin/Thumbtack Effect --}}
          <div class="absolute -top-3 left-1/2 transform -translate-x-1/2">
            <div class="w-6 h-6 bg-red-500 rounded-full shadow-md relative">
              <div class="absolute inset-1 bg-red-400 rounded-full"></div>
            </div>
          </div>

          {{-- Status Badge --}}
          <div class="absolute top-4 right-4">
            @if($s->isOpen())
              <span class="px-3 py-1 rounded-full bg-green-500 text-white text-xs font-bold shadow-sm">
                OPEN
              </span>
            @else
              <span class="px-3 py-1 rounded-full bg-gray-400 text-white text-xs font-bold shadow-sm">
                CLOSED
              </span>
            @endif
          </div>

          {{-- Session Number --}}
          <div class="mb-3">
            <span class="inline-block px-3 py-1 bg-white/70 rounded-md text-xs font-bold text-gray-700 uppercase tracking-wider shadow-sm">
              📝 Pertemuan {{ $s->number <= 5 ? $s->number : 'UAS' }}
            </span>
          </div>

          {{-- Title --}}
          <h3 class="text-lg font-bold text-gray-800 mb-4 line-clamp-2 min-h-[3.5rem] group-hover:text-gray-900">
            {{ $s->title }}
          </h3>

          {{-- Duration --}}
          <div class="flex items-center gap-2 mb-3">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700">{{ $s->duration_minutes }} menit</span>
          </div>

          {{-- Schedule Info --}}
          @if($s->opens_at || $s->closes_at)
            <div class="mt-4 pt-3 border-t-2 border-dashed border-gray-300/50 space-y-1">
              @if($s->opens_at)
                <div class="flex items-start gap-2 text-xs text-gray-600">
                  <span class="font-semibold">🔓</span>
                  <span>{{ $s->opens_at->format('d M Y, H:i') }}</span>
                </div>
              @endif
              @if($s->closes_at)
                <div class="flex items-start gap-2 text-xs text-gray-600">
                  <span class="font-semibold">🔒</span>
                  <span>{{ $s->closes_at->format('d M Y, H:i') }}</span>
                </div>
              @endif
            </div>
          @endif

          {{-- Hover Effect Indicator --}}
          <div class="absolute bottom-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
          </div>
        </a>
      @endforeach
    </div>
  @else
    <div class="text-center py-16">
      <div class="text-6xl mb-4">📋</div>
      <p class="text-gray-600 text-lg font-medium">Belum ada pertemuan tersedia</p>
      <p class="text-gray-500 text-sm mt-2">Pertemuan akan ditampilkan setelah ditambahkan oleh admin</p>
    </div>
  @endif
</div>

@endsection