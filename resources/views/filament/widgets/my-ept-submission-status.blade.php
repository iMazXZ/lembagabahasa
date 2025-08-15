<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <h2 class="text-lg font-semibold tracking-tight text-gray-950 dark:text-white">
                    Status Pengajuan Surat Rekomendasi
                </h2>

                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Berikut adalah status pengajuan terakhir yang Anda kirimkan.
                </p>
            </div>

            {{-- TOMBOL UNTUK MELIHAT RIWAYAT --}}
            <a href="{{ \App\Filament\Pages\SubmitEptScore::getUrl() }}"
               class="inline-flex items-center justify-center gap-x-1 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-gray-950 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:text-white dark:ring-gray-700 dark:hover:bg-gray-800">
                Lihat Semua Riwayat
            </a>
        </div>

        {{-- TAMPILAN STATUS --}}
        <div class="mt-6 border-t border-gray-200 pt-6 dark:border-white/10">
            @if ($submission)
                @php
                    $status = $submission->status;
                    $color = match ($status) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    };
                    $icon = match ($status) {
                        'approved' => 'heroicon-o-check-circle',
                        'rejected' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    };
                    $text = match ($status) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => 'Menunggu Persetujuan',
                    };
                @endphp
                <div class="flex items-center gap-2">
                    <x-filament::badge :color="$color" :icon="$icon">
                        {{ $text }}
                    </x-filament::badge>
                </div>
                @if($status == 'rejected' && $submission->catatan_admin)
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    <strong>Catatan Staf:</strong> {{ $submission->catatan_admin }}
                </p>
                @endif
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Anda belum pernah melakukan pengajuan Surat Rekomendasi.
                </p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>