<x-filament::page>
    <!-- Header Section -->
    <div class="mb-6 sm:mb-8">
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-xl p-4 sm:p-6 border border-gray-200 dark:border-gray-600 shadow-sm">
            <div class="flex items-center gap-3 sm:gap-4">
                <div class="flex-shrink-0">
                    @if($record->users->image)
                        <img src="{{ Storage::url($record->users->image) }}" 
                            alt="{{ $record->users->name }}" 
                            class="w-10 h-10 rounded-full object-cover border-2 border-blue-200 dark:border-blue-700">
                    @else
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900 dark:to-indigo-900 rounded-full flex items-center justify-center border-2 border-blue-200 dark:border-blue-700">
                            <x-heroicon-o-user class="w-5 h-5 text-blue-600 dark:text-blue-400"/>
                        </div>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white truncate">{{ $record->users->name }}</h1>
                    <p class="text-xs sm:text-base text-gray-600 dark:text-gray-300 font-medium truncate">{{ $record->users->srn }} - {{ $record->users->prody->name }} ({{ $record->users->year }})</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Groups Section -->
    <div class="space-y-4">
      <h2 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-1">
        <x-heroicon-o-information-circle class="w-5 h-5"/>
        <span>Informasi English Proficiency Test</span>
      </h2>

      @forelse ($record->pendaftaranGrupTes as $i => $grup)
        @php
          $nilai = $grup->dataNilaiTes;
          $hasScore = $nilai && $nilai->total_score;
        @endphp
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
          <!-- Group Header -->
          <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-700 dark:to-gray-600 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between">
                <div class="flex flex-col gap-1">
                <div>
                  <h3 class="text-base font-semibold text-gray-900 dark:text-white leading-tight">
                  Grup Tes {{ $grup->masterGrupTes->group_number }}
                  </h3>
                </div>
                <!-- Schedule Information -->
                <div>
                  <div class="flex items-center gap-1 mb-1">
                  <x-heroicon-o-calendar class="w-4 h-4 text-gray-400"/>
                  @if ($grup->masterGrupTes->tanggal_tes)
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                      {{ \Carbon\Carbon::parse($grup->masterGrupTes->tanggal_tes)->locale('id')->translatedFormat('l, d F Y H:i') }} WIB
                    </span>
                  @else
                    <span class="text-xs font-medium text-yellow-600 dark:text-yellow-400">Jadwal Sedang Menunggu</span>
                  @endif
                  </div>
                </div>
                </div>
                <div class="text-right">
                  @if($hasScore)
                    <x-filament::badge color="success" icon="heroicon-o-check">
                      Selesai
                    </x-filament::badge>
                  @else
                    <x-filament::badge color="danger" icon="heroicon-o-clock">
                      Pending
                    </x-filament::badge>
                  @endif
                </div>
            </div>
          </div>

          <!-- Group Content -->
          <div class="p-4">
            <!-- Test Scores -->
            @if ($hasScore)
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Score Details -->
                <div>
                  <h4 class="text-xs font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1">
                    <x-heroicon-o-chart-bar class="w-4 h-4 text-gray-500"/>
                    <span>Detail Nilai</span>
                  </h4>
                  <div class="space-y-2">
                    <div class="flex justify-between items-center py-1 px-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                      <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Listening Comprehension</span>
                      <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $nilai->listening_comprehension }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 px-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                      <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Structure & Written Expression</span>
                      <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $nilai->structure_written_expr }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1 px-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                      <span class="text-xs font-medium text-gray-600 dark:text-gray-300">Reading Comprehension</span>
                      <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $nilai->reading_comprehension }}</span>
                    </div>
                  </div>
                </div>

                <!-- Total Score & Status -->
                <div>
                  <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">{{ $nilai->total_score }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-300 mb-2">Total Score</div>
                    <x-filament::badge 
                      :color="strtolower($nilai->rank ?? '') === 'pass' ? 'success' : 'danger'"
                    >
                      @if(strtolower($nilai->rank ?? '') === 'pass')
                        Pass / Lulus
                      @elseif(strtolower($nilai->rank ?? '') === 'fail')
                        Fail / Gagal
                      @else
                        {{ ucfirst($nilai->rank ?? 'N/A') }}
                      @endif
                    </x-filament::badge>
                  </div>
                </div>
              </div>
            @else
              <!-- No Score Available -->
              <div class="flex flex-col items-center justify-center text-center py-2">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-2 shadow">
                  <x-heroicon-o-document-text class="w-5 h-5 text-blue-500 dark:text-blue-300"/>
                </div>
                <h4 class="text-base font-medium text-gray-900 dark:text-white mb-1">Nilai Belum Tersedia</h4>
                <p class="text-xs text-gray-400 text-center">
                  Nilai belum tersedia. Silakan cek kembali nanti.
                </p>
              </div>
            @endif
          </div>
        </div>
      @empty
        <div class="text-center py-8 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
          <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-2">
            <x-heroicon-o-clipboard-document class="w-6 h-6 text-gray-400"/>
          </div>
          <h3 class="text-base font-medium text-gray-900 dark:text-white mb-1">Tidak Ada Grup Tes</h3>
          <p class="text-xs text-gray-500 dark:text-gray-400">Peserta belum terdaftar dalam grup tes manapun.</p>
        </div>
      @endforelse
    </div>
</x-filament::page>