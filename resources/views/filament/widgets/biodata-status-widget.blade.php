<x-filament-widgets::widget>
    <x-filament::section>
        <div class="h-full">
            @if ($isBiodataComplete)
                {{-- === JIKA BIODATA SUDAH LENGKAP === --}}
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Status Biodata
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Biodata Sudah Lengkap
                        </p>
                    </div>

                    {{-- Tombol pakai komponen Filament Button (aman, tanpa actions package) --}}
                    <x-filament::button
                        tag="a"
                        href="{{ route('filament.admin.pages.biodata') }}"
                        color="gray"
                        icon="heroicon-o-pencil-square"
                        size="sm"
                    >
                        Ubah
                    </x-filament::button>
                </div>
            @else
                {{-- === JIKA BELUM LENGKAP === --}}
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            Status Biodata
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Silakan Lengkapi Biodata
                        </p>
                    </div>

                    {{-- Tombol pakai Filament Button --}}
                    <x-filament::button
                        tag="a"
                        href="{{ route('filament.admin.pages.biodata') }}"
                        color="warning"
                        icon="heroicon-o-pencil-square"
                        size="sm"
                    >
                        Lengkapi
                    </x-filament::button>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
