{{-- resources/views/bl/schedule-monitor.blade.php --}}
@extends('layouts.front')
@section('title', 'Kalender Basic Listening (Mingguan)')

@push('styles')
<style>
  /* --- Sticky Note Design --- */
  .note {
    position: relative;
    border-radius: 1.25rem;
    box-shadow: 0 8px 20px rgba(2,6,23,.08), 0 2px 0 rgba(255,255,255,.85) inset;
    transform: rotate(-0.5deg);
    transition: all .25s ease;
  }
  .note:hover { 
    transform: rotate(0deg) translateY(-4px);
    box-shadow: 0 16px 32px rgba(2,6,23,.14), 0 2px 0 rgba(255,255,255,.9) inset;
  }
  .note .pin {
    position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
    width: 20px; height: 20px; border-radius: 9999px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    border: 3px solid #fff; box-shadow: 0 3px 8px rgba(239,68,68,.35);
    animation: bob 3s ease-in-out infinite;
  }
  .note::before {
    content: ''; position: absolute; top: 10px; left: 50%; transform: translateX(-50%);
    width: 24px; height: 12px; border-radius: 999px;
    background: radial-gradient(ellipse at center, rgba(0,0,0,.12), transparent 65%);
    filter: blur(2px);
  }
  .note .rip {
    position: absolute; bottom: -1px; left: 0; right: 0; height: 8px; opacity: .2;
    background: repeating-linear-gradient(90deg, rgba(0,0,0,.06) 0 8px, rgba(0,0,0,0) 8px 16px);
    border-bottom-left-radius: 1.25rem; border-bottom-right-radius: 1.25rem;
  }
  @keyframes bob { 0%,100% { transform: translateX(-50%) translateY(0); } 50% { transform: translateX(-50%) translateY(-2px); } }

  .day-card { backdrop-filter: blur(6px); transition: transform .25s ease; }
  .day-card:hover { transform: translateY(-2px); }
  .day-header { position: sticky; top: 0; z-index: 5; backdrop-filter: blur(10px); }

  @keyframes pulse-ring {
    0% { box-shadow: 0 0 0 0 rgba(16,185,129,.5); }
    70% { box-shadow: 0 0 0 6px rgba(16,185,129,0); }
    100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
  }
  .status-pulse { animation: pulse-ring 2s cubic-bezier(0.4,0,0.6,1) infinite; }
</style>
@endpush

@php
  use Carbon\Carbon;
  use App\Models\BasicListeningSchedule;

  $now  = isset($now) ? $now->copy() : Carbon::now('Asia/Jakarta');
  $dmap = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu',7=>'Minggu'];
  $days = ['Senin','Selasa','Rabu','Kamis','Jumat'];

  // Ambil data mingguan (tanpa relasi session)
  $weekData = $weekData
      ?? BasicListeningSchedule::with(['tutors:id,name','prody:id,name'])
            ->whereIn('hari', $days)
            ->orderBy('hari')->orderBy('jam_mulai')
            ->get();

  $byDay = $weekData->groupBy('hari');

  // Pastel palette
  $pastels = [
    ['bg'=>'bg-gradient-to-br from-amber-50 to-amber-100','border'=>'border-amber-200','text'=>'text-amber-900','chip'=>'border-amber-300 bg-amber-50'],
    ['bg'=>'bg-gradient-to-br from-rose-50 to-rose-100','border'=>'border-rose-200','text'=>'text-rose-900','chip'=>'border-rose-300 bg-rose-50'],
    ['bg'=>'bg-gradient-to-br from-emerald-50 to-emerald-100','border'=>'border-emerald-200','text'=>'text-emerald-900','chip'=>'border-emerald-300 bg-emerald-50'],
    ['bg'=>'bg-gradient-to-br from-sky-50 to-sky-100','border'=>'border-sky-200','text'=>'text-sky-900','chip'=>'border-sky-300 bg-sky-50'],
    ['bg'=>'bg-gradient-to-br from-violet-50 to-violet-100','border'=>'border-violet-200','text'=>'text-violet-900','chip'=>'border-violet-300 bg-violet-50'],
    ['bg'=>'bg-gradient-to-br from-pink-50 to-pink-100','border'=>'border-pink-200','text'=>'text-pink-900','chip'=>'border-pink-300 bg-pink-50'],
    ['bg'=>'bg-gradient-to-br from-lime-50 to-lime-100','border'=>'border-lime-200','text'=>'text-lime-900','chip'=>'border-lime-300 bg-lime-50'],
  ];

  $todayName = $dmap[$now->dayOfWeekIso];
  $nowTime   = $now->format('H:i:s');

  $isNow = fn($item) => $item->hari === $todayName && $item->jam_mulai <= $nowTime && $item->jam_selesai >= $nowTime;
