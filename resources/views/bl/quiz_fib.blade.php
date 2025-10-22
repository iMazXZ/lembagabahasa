@extends('layouts.front')
@section('title', 'Basic Listening - Fill in Blank')

@push('styles')
<style>
.quiz-wrap {
    max-width: 960px; 
    margin: 2rem auto; 
    background: #fff; 
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,.06); 
    padding: 2rem; 
    position: relative; 
    overflow: hidden;
}

/* Progress Bar & Timer Styles */
.progress-bar {
    height: 8px;
    background: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
    margin-bottom: 1.25rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #d946ef);
    width: 0%;
    transition: width 0.3s ease;
}

.timer {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: linear-gradient(135deg, #f59e0b, #f97316);
    color: #fff;
    padding: 0.5rem 1rem;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.9rem;
    z-index: 10;
}

.quiz-header {
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.quiz-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.quiz-meta {
    color: #6b7280;
    font-size: 0.875rem;
}

.paragraph-container {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    line-height: 1.7;
    font-size: 1.1rem;
    white-space: pre-line; /* Preserve line breaks */
    word-wrap: break-word;
}

/* 🆕 UKURAN INPUT LEBIH KECIL */
.fib-input {
    display: inline-block;
    width: 80px; /* 🆕 DIKECILKAN DARI 120px */
    border: 1px solid #d1d5db;
    border-radius: 4px; /* 🆕 DIKECILKAN */
    padding: 0.25rem 0.5rem; /* 🆕 DIKECILKAN */
    margin: 0 0.15rem; /* 🆕 DIKECILKAN */
    background: white;
    font-size: 0.9rem; /* 🆕 DIKECILKAN */
    text-align: center;
    transition: all 0.2s ease;
    vertical-align: middle;
}

.fib-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1); /* 🆕 DIKECILKAN */
    transform: translateY(-1px);
}

.fib-input.filled {
    background: #f0f9ff;
    border-color: #3b82f6;
}

