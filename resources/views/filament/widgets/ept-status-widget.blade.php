<x-filament::widget class="h-full">
    <x-filament::card class="h-full flex flex-col">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-1 mb-4">
            <x-heroicon-o-information-circle class="w-5 h-5 text-gray-500 dark:text-gray-400"/>
            <span>Status Pendaftaran EPT</span>
        </h2>

        <div class="flex-1 mt-4">
            @if ($latestEpt)
                {{-- TAMPILAN JIKA USER SUDAH PERNAH MENDAFTAR --}}
                <div class="space-y-2">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Daftar Pada: {{ $latestEpt->created_at->translatedFormat('d F Y') }}
                    </p>
                    <div class="flex items-center space-x-2 gap-2">
                        <span class="text-lg font-medium text-gray-700 dark:text-gray-200">Status:</span>
                        
                        @php
                            $status = $latestEpt->status_pembayaran;
                            
                            // Logika untuk Warna, Teks, dan Ikon berdasarkan referensi Anda
                            $badgeColor = match($status) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            };

                            $statusText = match($status) {
                                'pending' => 'Menunggu',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                default => 'Tidak Diketahui',
                            };

                            $badgeIcon = match($status) {
                                'pending' => 'heroicon-s-clock',
                                'approved' => 'heroicon-s-check-circle',
                                'rejected' => 'heroicon-s-x-circle',
                                default => 'heroicon-s-question-mark-circle',
                            };
                        @endphp

                        <x-filament::badge :color="$badgeColor" :icon="$badgeIcon">
                            {{ $statusText }}
                        </x-filament::badge>
                    </div>
                </div>

               <div class="mt-2 flex items-center gap-2">
                    @if ($latestEpt->status_pembayaran === 'approved')
                        {{ $this->getLihatJadwalAction() }}
                    @endif

                    {{ $this->getRiwayatAction() }}
                </div>

            @else
                {{-- TAMPILAN JIKA USER BELUM PERNAH MENDAFTAR --}}
                <div class="text-center flex flex-col justify-center items-center h-full">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-4 mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Anda belum pernah mendaftar EPT.</p>
                    
                    {{-- Me-render tombol dari class PHP --}}
                    {{ $this->getDaftarAction() }}
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>