@endphp

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-16">
    
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">
      <div>
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/90 backdrop-blur-sm rounded-full shadow-sm border border-emerald-200 mb-3">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          <span class="text-xs font-semibold text-emerald-700">Live Monitoring</span>
        </div>
        
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 mb-2">
          Kalender Basic Listening
        </h1>
        <p class="text-gray-600 text-base sm:text-lg">
          Jadwal Kelas Basic Listening • {{ $now->isoFormat('dddd, D MMMM Y') }}
        </p>
      </div>
      
      <div class="flex flex-col gap-3 bg-white/90 backdrop-blur-sm rounded-xl p-4 shadow-sm border border-gray-200">
        <div class="flex items-center gap-2.5">
          <span class="w-3 h-3 rounded-full bg-emerald-500 status-pulse"></span>
          <span class="text-sm font-medium text-gray-700">Sedang berlangsung</span>
        </div>
        <div class="flex items-center gap-2.5">
          <span class="w-3 h-3 rounded-full bg-orange-400"></span>
          <span class="text-sm font-medium text-gray-700">Mulai ≤ 30 menit lagi</span>
        </div>
      </div>
    </div>

    {{-- Calendar Grid - 2 Rows --}}
    <div class="space-y-6">
      {{-- Baris 1: Senin, Selasa, Rabu --}}
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach (array_slice($days, 0, 3) as $dayIndex => $dayName)
        @php $items = $byDay->get($dayName, collect()); @endphp

        <section class="day-card bg-white/70 border border-gray-200 rounded-2xl shadow-md overflow-hidden">
          <header class="day-header bg-white/80 px-5 py-3.5 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-bold text-gray-900">{{ $dayName }}</h2>
              @if($todayName === $dayName)
                <span class="flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-blue-500 text-white shadow-sm">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                  </svg>
                  Hari ini
                </span>
              @endif
            </div>
          </header>

          <div class="p-5 space-y-4 min-h-[320px]">
            @forelse ($items as $i => $item)
              @php
                $c = $pastels[($i + $dayIndex) % count($pastels)];
                $tutors = $item->tutors?->pluck('name')->join(', ') ?: '—';
                $active = $isNow($item);

                $startsIn = null;
                if ($item->hari === $todayName) {
                  $start = Carbon::createFromFormat('H:i:s', $item->jam_mulai, 'Asia/Jakarta');
                  $diff  = $start->diffInMinutes($now, false);
                  if ($diff < 0 && abs($diff) <= 30) $startsIn = abs($diff);
                }
              @endphp

              <article class="note {{ $c['bg'] }} {{ $c['border'] }} border p-4 relative group"
                       style="transform: rotate({{ ($i%2===0)?'-0.5deg':'0.4deg' }});">
                <span class="pin"></span>

                <div class="flex items-start gap-3">
                  <span class="mt-1 w-2.5 h-2.5 rounded-full flex-shrink-0
                    @if($active) bg-emerald-500 status-pulse @elseif($startsIn) bg-orange-400 @else bg-gray-300 @endif">
                  </span>

                  <div class="min-w-0 flex-1">
                    <h3 class="text-base font-bold leading-snug {{ $c['text'] }} mb-1.5">
                      {{ \Illuminate\Support\Str::limit($item->prody?->name ?? '—', 40) }}
                    </h3>

                    {{-- NAMA TUTOR (sudah fix) --}}
                    <p class="text-xs text-gray-700 mb-3">
                      <svg class="w-3 h-3 inline-block -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      {{ \Illuminate\Support\Str::limit($tutors, 70) }}
                    </p>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold shadow-sm
                      @if($active) {{ $c['chip'] }} text-emerald-700
                      @elseif($startsIn) {{ $c['chip'] }} text-orange-700
                      @else border-gray-300 bg-white/70 text-gray-700 @endif">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6l4 2M12 22a10 10 0 110-20 10 10 0 010 20z"/>
                      </svg>
                      {{ \Illuminate\Support\Str::of($item->jam_mulai)->substr(0,5) }}–{{ \Illuminate\Support\Str::of($item->jam_selesai)->substr(0,5) }}
                      @if($active)
                        <span class="ml-0.5 px-1.5 py-0.5 bg-emerald-500 text-white text-[10px] font-black uppercase rounded">LIVE</span>
                      @elseif($startsIn)
                        <span class="ml-0.5 px-1.5 py-0.5 bg-orange-500 text-white text-[10px] font-black uppercase rounded">{{ $startsIn }}m</span>
                      @endif
                    </span>
                  </div>
                </div>

                <span class="rip"></span>
              </article>
            @empty
              <div class="text-center py-10">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-gray-500 italic">Belum ada jadwal</p>
              </div>
            @endforelse
          </div>
        </section>
      @endforeach
      </div>

      {{-- Baris 2: Kamis, Jumat --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
      @foreach (array_slice($days, 3, 2) as $dayIndex => $dayName)
        @php 
          $items = $byDay->get($dayName, collect()); 
          $actualIndex = $dayIndex + 3;
        @endphp

        <section class="day-card bg-white/70 border border-gray-200 rounded-2xl shadow-md overflow-hidden">
          <header class="day-header bg-white/80 px-5 py-3.5 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-bold text-gray-900">{{ $dayName }}</h2>
              @if($todayName === $dayName)
                <span class="flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-blue-500 text-white shadow-sm">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                  </svg>
                  Hari ini
                </span>
              @endif
            </div>
          </header>

          <div class="p-5 space-y-4 min-h-[320px]">
            @forelse ($items as $i => $item)
              @php
                $c = $pastels[($i + $actualIndex) % count($pastels)];
                $tutors = $item->tutors?->pluck('name')->join(', ') ?: '—';
                $active = $isNow($item);

                $startsIn = null;
                if ($item->hari === $todayName) {
                  $start = Carbon::createFromFormat('H:i:s', $item->jam_mulai, 'Asia/Jakarta');
                  $diff  = $start->diffInMinutes($now, false);
                  if ($diff < 0 && abs($diff) <= 30) $startsIn = abs($diff);
                }
              @endphp

              <article class="note {{ $c['bg'] }} {{ $c['border'] }} border p-4 relative group"
                       style="transform: rotate({{ ($i%2===0)?'-0.5deg':'0.4deg' }});">
                <span class="pin"></span>

                <div class="flex items-start gap-3">
                  <span class="mt-1 w-2.5 h-2.5 rounded-full flex-shrink-0
                    @if($active) bg-emerald-500 status-pulse @elseif($startsIn) bg-orange-400 @else bg-gray-300 @endif">
                  </span>

                  <div class="min-w-0 flex-1">
                    <h3 class="text-base font-bold leading-snug {{ $c['text'] }} mb-1.5">
                      {{ \Illuminate\Support\Str::limit($item->prody?->name ?? '—', 40) }}
                    </h3>

                    {{-- NAMA TUTOR --}}
                    <p class="text-xs text-gray-700 mb-3">
                      <svg class="w-3 h-3 inline-block -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      {{ \Illuminate\Support\Str::limit($tutors, 70) }}
                    </p>

                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold shadow-sm
                      @if($active) {{ $c['chip'] }} text-emerald-700
                      @elseif($startsIn) {{ $c['chip'] }} text-orange-700
                      @else border-gray-300 bg-white/70 text-gray-700 @endif">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6l4 2M12 22a10 10 0 110-20 10 10 0 010 20z"/>
                      </svg>
                      {{ \Illuminate\Support\Str::of($item->jam_mulai)->substr(0,5) }}–{{ \Illuminate\Support\Str::of($item->jam_selesai)->substr(0,5) }}
                      @if($active)
                        <span class="ml-0.5 px-1.5 py-0.5 bg-emerald-500 text-white text-[10px] font-black uppercase rounded">LIVE</span>
                      @elseif($startsIn)
                        <span class="ml-0.5 px-1.5 py-0.5 bg-orange-500 text-white text-[10px] font-black uppercase rounded">{{ $startsIn }}m</span>
                      @endif
                    </span>
                  </div>
                </div>

                <span class="rip"></span>
              </article>
            @empty
              <div class="text-center py-10">
                <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-gray-500 italic">Belum ada jadwal</p>
              </div>
            @endforelse
          </div>
        </section>
      @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
