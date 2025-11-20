{{-- resources/views/dashboard/translation/create.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Ajukan Penerjemahan')
@section('page-title', 'Formulir Pengajuan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Ajukan Penerjemahan Baru</h1>
            <p class="mt-1 text-sm text-slate-500">
                Isi formulir di bawah ini untuk mengajukan penerjemahan abstrak.
            </p>
        </div>
        <a href="{{ route('dashboard.translation') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-700 text-xs font-bold hover:bg-slate-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Batal</span>
        </a>
    </div>

    {{-- Error Alerts --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <div class="flex items-center gap-2 mb-2 text-red-800 font-bold text-sm">
                <i class="fa-solid fa-circle-exclamation"></i>
                Mohon perbaiki kesalahan berikut:
            </div>
            <ul class="list-disc list-inside text-xs text-red-700 space-y-1 ml-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('dashboard.translation.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        @csrf

        {{-- Section: Info Pemohon --}}
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-400">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">{{ $user->name }}</h3>
                    <p class="text-xs text-slate-500">
                        {{ $user->srn ?? 'NPM Kosong' }} • {{ $user->prody->name ?? 'Prodi Kosong' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="p-6 sm:p-8 space-y-8">

            {{-- Upload Pembayaran --}}
            <div>
                <label class="block text-sm font-bold text-slate-800 mb-2">
                    Bukti Pembayaran <span class="text-red-500">*</span>
                </label>

                <div class="relative group">
                    <div id="payment-dropzone"
                        class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center
                                hover:border-um-blue hover:bg-blue-50/50 transition-colors
                                flex flex-col items-center justify-center gap-2 cursor-pointer">
                        
                        <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mb-1
                                    text-slate-400 group-hover:text-um-blue group-hover:bg-blue-100 transition-colors">
                            <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                        </div>

                        <p class="text-sm text-slate-600 font-medium">
                            Klik untuk upload atau drag file ke sini
                        </p>
                        <p class="text-xs text-slate-400">
                            JPG, PNG (Maks. 8MB)
                        </p>

                        {{-- Preview kecil (akan diisi via JS) --}}
                        <div id="payment-preview-wrapper" class="mt-3 hidden">
                            <div class="flex items-center gap-3 justify-center">
                                <img id="payment-preview"
                                    src=""
                                    alt="Preview bukti pembayaran"
                                    class="h-12 w-12 rounded-lg object-cover border border-slate-200 shadow-sm">
                                <div class="text-left">
                                    <p id="payment-filename" class="text-xs font-semibold text-slate-700 truncate max-w-[180px]"></p>
                                    <p class="text-[11px] text-slate-400">File siap diunggah</p>
                                </div>
                            </div>
                        </div>

                        {{-- Input file menutupi area dropzone --}}
                        <input
                            id="bukti_pembayaran_input"
                            type="file"
                            name="bukti_pembayaran"
                            accept="image/*"
                            required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        >
                    </div>
                </div>

                @error('bukti_pembayaran')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Input Abstrak --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-bold text-slate-800">
                        Teks Abstrak <span class="text-red-500">*</span>
                    </label>
                    <span class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded border border-slate-200">
                        <span id="word-count" class="font-bold text-um-blue">0</span> Kata
                    </span>
                </div>

                <div class="relative space-y-2">
                    {{-- Hidden input yang akan dikirim ke server --}}
                    <input
                        id="source_text"
                        type="hidden"
                        name="source_text"
                        value="{{ old('source_text') }}"
                    >

                    {{-- Trix editor (rich text) --}}
                    <trix-editor
                        input="source_text"
                        class="trix-content block w-full rounded-xl border border-slate-300 text-sm leading-relaxed bg-white focus:border-um-blue focus:ring-um-blue"
                        placeholder="Salin dan tempel teks abstrak Anda di sini..."
                    ></trix-editor>

                    {{-- Icon corner (opsional, biar tetap ada seperti sebelumnya) --}}
                    <div class="absolute bottom-3 right-3 text-slate-300 pointer-events-none">
                        <i class="fa-solid fa-pen-nib"></i>
                    </div>
                </div>

                @error('source_text')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Info Tambahan (Readonly) --}}
            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Tanggal</span>
                    <div class="text-sm font-medium text-slate-800 bg-slate-50 px-3 py-2 rounded-lg border border-slate-200">
                        {{ now()->translatedFormat('d F Y') }}
                    </div>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1">Status Awal</span>
                    <div class="text-sm font-medium text-slate-800 bg-slate-50 px-3 py-2 rounded-lg border border-slate-200">
                        Menunggu
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-um-blue hover:bg-um-dark-blue text-white font-bold text-sm shadow-lg shadow-blue-900/20 transition-all hover:scale-[1.02]">
                <i class="fa-solid fa-paper-plane"></i>
                Kirim Permohonan
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // === Preview Bukti Pembayaran ===
        const dropzone   = document.getElementById('payment-dropzone');
        const input      = document.getElementById('bukti_pembayaran_input');
        const previewBox = document.getElementById('payment-preview-wrapper');
        const previewImg = document.getElementById('payment-preview');
        const fileNameEl = document.getElementById('payment-filename');

        if (dropzone && input && previewBox && previewImg && fileNameEl) {

            function handleFile(file) {
                if (!file) return;

                // Tampilkan nama file
                fileNameEl.textContent = file.name;

                // Kalau bukan image, jangan paksa preview
                if (!file.type.startsWith('image/')) {
                    previewImg.src = '';
                    previewBox.classList.remove('hidden');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewBox.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }

            input.addEventListener('change', function (e) {
                const file = e.target.files && e.target.files[0];
                handleFile(file);
            });

            // Highlight saat drag & drop (opsional, biar kerasa interaktif)
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-um-blue', 'bg-blue-50/50');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-um-blue', 'bg-blue-50/50');
                });
            });

            // Saat file dijatuhkan ke area
            dropzone.addEventListener('drop', function (e) {
                const dt = e.dataTransfer;
                if (!dt || !dt.files || !dt.files[0]) return;

                input.files = dt.files; // set ke input asli
                handleFile(dt.files[0]);
            });
        }
    });
</script>

@endsection