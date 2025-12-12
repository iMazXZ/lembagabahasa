<x-filament-panels::page>
    {{-- WhatsApp API Status --}}
    <x-filament::section icon="heroicon-o-signal">
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                @if($waStatus && $waStatus['status'] === 'connected')
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-success-500"></span>
                    </span>
                @else
                    <span class="flex h-2.5 w-2.5 rounded-full bg-danger-500"></span>
                @endif
                Status WhatsApp API
            </div>
        </x-slot>
        
        <x-slot name="description">
            wa-api.lembagabahasa.site
        </x-slot>
        
        <x-slot name="headerEnd">
            <x-filament::button 
                wire:click="refreshWaStatus" 
                size="sm" 
                color="gray"
                icon="heroicon-o-arrow-path"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="refreshWaStatus">Refresh</span>
                <span wire:loading wire:target="refreshWaStatus">Loading...</span>
            </x-filament::button>
        </x-slot>

        @if($waStatus)
            <div class="flex flex-wrap items-center gap-4 text-sm">
                @if($waStatus['status'] === 'connected')
                    <x-filament::badge color="success" icon="heroicon-o-check-circle">
                        Terhubung
                    </x-filament::badge>
                @elseif($waStatus['status'] === 'disconnected')
                    <x-filament::badge color="danger" icon="heroicon-o-x-circle">
                        Terputus
                    </x-filament::badge>
                @else
                    <x-filament::badge color="warning" icon="heroicon-o-exclamation-triangle">
                        {{ ucfirst($waStatus['status'] ?? 'Unknown') }}
                    </x-filament::badge>
                @endif
                
                @if(!empty($waStatus['uptimeFormatted']))
                    <span class="text-gray-500 dark:text-gray-400">
                        Uptime: <strong>{{ $waStatus['uptimeFormatted'] }}</strong>
                    </span>
                @endif
                
                @if(!empty($waStatus['qrPending']) && $waStatus['qrPending'])
                    <x-filament::badge color="warning" icon="heroicon-o-qr-code">
                        Menunggu scan QR
                    </x-filament::badge>
                @endif
                
                @if(!empty($waStatus['message']))
                    <span class="text-gray-500 dark:text-gray-400">
                        {{ $waStatus['message'] }}
                    </span>
                @endif
            </div>
        @else
            <div class="flex items-center gap-2 text-gray-500">
                <x-heroicon-o-exclamation-circle class="w-5 h-5" />
                <span>Tidak dapat memuat status</span>
            </div>
        @endif
    </x-filament::section>

    {{-- WhatsApp Message Logs --}}
    @if(!empty($waLogs))
    <x-filament::section collapsible collapsed icon="heroicon-o-document-text">
        <x-slot name="heading">
            Log Pesan Terakhir ({{ count($waLogs) }})
        </x-slot>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($waLogs as $log)
                <div class="py-2 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        @if($log['status'] === 'success')
                            <x-filament::badge color="success" size="sm">✓</x-filament::badge>
                        @else
                            <x-filament::badge color="danger" size="sm">✗</x-filament::badge>
                        @endif
                        
                        <span class="text-sm font-medium">{{ $log['phone'] }}</span>
                        <x-filament::badge size="sm" color="gray">{{ $log['type'] }}</x-filament::badge>
                    </div>
                    
                    <span class="text-xs text-gray-400">
                        {{ \Carbon\Carbon::parse($log['timestamp'])->diffForHumans() }}
                    </span>
                </div>
            @endforeach
        </div>
    </x-filament::section>
    @endif

    {{-- Settings Form --}}
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit" color="primary" icon="heroicon-o-check">
                Simpan Pengaturan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
