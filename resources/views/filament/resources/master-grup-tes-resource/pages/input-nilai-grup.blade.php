<x-filament::page>
    <div class="space-y-4">
        <h2 class="text-xl font-bold">Input Nilai untuk Grup Tes {{ $record->group_number }}</h2>
        <p class="text-sm text-gray-500">Tanggal Tes: {{ \Carbon\Carbon::parse($record->tanggal_tes)->translatedFormat('d F Y H:i') }}</p>

        {{ $this->form }}

        <x-filament::button wire:click="save" color="success">
            Simpan Semua Nilai
        </x-filament::button>
    </div>
</x-filament::page>
