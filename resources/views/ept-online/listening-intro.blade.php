@extends('layouts.front')
@section('title', 'Persiapan Listening')
@section('hide_navbar', '1')
@section('hide_footer', '1')
@section('translate_no', '1')

@include('ept-online.partials.exam-guard')

@php
    $partList = $availableListeningParts ?? [];
@endphp

@push('styles')
<style>
    body { padding-top: 0 !important; background: #f8fafc; }
    .prep-shell {
        min-height: 100vh;
        background:
            radial-gradient(circle at top right, rgba(14,165,233,0.12), transparent 30%),
            radial-gradient(circle at left, rgba(16,185,129,0.08), transparent 25%),
            #f8fafc;
    }
    .prep-topbar {
        position: fixed; inset: 0 0 auto 0; z-index: 50; height: 76px;
        backdrop-filter: blur(14px);
        background: rgba(255,255,255,0.9);
        border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(15,23,42,.06);
    }
    .prep-content { width: 100%; padding: 120px 24px 48px; }
    .prep-stage { width: 100%; max-width: 980px; margin: 0 auto; }
    .prep-panel {
        width: 100%;
        border-radius: 32px;
        border: 1px solid #e2e8f0;
        background: rgba(255,255,255,0.94);
        box-shadow: 0 18px 40px rgba(15,23,42,.08);
        overflow: hidden;
    }
    .prep-hero {
        padding: 32px;
        border-bottom: 1px solid #e2e8f0;
        background:
            linear-gradient(135deg, rgba(255,255,255,0.98), rgba(240,249,255,0.92));
    }
    .prep-grid {
        display: grid;
        gap: 16px;
        padding: 28px 32px 32px;
    }
    .prep-tile {
        border-radius: 22px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        padding: 18px 20px;
    }
    .prep-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: .45rem .8rem;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .16em;
        text-transform: uppercase;
        color: #475569;
    }
    .prep-cta {
        padding: 0 32px 32px;
    }
    @media (min-width: 768px) {
        .prep-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (min-width: 1280px) {
        .prep-content { padding-left: 32px; padding-right: 32px; }
    }
</style>
@endpush

@section('content')
<div class="prep-shell" data-exam-guard>
    <div class="prep-topbar">
        <div class="flex h-full items-center justify-between gap-4 px-4 lg:px-8">
            <div class="min-w-0">
                <div class="text-[11px] font-black uppercase tracking-[0.22em] text-slate-400">EPT Online</div>
                <div class="truncate text-sm font-bold text-slate-900 sm:text-base">{{ $attempt->form?->title ?? 'Paket Tes Online' }}</div>
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Listening Preparation</div>
            </div>
            <div class="rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-600">
                {{ $section->duration_minutes }} Menit
            </div>
        </div>
    </div>

    <div class="prep-content">
        <section class="prep-stage">
            <div class="prep-panel">
                <div class="prep-hero">
                    <div class="text-center">
                        <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Listening Section</div>
                        <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-[2rem]">
                            {{ $section->title }}
                        </h1>
                        <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-base">
                            Audio akan diputar dari awal. Kerjakan soal sesuai arahan yang Anda dengar.
                        </p>
                    </div>
                </div>

                <div class="prep-grid">
                    <div class="prep-tile">
                        <div class="text-sm font-bold text-slate-900">Section</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">{{ $section->title }}</div>
                    </div>

                    <div class="prep-tile">
                        <div class="text-sm font-bold text-slate-900">Durasi</div>
                        <div class="mt-2 text-sm leading-6 text-slate-600">{{ $section->duration_minutes }} menit</div>
                    </div>

                    @if ($partList !== [])
                        <div class="prep-tile">
                            <div class="text-sm font-bold text-slate-900">Part</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($partList as $part)
                                    <span class="prep-chip">Part {{ $part }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="prep-cta">
                    <form method="POST" action="{{ route('ept-online.attempt.start-section', ['attempt' => $attempt->public_id]) }}" data-allow-unload="1">
                        @csrf
                        <button type="submit" data-allow-unload="1" class="mx-auto inline-flex w-full max-w-sm items-center justify-center rounded-2xl bg-slate-900 px-5 py-4 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Mulai Listening
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