.btn-submit {
    background: linear-gradient(135deg, #059669, #10b981);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.2s ease;
    width: 100%;
    margin-top: 1rem;
    font-size: 1rem;
}

.btn-submit:hover:not(:disabled) {
    filter: brightness(0.95);
    transform: translateY(-1px);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.words-counter {
    text-align: center;
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    padding: 0.5rem;
    background: #f9fafb;
    border-radius: 8px;
}

.instruction-box {
    background: #eff6ff;
    border: 1px solid #dbeafe;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.instruction-content {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
}

.instruction-icon {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    color: #2563eb;
    margin-top: 0.1rem;
}

.instruction-text {
    flex: 1;
}

.instruction-title {
    font-weight: 600;
    color: #1e40af;
    margin-bottom: 0.25rem;
    font-size: 0.9rem;
}

.instruction-desc {
    color: #374151;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Responsive Design */
@media (max-width: 768px) {
    .quiz-wrap {
        margin: 1rem;
        padding: 1.5rem;
        border-radius: 16px;
    }
    
    .timer {
        position: static;
        display: inline-block;
        margin-bottom: 1rem;
    }
    
    .fib-input {
        width: 70px; /* 🆕 LEBIH KECIL DI MOBILE */
        font-size: 0.85rem;
        padding: 0.2rem 0.4rem;
    }
    
    .paragraph-container {
        padding: 1rem;
        font-size: 1rem;
    }
}

/* Animation for low time warning */
@keyframes pulseWarning {
    0%, 100% { background: linear-gradient(135deg, #f59e0b, #f97316); }
    50% { background: linear-gradient(135deg, #dc2626, #ef4444); }
}

.timer.warning {
    animation: pulseWarning 1s ease-in-out infinite;
}
</style>
@endpush

@section('content')

@php
    use Illuminate\Support\Facades\Storage;
    
    // Default values untuk menghindari error
    $currentIndex = $currentIndex ?? 0;
    $totalQuestions = $totalQuestions ?? 1;
    $blankCount = $blankCount ?? 0;
    $isLastQuestion = $isLastQuestion ?? false;
@endphp

<div class="quiz-wrap">
    {{-- ⏳ Countdown Timer --}}
    @if(!empty($remainingSeconds) && $remainingSeconds > 0)
        <div class="timer" id="timer">--:--</div>
    @endif

    {{-- 🧭 Progress Bar --}}
    @if(!empty($remainingSeconds) && $remainingSeconds > 0)
    <div class="progress-bar">
        <div class="progress-fill" id="progressFill"></div>
    </div>
    @endif

    {{-- Quiz Header --}}
    <div class="quiz-header">
        <h1 class="quiz-title">Fill in the Blank</h1>
        <div class="quiz-meta">
            @if($attempt->session?->title)
                <strong>{{ $attempt->session->title }}</strong> • 
            @endif
            Soal {{ $currentIndex + 1 }} dari {{ $totalQuestions }}
        </div>
    </div>

    {{-- Audio Player --}}
    @if($question->audio_url)
    <div class="mb-6">
        <audio controls class="w-full" id="questionAudio">
            <source src="{{ Storage::url($question->audio_url) }}" type="audio/mpeg">
            Browser tidak mendukung audio.
        </audio>
        <div class="text-xs text-gray-500 mt-1">Dengarkan audio dan isi bagian yang kosong</div>
    </div>
    @endif

    {{-- Instructions --}}
    <div class="instruction-box">
        <div class="instruction-content">
            <div class="instruction-icon">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="instruction-text">
                <div class="instruction-title">Petunjuk Pengerjaan</div>
                <div class="instruction-desc">
                    Dengarkan audio dengan seksama, kemudian isi bagian yang kosong dengan kata yang tepat. 
                    Total ada <strong>{{ $blankCount }} bagian</strong> yang harus diisi.
                    @if(!empty($remainingSeconds) && $remainingSeconds > 0)
                    Waktu tersisa: <strong id="timeRemainingText"></strong>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Paragraph dengan input fields --}}
    <form method="POST" action="{{ route('bl.quiz.fib.answer', $attempt) }}" id="fibForm">
        @csrf
        <input type="hidden" name="question_id" value="{{ $question->id }}">

        <div class="paragraph-container">
            @if(!empty($processedParagraph) && str_contains($processedParagraph, '<input'))
                {!! $processedParagraph !!}
            @else
                {{-- FALLBACK EMERGENCY --}}
                <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                    <p><strong>⚠️ Emergency Fallback Active</strong></p>
                    <p>Please listen to the audio and fill in the blanks:</p>
                    <p>
                        The weather is <input type="text" class="fib-input" name="answers[0]" value="" placeholder="adjective"> today. 
                        I can hear <input type="text" class="fib-input" name="answers[1]" value="" placeholder="sound"> outside. 
                        The birds are <input type="text" class="fib-input" name="answers[2]" value="" placeholder="verb + ing">.
                    </p>
                </div>
            @endif
        </div>

        <div class="words-counter">
            <span id="filledCount">0</span>/{{ $blankCount }} kata terisi
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            Kumpulkan Jawaban
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
  // ===== Auto-Save to LocalStorage =====
function getStorageKey() {
    return 'fib_answers_{{ $attempt->id }}_{{ $question->id }}';
}

function saveAnswersToLocalStorage() {
    const inputs = document.querySelectorAll('.fib-input');
    const answers = {};
    
    inputs.forEach((input, index) => {
        answers[index] = input.value;
    });
    
    localStorage.setItem(getStorageKey(), JSON.stringify(answers));
    console.log('Answers auto-saved to localStorage');
}

function loadAnswersFromLocalStorage() {
    const saved = localStorage.getItem(getStorageKey());
    if (saved) {
        const answers = JSON.parse(saved);
        const inputs = document.querySelectorAll('.fib-input');
        
        inputs.forEach((input, index) => {
            if (answers[index] && answers[index].trim() !== '') {
                input.value = answers[index];
            }
        });
        
        console.log('Answers loaded from localStorage');
        updateFilledCount(); // Update counter setelah load
    }
}

function clearLocalStorage() {
    localStorage.removeItem(getStorageKey());
    console.log('LocalStorage cleared');
}

// ===== Word Counter Functionality =====
function updateFilledCount() {
    const inputs = document.querySelectorAll('.fib-input');
    let filledCount = 0;
    
    inputs.forEach(input => {
        if (input.value.trim() !== '') {
            filledCount++;
            input.classList.add('filled');
        } else {
            input.classList.remove('filled');
        }
    });
    
    document.getElementById('filledCount').textContent = filledCount;
    
    // Update progress text color
    const counterEl = document.getElementById('filledCount');
    if (filledCount === {{ $blankCount }}) {
        counterEl.style.color = '#059669';
        counterEl.style.fontWeight = 'bold';
    } else {
        counterEl.style.color = '#6b7280';
        counterEl.style.fontWeight = 'normal';
    }
}

// Initialize word counter and add event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Load saved answers dari localStorage
    loadAnswersFromLocalStorage();
    
    // Update counter saat input berubah + auto-save
    document.querySelectorAll('.fib-input').forEach(input => {
        input.addEventListener('input', function() {
            updateFilledCount();
            saveAnswersToLocalStorage(); // Auto-save on every keystroke
        });
        
        input.addEventListener('change', function() {
            updateFilledCount();
            saveAnswersToLocalStorage(); // Auto-save on blur/change
        });
    });
    
    // Auto-save setiap 10 detik juga (backup)
    setInterval(saveAnswersToLocalStorage, 10000);
    
    // Auto-focus first empty input
    const firstEmptyInput = document.querySelector('.fib-input:not([value])');
    if (firstEmptyInput) {
        setTimeout(() => firstEmptyInput.focus(), 500);
    }
});

// ===== Form Submission Handling =====
document.getElementById('fibForm')?.addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const filledCount = document.getElementById('filledCount').textContent;
    const totalBlanks = {{ $blankCount }};
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '📤 Mengumpulkan Jawaban...';
    }
    
    // Clear localStorage saat submit berhasil
    clearLocalStorage();
    
    // Optional: Warn if not all blanks are filled (but still allow submission)
    if (parseInt(filledCount) < totalBlanks) {
        const confirmSubmit = confirm(`Masih ada ${totalBlanks - parseInt(filledCount)} bagian yang kosong. Yakin ingin mengumpulkan?`);
        
        if (!confirmSubmit) {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '✅ Kumpulkan Jawaban';
            }
            return;
        }
    }
    
    // Cleanup timer before submission
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});

