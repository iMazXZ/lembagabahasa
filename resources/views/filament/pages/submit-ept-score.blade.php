<x-filament-panels::page>
    @if ($this->hasSubmissions)
        <x-filament::section>
            <x-slot name="heading">
            <div class="text-center">Riwayat Pengajuan Surat Rekomendasi</div>
        </x-slot>
            {{ $this->table }}
        </x-filament::section>
    @endif

    <x-filament::section>
        <x-slot name="heading">
            <div class="text-center">Form Pengajuan Surat Rekomendasi</div>
        </x-slot>
        {{ $this->form }}
        {{-- Ini komponen resmi untuk merender tombol/actions --}}
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament::section>
</x-filament-panels::page>
