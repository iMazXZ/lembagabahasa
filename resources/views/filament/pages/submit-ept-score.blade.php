<x-filament-panels::page>
    {{-- Bagian tabel riwayat (tidak berubah) --}}
    <div class="mt-12">
        <h2 class="text-2xl text-center font-bold tracking-tight mb-4">Riwayat Pengajuan Surat Rekomendasi</h2>
        <div class="mt-4">
            {{ $this->table }}
        </div>
    </div>
    {{-- Gunakan komponen form resmi dari Filament --}}
    <x-filament-panels::form wire:submit="submit">
        {{-- Ini untuk merender semua field input --}}
        <h2 class="text-2xl text-center font-bold tracking-tight">Form Pengajuan Surat Rekomendasi</h2>
        {{ $this->form }}

        {{-- Ini komponen resmi untuk merender tombol/actions --}}
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page>