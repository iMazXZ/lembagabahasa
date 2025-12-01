<x-filament-widgets::widget>
  <x-filament::section>
    <div class="flex items-center justify-between mb-4">
      <div>
        <p class="text-sm text-gray-500">Dashboard Admin</p>
        <h3 class="text-lg font-semibold text-gray-900">Antrean Prioritas</h3>
      </div>
      <div class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
        <x-filament::icon icon="heroicon-o-shield-check" class="h-4 w-4" />
        Admin
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Penerjemahan</p>
            <p class="text-2xl font-semibold text-primary-700">{{ $terjemahan['pending_count'] }} menunggu</p>
            <p class="text-xs text-gray-500">Disetujui: {{ $terjemahan['approved_count'] }} · Diproses: {{ $terjemahan['process_count'] }}</p>
          </div>
          <div class="rounded-full bg-primary-100 px-3 py-1 text-xs font-semibold text-primary-800">Layanan</div>
        </div>

        <div class="mt-4 space-y-3">
          @forelse ($terjemahan['latest'] as $item)
            <div class="flex items-start justify-between rounded-lg border border-gray-100 bg-gray-50/70 p-3">
              <div class="space-y-1">
                <p class="text-sm font-semibold text-gray-900">
                  {{ $item->users?->name ?? '—' }}
                </p>
                <p class="text-xs text-gray-500">Status: {{ $item->status }}</p>
              </div>
              <p class="text-xs text-gray-500">{{ optional($item->created_at)?->diffForHumans() }}</p>
            </div>
          @empty
            <p class="text-sm text-gray-500">Tidak ada antrean.</p>
          @endforelse
        </div>
      </div>

      <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Surat Rekomendasi</p>
            <p class="text-2xl font-semibold text-danger-600">{{ $surat['pending_count'] }} menunggu</p>
            <p class="text-xs text-gray-500">Prioritas review</p>
          </div>
          <div class="rounded-full bg-danger-50 px-3 py-1 text-xs font-semibold text-danger-700">Urgent</div>
        </div>

        <div class="mt-4 space-y-3">
          @forelse ($surat['latest'] as $item)
            <div class="flex items-start justify-between rounded-lg border border-gray-100 bg-gray-50/70 p-3">
              <div class="space-y-1">
                <p class="text-sm font-semibold text-gray-900">
                  {{ $item->user?->name ?? '—' }}
                </p>
                <p class="text-xs text-gray-500">Status: {{ ucfirst($item->status) }}</p>
              </div>
              <p class="text-xs text-gray-500">{{ optional($item->created_at)?->diffForHumans() }}</p>
            </div>
          @empty
            <p class="text-sm text-gray-500">Tidak ada antrean.</p>
          @endforelse
        </div>
      </div>
    </div>
  </x-filament::section>
</x-filament-widgets::widget>
