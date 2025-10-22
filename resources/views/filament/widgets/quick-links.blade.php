<x-filament-widgets::widget>
  <x-filament::section>
    <div class="flex flex-wrap gap-3">
      <x-filament::button tag="a" href="{{ url('/#berita') }}" size="lg" color="warning"
          class="w-full sm:w-auto" icon="heroicon-o-arrow-down-circle">
        Cek Jadwal & Nilai EPT
      </x-filament::button>

      <x-filament::button tag="a" href="{{ route('bl.index') }}" size="lg" color="warning"
          class="w-full sm:w-auto" icon="heroicon-o-musical-note">
        Basic Listening
      </x-filament::button>

      <x-filament::button tag="a" href="{{ route('verification.index') }}" size="lg" color="warning"
          class="w-full sm:w-auto" icon="heroicon-o-check-badge">
        Verifikasi Dokumen
      </x-filament::button>
    </div>
  </x-filament::section>
</x-filament-widgets::widget>
