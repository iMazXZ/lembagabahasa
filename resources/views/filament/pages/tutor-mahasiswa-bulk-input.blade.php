{{-- resources/views/filament/pages/tutor-mahasiswa-bulk-input.blade.php --}}
<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white/60 p-4 shadow-sm">
            <div>
                <div class="text-sm text-gray-500">Pilih mahasiswa lalu isi nilai sekaligus.</div>
                <div class="text-sm text-gray-500">Attempt terbaru tampil sebagai placeholder.</div>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <span class="rounded-full bg-primary-50 px-3 py-1 text-primary-700">
                    Mahasiswa terpilih: <strong>{{ $this->selectedCount }}</strong>
                </span>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex justify-end gap-3">
                @if ($this->showDownloadButton && $this->downloadUrl)
                    <x-filament::button tag="a" href="{{ $this->downloadUrl }}" target="_blank" icon="heroicon-o-arrow-down-tray" color="gray">
                        Download Excel
                    </x-filament::button>
                @endif
                <x-filament::button type="submit" icon="heroicon-o-check-circle">
                    Simpan Nilai
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
