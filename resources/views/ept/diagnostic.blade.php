{{-- resources/views/ept/diagnostic.blade.php --}}
@extends('layouts.front')
@section('title', 'Alat Diagnosa EPT')

@push('styles')
<style>
    .check-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        margin-bottom: 0.75rem;
    }
    .check-item.success { background: #f0fdf4; border-color: #bbf7d0; }
    .check-item.error { background: #fef2f2; border-color: #fecaca; }
    .check-item.warning { background: #fffbeb; border-color: #fde68a; }
    .status-icon { font-size: 1.25rem; }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('ept.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-slate-800 mb-4">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard EPT
        </a>
        <h1 class="text-2xl font-bold text-slate-900">Alat Diagnosa Sistem</h1>
        <p class="text-slate-600 mt-1">Pastikan perangkat Anda siap untuk mengikuti ujian EPT.</p>
    </div>

    {{-- Info Banner --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-8">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-info text-blue-600"></i>
            </div>
            <div>
                <h4 class="font-bold text-blue-800 mb-1">Sebelum Ujian</h4>
                <p class="text-sm text-blue-700">
                    Jalankan diagnosa ini beberapa hari sebelum ujian untuk memastikan perangkat Anda kompatibel dengan sistem EPT.
                </p>
            </div>
        </div>
    </div>

    {{-- Diagnostic Checks --}}
    <div class="bg-white rounded-xl border border-slate-200 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6">Hasil Pemeriksaan</h3>
        
        <div id="diagnosticResults">
            {{-- Browser --}}
            <div class="check-item" id="checkBrowser">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-globe text-slate-400"></i>
                    <div>
                        <div class="font-semibold text-slate-800">Browser</div>
                        <div class="text-sm text-slate-500" id="browserInfo">Memeriksa...</div>
                    </div>
                </div>
                <span class="status-icon" id="browserStatus"><i class="fa-solid fa-spinner fa-spin text-slate-400"></i></span>
            </div>

            {{-- OS --}}
            <div class="check-item" id="checkOS">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-desktop text-slate-400"></i>
                    <div>
                        <div class="font-semibold text-slate-800">Sistem Operasi</div>
                        <div class="text-sm text-slate-500" id="osInfo">Memeriksa...</div>
                    </div>
                </div>
                <span class="status-icon" id="osStatus"><i class="fa-solid fa-spinner fa-spin text-slate-400"></i></span>
            </div>

            {{-- Screen --}}
            <div class="check-item" id="checkScreen">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-display text-slate-400"></i>
                    <div>
                        <div class="font-semibold text-slate-800">Resolusi Layar</div>
                        <div class="text-sm text-slate-500" id="screenInfo">Memeriksa...</div>
                    </div>
                </div>
                <span class="status-icon" id="screenStatus"><i class="fa-solid fa-spinner fa-spin text-slate-400"></i></span>
            </div>

            {{-- Camera --}}
            <div class="check-item" id="checkCamera">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-camera text-slate-400"></i>
                    <div>
                        <div class="font-semibold text-slate-800">Kamera (Webcam)</div>
                        <div class="text-sm text-slate-500" id="cameraInfo">Memeriksa...</div>
                    </div>
                </div>
                <span class="status-icon" id="cameraStatus"><i class="fa-solid fa-spinner fa-spin text-slate-400"></i></span>
            </div>

            {{-- Audio --}}
            <div class="check-item" id="checkAudio">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-headphones text-slate-400"></i>
                    <div>
                        <div class="font-semibold text-slate-800">Audio Output</div>
                        <div class="text-sm text-slate-500" id="audioInfo">Memeriksa...</div>
                    </div>
                </div>
                <span class="status-icon" id="audioStatus"><i class="fa-solid fa-spinner fa-spin text-slate-400"></i></span>
            </div>

            {{-- Connection --}}
            <div class="check-item" id="checkConnection">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-wifi text-slate-400"></i>
                    <div>
                        <div class="font-semibold text-slate-800">Koneksi Internet</div>
                        <div class="text-sm text-slate-500" id="connectionInfo">Memeriksa...</div>
                    </div>
                </div>
                <span class="status-icon" id="connectionStatus"><i class="fa-solid fa-spinner fa-spin text-slate-400"></i></span>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-100">
            <button onclick="runDiagnostics()" class="w-full py-3 bg-emerald-600 text-white rounded-xl font-bold hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-rotate mr-2"></i> Jalankan Ulang Diagnosa
            </button>
        </div>
    </div>

    {{-- SEB Info --}}
    <div class="mt-8 bg-slate-50 border border-slate-200 rounded-xl p-6">
        <h4 class="font-bold text-slate-800 mb-3">Tentang Safe Exam Browser (SEB)</h4>
        <p class="text-sm text-slate-600 mb-4">
            Ujian EPT menggunakan Safe Exam Browser untuk menjaga integritas ujian. Pastikan Anda sudah menginstal SEB sebelum hari ujian.
        </p>
        <div class="flex flex-wrap gap-3">
            <a href="https://safeexambrowser.org/download_en.html" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-download"></i> Download SEB
            </a>
            <a href="https://safeexambrowser.org/about_overview_en.html" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-blue-600 hover:text-blue-800">
                <i class="fa-solid fa-circle-question"></i> Pelajari Lebih Lanjut
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function setStatus(id, status, info) {
    const el = document.getElementById(id);
    const infoEl = document.getElementById(id.replace('check', '').toLowerCase() + 'Info');
    const statusEl = document.getElementById(id.replace('check', '').toLowerCase() + 'Status');
    
    el.classList.remove('success', 'error', 'warning');
    el.classList.add(status);
    
    if (infoEl) infoEl.textContent = info;
    
    if (statusEl) {
        if (status === 'success') {
            statusEl.innerHTML = '<i class="fa-solid fa-circle-check text-green-500"></i>';
        } else if (status === 'error') {
            statusEl.innerHTML = '<i class="fa-solid fa-circle-xmark text-red-500"></i>';
        } else {
            statusEl.innerHTML = '<i class="fa-solid fa-triangle-exclamation text-amber-500"></i>';
        }
    }
}

async function runDiagnostics() {
    // Browser
    const ua = navigator.userAgent;
    let browserName = 'Unknown';
    if (ua.includes('Chrome') && !ua.includes('Edg')) browserName = 'Chrome';
    else if (ua.includes('Firefox')) browserName = 'Firefox';
    else if (ua.includes('Safari') && !ua.includes('Chrome')) browserName = 'Safari';
    else if (ua.includes('Edg')) browserName = 'Edge';
    setStatus('checkBrowser', 'success', browserName);

    // OS
    let osName = 'Unknown';
    if (ua.includes('Windows NT 10')) osName = 'Windows 10/11';
    else if (ua.includes('Windows')) osName = 'Windows (versi lama)';
    else if (ua.includes('Mac OS')) osName = 'macOS';
    else if (ua.includes('Linux')) osName = 'Linux';
    else if (ua.includes('Android')) osName = 'Android';
    else if (ua.includes('iOS')) osName = 'iOS';
    
    const isWindows = osName.includes('Windows 10');
    setStatus('checkOS', isWindows ? 'success' : 'warning', osName + (isWindows ? '' : ' (Direkomendasikan: Windows 10/11)'));

    // Screen
    const w = window.screen.width;
    const h = window.screen.height;
    const isGoodScreen = w >= 1280 && h >= 720;
    setStatus('checkScreen', isGoodScreen ? 'success' : 'warning', `${w} x ${h}` + (isGoodScreen ? '' : ' (Min: 1280x720)'));

    // Camera
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const cameras = devices.filter(d => d.kind === 'videoinput');
        if (cameras.length > 0) {
            setStatus('checkCamera', 'success', `${cameras.length} kamera terdeteksi`);
        } else {
            setStatus('checkCamera', 'error', 'Tidak ada kamera terdeteksi');
        }
    } catch (e) {
        setStatus('checkCamera', 'error', 'Tidak dapat mengakses kamera');
    }

    // Audio
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const audio = devices.filter(d => d.kind === 'audiooutput');
        if (audio.length > 0) {
            setStatus('checkAudio', 'success', `${audio.length} audio output terdeteksi`);
        } else {
            setStatus('checkAudio', 'warning', 'Audio output tidak terdeteksi');
        }
    } catch (e) {
        setStatus('checkAudio', 'warning', 'Tidak dapat memeriksa audio');
    }

    // Connection
    if (navigator.onLine) {
        const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (conn && conn.effectiveType) {
            const type = conn.effectiveType;
            const isGood = type === '4g';
            setStatus('checkConnection', isGood ? 'success' : 'warning', `Online (${type.toUpperCase()})`);
        } else {
            setStatus('checkConnection', 'success', 'Online');
        }
    } else {
        setStatus('checkConnection', 'error', 'Offline');
    }
}

// Auto-run on page load
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(runDiagnostics, 500);
});
</script>
@endpush
