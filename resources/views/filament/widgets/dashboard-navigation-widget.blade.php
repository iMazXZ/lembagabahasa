<x-filament::widget>
    <x-filament::card>
        <div class="flex flex-col gap-4">
            <p>Lengkapi Data Diri Sebelum Melakukan Permohonan</p>
            <div class="flex gap-4">
                @foreach($this->getHeaderActions() as $action)
                    {{ $action->render() }}
                @endforeach
            </div>
        </div>
    </x-filament::card>
</x-filament::widget>