// ===== Before Unload Confirmation =====
window.addEventListener('beforeunload', function(e) {
    const filledCount = document.getElementById('filledCount').textContent;
    const totalBlanks = {{ $blankCount }};
    
    // Jika ada jawaban yang sudah diisi, tampilkan konfirmasi
    if (parseInt(filledCount) > 0) {
        // Untuk browser modern
        e.preventDefault();
        e.returnValue = 'Jawaban Anda telah disimpan sementara. Yakin ingin meninggalkan halaman?';
        return e.returnValue;
    }
});

// ===== Page Visibility API (Auto-save ketika tab tidak aktif) =====
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        // Tab tidak aktif, simpan jawaban
        saveAnswersToLocalStorage();
    }
});

// ===== Timer Functionality =====
const totalSeconds = {{ (int) ($remainingSeconds ?? 0) }};
let timerInterval;

if (totalSeconds > 0) {
    let secondsLeft = totalSeconds;
    const timerEl = document.getElementById('timer');
    const progressFill = document.getElementById('progressFill');
    const timeRemainingText = document.getElementById('timeRemainingText');
    
    const formatTime = (seconds) => {
        const minutes = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    };
    
    const updateTimerDisplay = () => {
        const timeString = formatTime(secondsLeft);
        
        if (timerEl) {
            timerEl.textContent = timeString;
            
            // Add warning style when less than 5 minutes
            if (secondsLeft <= 300) { // 5 minutes
                timerEl.classList.add('warning');
            }
        }
        
        if (timeRemainingText) {
            const minutes = Math.floor(secondsLeft / 60);
            const secs = secondsLeft % 60;
            timeRemainingText.textContent = `${minutes} menit ${secs} detik`;
        }
    };
    
    const updateProgressBar = () => {
        if (progressFill) {
            const progressPercent = (secondsLeft / totalSeconds) * 100;
            progressFill.style.width = progressPercent + '%';
            
            // Change color based on time left
            if (secondsLeft <= 60) { // 1 minute
                progressFill.style.background = 'linear-gradient(90deg, #ef4444, #dc2626)';
            } else if (secondsLeft <= 300) { // 5 minutes
                progressFill.style.background = 'linear-gradient(90deg, #f59e0b, #d97706)';
            }
        }
    };
    
    const tick = () => {
        updateTimerDisplay();
        updateProgressBar();
        
        // Auto submit when time's up
        if (secondsLeft <= 0) {
            clearInterval(timerInterval);
            handleTimeUp();
            return;
        }
        
        secondsLeft--;
    };
    
    const handleTimeUp = () => {
        // Show time up message
        if (timerEl) {
            timerEl.textContent = 'Waktu Habis!';
            timerEl.style.background = 'linear-gradient(135deg, #dc2626, #ef4444)';
        }
        
        // Disable all inputs
        document.querySelectorAll('.fib-input').forEach(input => {
            input.disabled = true;
            input.placeholder = 'Waktu habis';
        });
        
        // Auto submit form
        setTimeout(() => {
            document.getElementById('fibForm')?.submit();
        }, 1500);
    };
    
    // Start the timer
    updateTimerDisplay();
    updateProgressBar();
    timerInterval = setInterval(tick, 1000);
    
    // Cleanup on page leave
    window.addEventListener('beforeunload', () => {
        if (timerInterval) {
            clearInterval(timerInterval);
        }
    });
} else {
    // Hide timer elements if no time limit
    const timerEl = document.getElementById('timer');
    const progressBar = document.querySelector('.progress-bar');
    
    if (timerEl) timerEl.style.display = 'none';
    if (progressBar) progressBar.style.display = 'none';
}

