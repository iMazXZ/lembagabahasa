<x-filament::widget class="h-full">
    <x-filament::card class="h-full flex flex-col">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-1 mb-2">
            <x-heroicon-o-document-duplicate class="w-5 h-5 text-gray-500 dark:text-gray-400"/>
            <span>Status Penerjemahan</span>
        </h2>

        <div class="flex-1 mt-4">
            @if ($latestPenerjemahan)
                {{-- TAMPILAN JIKA USER SUDAH PERNAH MENGAJUKAN --}}
                <div class="space-y-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Pengajuan: {{ $latestPenerjemahan->created_at->translatedFormat('d F Y') }}
                    </p>
                    <div class="flex items-center space-x-2 gap-2">
                        <span class="text-lg font-medium text-gray-700 dark:text-gray-200">Status:</span>
                        
                        @php
                            $status = $latestPenerjemahan->status;
                            
                            // Logika untuk warna badge
                            $badgeColor = match(true) {
                                $status === 'Selesai' => 'success',
                                str_contains($status, 'Ditolak') => 'danger',
                                $status === 'Diproses' => 'info',
                                default => 'warning',
                            };

                            // Logika untuk ikon badge (meniru dari resource Anda)
                            $badgeIcon = match(true) {
                                $status === 'Selesai' => 'heroicon-s-check-circle',
                                str_contains($status, 'Ditolak') => 'heroicon-s-x-circle',
                                $status === 'Diproses' => 'heroicon-s-cog-6-tooth',
                                default => 'heroicon-s-clock',
                            };
                        @endphp

                        {{-- Tambahkan :icon="$badgeIcon" --}}
                        <x-filament::badge :color="$badgeColor" :icon="$badgeIcon">
                            {{ $status }}
                        </x-filament::badge>
                    </div>
                </div>

                <div class="mt-2">
                    {{ $this->getRiwayatAction() }}
                </div>

            @else
                {{-- TAMPILAN JIKA USER BELUM PERNAH MENGAJUKAN --}}
                <div class="text-center flex flex-col justify-center items-center h-full">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>
                    </div>
                    <p class="text-base text-gray-500 dark:text-gray-400 mb-4">Anda belum pernah mengajukan penerjemahan.</p>
                    
                    {{ $this->getAjukanAction() }}
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>