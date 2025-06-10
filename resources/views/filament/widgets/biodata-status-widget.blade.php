<x-filament::widget class="h-full">
    {{-- Tambahkan kelas h-full dan flex untuk membuat kartu mengisi tinggi dan menjadi container flex --}}
    <x-filament::card class="h-full flex flex-col">
        {{-- Container utama yang akan mendorong tombol ke bawah --}}
        <div class="flex-1 flex flex-col justify-between">

            @if ($isBiodataComplete)
                {{-- TAMPILAN JIKA BIODATA SUDAH LENGKAP --}}
                <div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Status Biodata</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Biodata Sudah Lengkap</p>
                        </div>
                        <div>
                            {{ $this->getUbahAction() }}
                        </div>
                    </div>
                </div>

            @else
                {{-- TAMPILAN JIKA BIODATA BELUM LENGKAP --}}
                <div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Status Biodata</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Silakan Lengkapi Biodata</p>
                        </div>
                        <div>
                            {{ $this->getLengkapiAction() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::widget>