<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-4">
            {{-- HEADER --}}
            <div class="text-sm text-gray-700 leading-relaxed">
                Selamat, <strong class="text-gray-900">{{ $user->name }}.</strong>
                Kamu telah menyelesaikan kelas Basic Listening
            </div>

            {{-- GRID STATS --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <x-filament::card class="h-full flex flex-col justify-center py-3">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Daily Score Total (Meeting 1 - 5)</div>
                    <div class="text-xl font-semibold text-gray-900">
                        {{ is_numeric($daily) ? number_format($daily,2) : '—' }}
                    </div>
                </x-filament::card>

                <x-filament::card class="h-full flex flex-col justify-center py-3">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Final Test</div>
                    <div class="text-xl font-semibold text-gray-900">
                        {{ is_numeric($finalTest) ? number_format($finalTest,0) : '—' }}
                    </div>
                </x-filament::card>

                <x-filament::card class="h-full flex flex-col justify-center py-3">
                    <div class="text-xs text-gray-500 uppercase tracking-wide">Total Score</div>
                    <div class="text-xl font-semibold text-gray-900">
                        {{ is_numeric($finalNumeric) ? number_format($finalNumeric,2) : '—' }} or {{ $finalLetter }}
                    </div>
                </x-filament::card>
            </div>

            {{-- ACTION --}}
            <div class="pt-2 border-t border-gray-100">
                @if($canDownload)
                    <x-filament::button
                        tag="a"
                        href="{{ route('bl.certificate.download') }}"
                        color="success"
                        size="sm"
                        icon="heroicon-o-arrow-down-tray"
                    >
                        Download PDF Sertifikat
                    </x-filament::button>

                    <p class="text-xs text-gray-500 mt-2">
                        Sertifikat dapat diunduh.
                    </p>
                @else
                    <x-filament::button
                        color="gray"
                        size="sm"
                        icon="heroicon-o-lock-closed"
                        disabled
                    >
                        Download PDF Sertifikat Tidak Tersedia
                    </x-filament::button>

                    <p class="text-xs text-gray-500 mt-2">
                        Hubungi tutor jika nilai Attendance atau Final Test belum diinput.
                    </p>
                @endif
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
