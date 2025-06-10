<x-filament::page>
    
</x-filament::page>

@push('scripts')
<script>
    // Dengarkan event 'open-pdf-new-tab' yang kita kirim dari action
    window.addEventListener('open-pdf-new-tab', event => {
        // Buka URL yang dikirim dari server di tab baru
        window.open(event.detail.url, '_blank');
    });
</script>
@endpush