{{-- resources/views/filament/pages/bl-survey-results.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter form --}}
        <x-filament::section heading="Filter & Kategori">
            {{ $this->form }}
        </x-filament::section>

        {{-- Stats / header widgets (opsional) --}}
        @php($widgets = $this->getHeaderWidgets())
        @if (! empty($widgets))
            <x-filament-widgets::widgets
                :widgets="$widgets"
                class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3"
            />
        @endif

        {{-- Table --}}
        <x-filament::section heading="Ringkasan Hasil Kuesioner">
            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament-panels::page>
