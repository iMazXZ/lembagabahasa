{{-- Filament v3.3: gunakan komponen panels, dan render $this->table --}}
<x-filament-panels::page>
    <div class="mb-4 text-sm text-gray-600">
        Daftar mahasiswa di prodi yang Anda ampu. Gunakan filter
        <strong>Prefix Angkatan (SRN)</strong> untuk melihat angkatan tertentu (default: 25).
    </div>

    {{ $this->table }}
</x-filament-panels::page>
