@extends('layouts.front')
@section('title', 'EPT Online Selesai')

@php
    $scoreVisibleAfterSubmit = (bool) ($attempt->form?->show_score_after_submit && filled($result?->total_scaled));
    $scorePublished = (bool) ($result?->is_published && filled($result?->total_scaled));
    $canShowScore = $scoreVisibleAfterSubmit || $scorePublished;
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-10 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <div class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-8 py-8">
                <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">EPT Online</div>
                <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 class="text-3xl font-black tracking-tight text-slate-950">Tes selesai</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Jawaban sudah dikunci dan submit berhasil dicatat pada sistem.
                        </p>
                    </div>

                    <div class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-bold text-emerald-700">
                        Submitted
                    </div>
                </div>
            </div>

            <div class="grid gap-6 px-8 py-8 lg:grid-cols-[minmax(0,1.25fr)_320px]">
                <div class="space-y-6">
                    <div class="rounded-[28px] border border-slate-200 bg-slate-50 p-6">
                        @if ($canShowScore)
                            <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Nilai Akhir</div>
                            <div class="mt-3 flex items-end gap-3">
                                <div class="text-5xl font-black tracking-tight text-slate-950">{{ $result->total_scaled }}</div>
                                <div class="pb-1 text-sm font-medium text-slate-500">skor akhir</div>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">Listening</div>
                                    <div class="mt-2 text-xl font-bold text-slate-900">{{ $result->listening_scaled ?? $result->listening_raw ?? '-' }}</div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">Structure</div>
                                    <div class="mt-2 text-xl font-bold text-slate-900">{{ $result->structure_scaled ?? $result->structure_raw ?? '-' }}</div>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3">
                                    <div class="text-xs font-black uppercase tracking-[0.14em] text-slate-400">Reading</div>
                                    <div class="mt-2 text-xl font-bold text-slate-900">{{ $result->reading_scaled ?? $result->reading_raw ?? '-' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Status Nilai</div>
                            <div class="mt-3 text-2xl font-black tracking-tight text-slate-950">Menunggu publikasi nilai</div>
                            <p class="mt-3 text-sm leading-7 text-slate-600">
                                Nilai akhir belum dipublikasikan oleh admin atau pengawas. Halaman peserta tidak menampilkan kunci jawaban setelah submit.
                            </p>
                        @endif
                    </div>

                    <div class="rounded-[28px] border border-slate-200 bg-white p-6">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Paket Tes</div>
                                <div class="mt-2 text-lg font-bold text-slate-900">{{ $attempt->form?->title ?? 'Paket Tes Online' }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $attempt->form?->code ?? '-' }}</div>
                            </div>

                            <div>
                                <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Waktu Submit</div>
                                <div class="mt-2 text-lg font-bold text-slate-900">{{ optional($attempt->submitted_at)->translatedFormat('d M Y, H:i') ?? '-' }} WIB</div>
                                <div class="mt-1 text-sm text-slate-500">{{ \App\Models\EptOnlineAttempt::statusOptions()[$attempt->status] ?? $attempt->status }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="rounded-[28px] border border-slate-200 bg-white p-6">
                        <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Catatan</div>
                        <div class="mt-3 text-sm leading-7 text-slate-600">
                            Jawaban benar dan pembahasan tidak ditampilkan pada halaman peserta.
                            @unless($scoreVisibleAfterSubmit)
                                Skor hanya tampil jika sudah dipublikasikan atau paket tes mengaktifkan nilai langsung setelah submit.
                            @endunless
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200 bg-white p-6">
                        <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Aksi</div>
                        <div class="mt-4 flex flex-col gap-3">
                            <a href="{{ route('ept-online.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                                Kembali ke EPT Online
                            </a>
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
