<x-filament::widget class="h-full">
    <x-filament::card class="h-full flex flex-col">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-1 mb-4">
            <x-heroicon-o-information-circle class="w-5 h-5 text-gray-500 dark:text-gray-400"/>
            <span>Status Surat Rekomendasi EPT</span>
        </h2>

        <div class="flex-1 mt-2">
            @if ($submission)
                {{-- Ada pengajuan --}}
                @php
                    $status = $submission->status; // pending | approved | rejected
                    $badgeColor = match($status) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'warning',
                    };
                    $badgeIcon = match($status) {
                        'approved' => 'heroicon-s-check-circle',
                        'rejected' => 'heroicon-s-x-circle',
                        default    => 'heroicon-s-clock',
                    };
                    $statusText = match($status) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default    => 'Menunggu',
                    };
                @endphp

                <div class="space-y-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Diajukan: {{ $submission->created_at->translatedFormat('d F Y, H:i') }}
                    </p>

                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Status:</span>
                        <x-filament::badge :color="$badgeColor" :icon="$badgeIcon">
                            {{ $statusText }}
                        </x-filament::badge>
                    </div>

                    @if ($status === 'rejected' && $submission->catatan_admin)
                        <p class="text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-medium">Catatan Staf:</span> {{ $submission->catatan_admin }}
                        </p>
                    @endif
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @if ($status === 'approved')
                        {{ $this->getLihatDetailAction() }}
                    @elseif ($status === 'rejected')
                        {{ $this->getAjukanUlangAction() }}
                    @endif

                    {{ $this->getRiwayatAction() }}
                </div>
            @else
                {{-- Belum pernah mengajukan --}}
                <div class="text-center flex flex-col justify-center items-center h-full">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 mt-2">
                        <x-heroicon-o-document-text class="h-8 w-8 text-gray-400" />
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                        Anda belum pernah mengajukan Surat Rekomendasi.
                    </p>
                    {{ $this->getAjukanAction() }}
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>