// ===== Word Counter Functionality =====
function updateFilledCount() {
    const inputs = document.querySelectorAll('.fib-input');
    let filledCount = 0;
    
    inputs.forEach(input => {
        if (input.value.trim() !== '') {
            filledCount++;
            input.classList.add('filled');
        } else {
            input.classList.remove('filled');
        }
    });
    
    document.getElementById('filledCount').textContent = filledCount;
    
    // Update progress text color
    const counterEl = document.getElementById('filledCount');
    if (filledCount === {{ $blankCount }}) {
        counterEl.style.color = '#059669';
        counterEl.style.fontWeight = 'bold';
    } else {
        counterEl.style.color = '#6b7280';
        counterEl.style.fontWeight = 'normal';
    }
}

// Initialize word counter and add event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Update counter saat input berubah
    document.querySelectorAll('.fib-input').forEach(input => {
        input.addEventListener('input', updateFilledCount);
        input.addEventListener('change', updateFilledCount);
    });
    
    // Initialize counter
    updateFilledCount();
    
    // Auto-focus first empty input
    const firstEmptyInput = document.querySelector('.fib-input:not([value])');
    if (firstEmptyInput) {
        setTimeout(() => firstEmptyInput.focus(), 500);
    }
});

// ===== Form Submission Handling =====
document.getElementById('fibForm')?.addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const filledCount = document.getElementById('filledCount').textContent;
    const totalBlanks = {{ $blankCount }};
    
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '📤 Mengumpulkan Jawaban...';
    }
    
    // Optional: Warn if not all blanks are filled (but still allow submission)
    if (parseInt(filledCount) < totalBlanks) {
        const confirmSubmit = confirm(`Masih ada ${totalBlanks - parseInt(filledCount)} bagian yang kosong. Yakin ingin mengumpulkan?`);
        
        if (!confirmSubmit) {
            e.preventDefault();
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Kumpulkan Jawaban';
            }
            return;
        }
    }
    
    // Cleanup timer before submission
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});

// ===== Audio Control Enhancement =====
const audioPlayer = document.getElementById('questionAudio');
if (audioPlayer) {
    audioPlayer.addEventListener('play', function() {
        // Optional: Add visual feedback when audio plays
        console.log('Audio started playing');
    });
    
    audioPlayer.addEventListener('ended', function() {
        // Optional: Auto-focus first input when audio ends
        const firstInput = document.querySelector('.fib-input');
        if (firstInput) {
            firstInput.focus();
        }
    });
}

// ===== Keyboard Navigation =====
document.addEventListener('keydown', function(e) {
    // Enter key to move to next input or submit
    if (e.key === 'Enter' && e.target.classList.contains('fib-input')) {
        e.preventDefault();
        const inputs = Array.from(document.querySelectorAll('.fib-input'));
        const currentIndex = inputs.indexOf(e.target);
        
        if (currentIndex < inputs.length - 1) {
            inputs[currentIndex + 1].focus();
        } else {
            document.getElementById('submitBtn')?.click();
        }
    }
});
</script>
@endpush