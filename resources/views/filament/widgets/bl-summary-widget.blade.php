<x-filament-widgets::widget>
  <x-filament::section>
    @php
      $avg          = $avgScoreWeek !== null ? number_format($avgScoreWeek, 1) : '—';
      $pendingClass = $pendingSubmit > 0 ? 'text-danger-600' : 'text-emerald-600';
      $pendingLabel = $pendingSubmit > 0 ? 'Butuh follow-up' : 'Tidak ada pending';
    @endphp

    <div class="flex items-center justify-between mb-4">
      <div>
        <p class="text-sm text-gray-500">Basic Listening</p>
        <h3 class="text-lg font-semibold text-gray-900">Ringkasan Mingguan</h3>
        <p class="text-xs text-gray-500 flex items-center gap-1">
          <x-filament::icon icon="heroicon-o-calendar-days" class="h-4 w-4" />
          {{ $startWeek->isoFormat('D MMM') }} — {{ $now->isoFormat('D MMM, HH:mm') }}
        </p>
      </div>
      <div class="inline-flex items-center gap-1 rounded-full border border-gray-200 px-3 py-1 text-xs font-semibold text-gray-700">
        <x-filament::icon icon="heroicon-o-chart-bar" class="h-4 w-4" />
        Monitoring
      </div>
    </div>

    <div class="grid grid-cols-1 gap-3">
      <x-filament::card class="border border-gray-200">
        <div class="flex items-center gap-3">
          <x-filament::icon icon="heroicon-o-clipboard-document-check" class="h-8 w-8 text-emerald-600" />
          <div class="flex-1">
            <p class="text-sm text-gray-600">Attempt minggu ini</p>
            <p class="text-3xl font-semibold text-gray-900">{{ $attemptsThisWeek }}</p>
            <p class="text-xs text-gray-500">Termasuk re-submit dan paksa submit.</p>
          </div>
        </div>
      </x-filament::card>

      <x-filament::card class="border border-gray-200">
        <div class="flex items-center gap-3">
          <x-filament::icon icon="heroicon-o-sparkles" class="h-8 w-8 text-primary-600" />
          <div class="flex-1">
            <p class="text-sm text-gray-600">Rata skor</p>
            <p class="text-3xl font-semibold text-gray-900">{{ $avg }}</p>
            <p class="text-xs text-gray-500">Hanya attempt yang sudah submit & punya skor.</p>
          </div>
        </div>
      </x-filament::card>

      <x-filament::card class="border border-gray-200">
        <div class="flex items-center gap-3">
          <x-filament::icon icon="heroicon-o-clock" class="h-8 w-8 {{ $pendingSubmit > 0 ? 'text-danger-600' : 'text-emerald-600' }}" />
          <div class="flex-1">
            <p class="text-sm text-gray-600">Belum submit</p>
            <p class="text-3xl font-semibold {{ $pendingClass }}">{{ $pendingSubmit }}</p>
            <p class="text-xs text-gray-500">{{ $pendingLabel }}</p>
          </div>
        </div>
      </x-filament::card>

      <x-filament::card class="border border-gray-200">
        <div class="flex items-center gap-3">
          <x-filament::icon icon="heroicon-o-pencil-square" class="h-8 w-8 text-indigo-600" />
          <div class="flex-1">
            <p class="text-sm text-gray-600">Nilai manual</p>
            <p class="text-3xl font-semibold text-gray-900">{{ $manualScores }}</p>
            <p class="text-xs text-gray-500">Override manual (S1–S5).</p>
          </div>
        </div>
      </x-filament::card>
    </div>

    <div class="mt-6">
      <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
          <x-filament::icon icon="heroicon-o-bolt" class="h-4 w-4 text-primary-600" />
          Attempt terbaru
        </h4>
        <a href="{{ route('filament.admin.resources.basic-listening-attempts.index') }}" class="text-xs font-semibold text-primary-700 hover:underline">
          Lihat semua
        </a>
      </div>
      <div class="grid gap-3 md:grid-cols-2">
        @forelse ($latestAttempts as $item)
          <div class="rounded-lg border border-gray-100 bg-white shadow-sm p-3 flex items-start justify-between">
            <div class="space-y-1">
              <p class="text-sm font-semibold text-gray-900">{{ $item->user?->name ?? '—' }}</p>
              <p class="text-xs text-gray-500">
                {{ $item->user?->prody?->name ?? 'Prodi -' }} · Skor: {{ $item->score !== null ? $item->score : '—' }}
              </p>
            </div>
            <p class="text-xs text-gray-500">{{ optional($item->submitted_at)?->diffForHumans() }}</p>
          </div>
        @empty
          <p class="text-sm text-gray-500">Belum ada attempt terbaru.</p>
        @endforelse
      </div>
    </div>
  </x-filament::section>
</x-filament-widgets::widget>
