<x-slot:title>Verifikasi Email</x-slot:title>

<div class="text-center space-y-4">
    <h2 class="text-2xl font-bold">Verifikasi Email</h2>
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Kami telah mengirim tautan verifikasi ke email kamu.
        <br>
        Jika belum menerima, klik tombol di bawah untuk kirim ulang.
    </p>

    <x-filament::button wire:click="resendVerification">
        Kirim Ulang Tautan
    </x-filament::button>
</div>
