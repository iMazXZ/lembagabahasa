{{-- resources/views/ept/launcher.blade.php --}}
@extends('layouts.front')
@section('title', 'CBT Launcher - EPT')

@push('styles')
<style>
    .webcam-container {
        position: relative;
        width: 100%;
        max-width: 400px;
        aspect-ratio: 4/3;
        background: #1e293b;
        border-radius: 1rem;
        overflow: hidden;
    }
    .webcam-container video,
    .webcam-container canvas {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .webcam-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.6);
        color: white;
        text-align: center;
        padding: 1rem;
    }
    .capture-btn {
        position: absolute;
        bottom: 1rem;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: white;
        border: 4px solid #10b981;
        cursor: pointer;
        transition: all 0.2s;
    }
    .capture-btn:hover {
        transform: translateX(-50%) scale(1.1);
    }
    .capture-btn:active {
        transform: translateX(-50%) scale(0.95);
    }
    .step-indicator {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-bottom: 2rem;
    }
    .step-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #cbd5e1;
        transition: all 0.3s;
    }
    .step-dot.active {
        background: #10b981;
        transform: scale(1.2);
    }
    .step-dot.done {
        background: #10b981;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 to-slate-800 py-10 px-4">
    <div class="max-w-2xl mx-auto">
        
        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-500/20 text-emerald-400 text-sm font-medium mb-4">
                <i class="fa-solid fa-shield-halved"></i>
                Safe Exam Browser
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">CBT Launcher</h1>
            <p class="text-slate-400">Verifikasi identitas sebelum memulai ujian</p>
        </div>
        
        {{-- Step Indicator --}}
        <div class="step-indicator">
            <div class="step-dot active" id="dot1"></div>
            <div class="step-dot" id="dot2"></div>
            <div class="step-dot" id="dot3"></div>
        </div>
        
        {{-- Session Info Card --}}
        <div class="bg-slate-800 rounded-xl border border-slate-700 p-5 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center">
                    <i class="fa-solid fa-calendar-check text-emerald-400 text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-white">{{ $session->name }}</h3>
                    <p class="text-sm text-slate-400">
                        {{ $session->date->translatedFormat('l, d F Y') }} • 
                        {{ $session->start_time }} - {{ $session->end_time }}
                    </p>
                </div>
            </div>
        </div>
        
        {{-- Step 1: Webcam --}}
        <div id="step1" class="bg-slate-800 rounded-2xl border border-slate-700 p-6 mb-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center text-sm">1</span>
                Swafoto Verifikasi
            </h3>
            
            <div class="flex flex-col items-center">
                <div class="webcam-container mb-4" id="webcamContainer">
                    <video id="webcam" autoplay playsinline muted></video>
                    <canvas id="canvas" style="display: none;"></canvas>
                    
                    <div class="webcam-overlay" id="webcamOverlay">
                        <i class="fa-solid fa-camera text-4xl mb-3"></i>
                        <p class="text-sm">Klik tombol di bawah untuk mengaktifkan kamera</p>
                    </div>
                    
                    <button type="button" class="capture-btn" id="captureBtn" style="display: none;" title="Ambil Foto">
                        <i class="fa-solid fa-camera text-emerald-600"></i>
                    </button>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" id="startCameraBtn" class="px-4 py-2 bg-emerald-600 text-white rounded-lg font-semibold hover:bg-emerald-700">
                        <i class="fa-solid fa-video mr-2"></i> Aktifkan Kamera
                    </button>
                    <button type="button" id="retakeBtn" class="px-4 py-2 bg-slate-600 text-white rounded-lg font-semibold hover:bg-slate-500" style="display: none;">
                        <i class="fa-solid fa-rotate mr-2"></i> Ulangi
                    </button>
                </div>
                
                <p class="text-xs text-slate-500 mt-3 text-center">
                    Pastikan wajah Anda terlihat jelas dan pencahayaan cukup.
                </p>
            </div>
        </div>
        
        {{-- Step 2: Passcode --}}
        <div id="step2" class="bg-slate-800 rounded-2xl border border-slate-700 p-6 mb-6 opacity-50 pointer-events-none">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                <span class="w-7 h-7 bg-slate-600 rounded-full flex items-center justify-center text-sm" id="step2Badge">2</span>
                Passcode Pengawas
            </h3>
            
            <div>
                <label class="block text-sm text-slate-400 mb-2">Masukkan kode dari pengawas:</label>
                <input type="text" id="passcodeInput" 
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-xl text-white text-center text-xl tracking-widest font-mono uppercase focus:border-emerald-500 focus:ring focus:ring-emerald-500/20"
                    placeholder="••••••••" maxlength="20" autocomplete="off">
                <p class="text-xs text-slate-500 mt-2">
                    Kode ini diberikan oleh pengawas saat ujian dimulai.
                </p>
            </div>
        </div>
        
        {{-- Step 3: Start --}}
        <div id="step3" class="opacity-50 pointer-events-none">
            <button type="button" id="startExamBtn" 
                class="w-full py-4 bg-emerald-600 text-white rounded-xl text-lg font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-900/30 flex items-center justify-center gap-3"
                disabled>
                <i class="fa-solid fa-play"></i>
                Mulai Ujian
            </button>
        </div>
        
        {{-- Error Message --}}
        <div id="errorMessage" class="hidden mt-4 bg-red-500/20 border border-red-500/50 rounded-xl p-4 text-red-400 text-sm text-center">
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('canvas');
    const webcamOverlay = document.getElementById('webcamOverlay');
    const captureBtn = document.getElementById('captureBtn');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const retakeBtn = document.getElementById('retakeBtn');
    const passcodeInput = document.getElementById('passcodeInput');
    const startExamBtn = document.getElementById('startExamBtn');
    const errorMessage = document.getElementById('errorMessage');
    
    let stream = null;
    let selfieData = null;
    
    // Step 1: Start Camera
    startCameraBtn.addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user', width: 640, height: 480 } 
            });
            video.srcObject = stream;
            webcamOverlay.style.display = 'none';
            captureBtn.style.display = 'block';
            startCameraBtn.style.display = 'none';
        } catch (err) {
            showError('Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.');
        }
    });
    
    // Capture Photo
    captureBtn.addEventListener('click', function() {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        selfieData = canvas.toDataURL('image/jpeg', 0.8);
        
        // Show captured image
        video.style.display = 'none';
        canvas.style.display = 'block';
        captureBtn.style.display = 'none';
        retakeBtn.style.display = 'inline-flex';
        
        // Stop camera
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
        
        // Enable step 2
        enableStep2();
    });
    
    // Retake Photo
    retakeBtn.addEventListener('click', async function() {
        selfieData = null;
        video.style.display = 'block';
        canvas.style.display = 'none';
        retakeBtn.style.display = 'none';
        
        // Restart camera
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user', width: 640, height: 480 } 
            });
            video.srcObject = stream;
            captureBtn.style.display = 'block';
        } catch (err) {
            showError('Tidak dapat mengakses kamera.');
        }
        
        // Disable step 2 & 3
        disableStep2();
    });
    
    // Passcode Input
    passcodeInput.addEventListener('input', function() {
        if (this.value.length >= 4 && selfieData) {
            enableStep3();
        } else {
            disableStep3();
        }
    });
    
    // Start Exam
    startExamBtn.addEventListener('click', async function() {
        if (!selfieData || !passcodeInput.value) {
            showError('Lengkapi swafoto dan passcode terlebih dahulu.');
            return;
        }
        
        startExamBtn.disabled = true;
        startExamBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses...';
        hideError();
        
        try {
            const response = await fetch('{{ route("ept.launcher.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    passcode: passcodeInput.value,
                    selfie: selfieData,
                }),
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                showError(data.message || 'Terjadi kesalahan.');
                startExamBtn.disabled = false;
                startExamBtn.innerHTML = '<i class="fa-solid fa-play"></i> Mulai Ujian';
            }
        } catch (err) {
            showError('Gagal menghubungi server. Coba lagi.');
            startExamBtn.disabled = false;
            startExamBtn.innerHTML = '<i class="fa-solid fa-play"></i> Mulai Ujian';
        }
    });
    
    function enableStep2() {
        document.getElementById('step2').classList.remove('opacity-50', 'pointer-events-none');
        document.getElementById('step2Badge').classList.remove('bg-slate-600');
        document.getElementById('step2Badge').classList.add('bg-emerald-500');
        document.getElementById('dot1').classList.add('done');
        document.getElementById('dot2').classList.add('active');
        passcodeInput.focus();
    }
    
    function disableStep2() {
        document.getElementById('step2').classList.add('opacity-50', 'pointer-events-none');
        document.getElementById('step2Badge').classList.add('bg-slate-600');
        document.getElementById('step2Badge').classList.remove('bg-emerald-500');
        document.getElementById('dot2').classList.remove('active');
        disableStep3();
    }
    
    function enableStep3() {
        document.getElementById('step3').classList.remove('opacity-50', 'pointer-events-none');
        document.getElementById('dot2').classList.add('done');
        document.getElementById('dot3').classList.add('active');
        startExamBtn.disabled = false;
    }
    
    function disableStep3() {
        document.getElementById('step3').classList.add('opacity-50', 'pointer-events-none');
        document.getElementById('dot3').classList.remove('active');
        startExamBtn.disabled = true;
    }
    
    function showError(msg) {
        errorMessage.textContent = msg;
        errorMessage.classList.remove('hidden');
    }
    
    function hideError() {
        errorMessage.classList.add('hidden');
    }
});
</script>
@endpush
