{{-- resources/views/ept/quiz.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EPT - {{ $quiz->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f172a; overflow: hidden; }
        .question-nav-btn {
            @apply w-10 h-10 rounded-lg text-sm font-bold flex items-center justify-center transition-all;
        }
        .question-nav-btn.current { @apply bg-blue-600 text-white ring-2 ring-blue-400; }
        .question-nav-btn.answered { @apply bg-emerald-600 text-white; }
        .question-nav-btn.unanswered { @apply bg-slate-700 text-slate-300 hover:bg-slate-600; }
        .option-btn {
            @apply w-full p-4 rounded-xl border-2 text-left transition-all flex items-center gap-4;
        }
        .option-btn.selected { @apply border-emerald-500 bg-emerald-500/20 text-white; }
        .option-btn:not(.selected) { @apply border-slate-600 bg-slate-800 text-slate-300 hover:border-slate-500; }
        .audio-player { filter: invert(1) hue-rotate(180deg); width: 100%; }
    </style>
</head>
<body class="text-white">
    <div class="h-screen flex flex-col">
        {{-- Header --}}
        <header class="bg-slate-800 border-b border-slate-700 px-4 py-3 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-4">
                <div class="text-lg font-bold">
                    {{ $quiz->name }}
                </div>
                <div class="flex gap-2">
                    @foreach(['listening', 'structure', 'reading'] as $sec)
                        <span class="px-3 py-1 rounded-lg text-xs font-bold 
                            {{ $section === $sec ? 'bg-emerald-600' : 'bg-slate-700 text-slate-400' }}">
                            {{ ucfirst($sec) }}
                        </span>
                    @endforeach
                </div>
            </div>
            
            {{-- Timer --}}
            <div class="flex items-center gap-4">
                <div id="timer" class="flex items-center gap-2 px-4 py-2 bg-red-600/20 border border-red-500/50 rounded-xl">
                    <i class="fa-solid fa-clock text-red-400"></i>
                    <span id="timerDisplay" class="font-mono font-bold text-red-400 text-lg">--:--</span>
                </div>
                <div class="text-sm text-slate-400">
                    <span class="font-bold text-white">{{ $questionIndex + 1 }}</span> / {{ $questions->count() }}
                </div>
            </div>
        </header>
        
        {{-- Main Content --}}
        <div class="flex-1 flex overflow-hidden">
            {{-- Question Navigation Sidebar --}}
            <aside class="w-64 bg-slate-800 border-r border-slate-700 p-4 overflow-y-auto shrink-0">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">Nomor Soal</h3>
                <div class="grid grid-cols-5 gap-2">
                    @foreach($questions as $i => $q)
                        <a href="{{ route('ept.quiz.show', ['attempt' => $attempt, 'q' => $i]) }}"
                           class="question-nav-btn {{ $i === $questionIndex ? 'current' : ($answers->has($q->id) ? 'answered' : 'unanswered') }}">
                            {{ $i + 1 }}
                        </a>
                    @endforeach
                </div>
                
                <div class="mt-6 pt-4 border-t border-slate-700">
                    <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                        <span class="w-4 h-4 rounded bg-emerald-600"></span> Terjawab
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                        <span class="w-4 h-4 rounded bg-slate-700"></span> Belum Dijawab
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="w-4 h-4 rounded bg-blue-600 ring-2 ring-blue-400"></span> Saat Ini
                    </div>
                </div>
            </aside>
            
            {{-- Question Content --}}
            <main class="flex-1 overflow-y-auto p-8">
                <div class="max-w-4xl mx-auto">
                    {{-- Audio untuk Listening Section (1 audio untuk seluruh section) --}}
                    @if($section === 'listening' && $quiz->listening_audio_url)
                        <div class="mb-6 p-4 bg-slate-800 rounded-xl border border-slate-700 sticky top-0 z-10">
                            <p class="text-sm text-slate-400 mb-3">
                                <i class="fa-solid fa-headphones mr-2"></i> Audio Listening Section
                            </p>
                            <audio controls class="audio-player" id="audioPlayer">
                                <source src="{{ Storage::url($quiz->listening_audio_url) }}" type="audio/mpeg">
                            </audio>
                            <p class="text-xs text-slate-500 mt-2">
                                <i class="fa-solid fa-info-circle mr-1"></i> 
                                Dengarkan audio sambil menjawab soal. Audio tidak dapat di-rewind.
                            </p>
                        </div>
                    @endif
                    
                    {{-- Passage untuk Reading --}}
                    @if($section === 'reading' && $currentQuestion->passage)
                        <div class="mb-6 p-6 bg-slate-800 rounded-xl border border-slate-700 max-h-64 overflow-y-auto">
                            <p class="text-sm text-slate-400 mb-3">
                                <i class="fa-solid fa-book-open mr-2"></i> Passage:
                            </p>
                            <div class="text-slate-300 leading-relaxed whitespace-pre-wrap">{{ $currentQuestion->passage }}</div>
                        </div>
                    @endif
                    
                    {{-- Question --}}
                    <div class="mb-8">
                        <div class="text-sm text-slate-500 mb-2">Soal {{ $questionIndex + 1 }}</div>
                        <h2 class="text-xl font-semibold leading-relaxed">{{ $currentQuestion->question }}</h2>
                    </div>
                    
                    {{-- Options --}}
                    <div class="space-y-3" id="optionsContainer">
                        @foreach(['A', 'B', 'C', 'D'] as $opt)
                            @php
                                $optKey = 'option_' . strtolower($opt);
                                $optText = $currentQuestion->$optKey ?? '';
                                $isSelected = ($answers->get($currentQuestion->id)?->answer ?? null) === $opt;
                            @endphp
                            @if($optText)
                                <button type="button" 
                                    class="option-btn {{ $isSelected ? 'selected' : '' }}"
                                    data-option="{{ $opt }}"
                                    onclick="selectAnswer('{{ $opt }}')">
                                    <span class="w-8 h-8 rounded-full {{ $isSelected ? 'bg-emerald-500' : 'bg-slate-600' }} flex items-center justify-center font-bold shrink-0">
                                        {{ $opt }}
                                    </span>
                                    <span>{{ $optText }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>
                    
                    {{-- Navigation --}}
                    <div class="flex items-center justify-between mt-10 pt-6 border-t border-slate-700">
                        @if($questionIndex > 0)
                            <a href="{{ route('ept.quiz.show', ['attempt' => $attempt, 'q' => $questionIndex - 1]) }}"
                               class="px-6 py-3 bg-slate-700 rounded-xl font-semibold hover:bg-slate-600 transition-colors">
                                <i class="fa-solid fa-arrow-left mr-2"></i> Sebelumnya
                            </a>
                        @else
                            <div></div>
                        @endif
                        
                        @if($questionIndex < $questions->count() - 1)
                            <a href="{{ route('ept.quiz.show', ['attempt' => $attempt, 'q' => $questionIndex + 1]) }}"
                               class="px-6 py-3 bg-emerald-600 rounded-xl font-semibold hover:bg-emerald-700 transition-colors">
                                Selanjutnya <i class="fa-solid fa-arrow-right ml-2"></i>
                            </a>
                        @else
                            <button type="button" onclick="finishSection()" 
                                class="px-6 py-3 bg-blue-600 rounded-xl font-semibold hover:bg-blue-700 transition-colors">
                                {{ $section === 'reading' ? 'Selesai Ujian' : 'Lanjut Section →' }}
                            </button>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    {{-- Status Indicator --}}
    <div id="saveStatus" class="fixed bottom-4 right-4 px-4 py-2 rounded-lg text-sm font-medium transition-all opacity-0">
    </div>
    
    <script>
        const attemptId = {{ $attempt->id }};
        const questionId = {{ $currentQuestion->id }};
        const csrfToken = '{{ csrf_token() }}';
        const section = '{{ $section }}';
        let remainingSeconds = {{ $remainingSeconds }};
        
        // Timer
        function updateTimer() {
            if (remainingSeconds <= 0) {
                document.getElementById('timerDisplay').textContent = '00:00';
                handleTimeout();
                return;
            }
            
            remainingSeconds--;
            const mins = Math.floor(remainingSeconds / 60);
            const secs = remainingSeconds % 60;
            document.getElementById('timerDisplay').textContent = 
                String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
            
            // Warning color
            if (remainingSeconds <= 60) {
                document.getElementById('timer').classList.add('animate-pulse');
            }
        }
        
        setInterval(updateTimer, 1000);
        updateTimer();
        
        // Save Answer
        async function selectAnswer(option) {
            // Update UI
            document.querySelectorAll('.option-btn').forEach(btn => {
                btn.classList.remove('selected');
                btn.querySelector('span').classList.remove('bg-emerald-500');
                btn.querySelector('span').classList.add('bg-slate-600');
            });
            
            const selected = document.querySelector(`[data-option="${option}"]`);
            if (selected) {
                selected.classList.add('selected');
                selected.querySelector('span').classList.remove('bg-slate-600');
                selected.querySelector('span').classList.add('bg-emerald-500');
            }
            
            // Save to server
            showStatus('Menyimpan...', 'info');
            
            try {
                const response = await fetch(`/ept/quiz/${attemptId}/answer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        answer: option,
                    }),
                });
                
                const data = await response.json();
                
                if (data.status === 'saved') {
                    showStatus('Tersimpan ✓', 'success');
                    // Update nav button
                    updateNavButton();
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    showStatus('Gagal menyimpan', 'error');
                }
            } catch (err) {
                showStatus('Error koneksi', 'error');
            }
        }
        
        function updateNavButton() {
            const idx = {{ $questionIndex }};
            const navBtns = document.querySelectorAll('.question-nav-btn');
            if (navBtns[idx]) {
                navBtns[idx].classList.remove('unanswered');
                navBtns[idx].classList.add('answered');
            }
        }
        
        function showStatus(text, type) {
            const el = document.getElementById('saveStatus');
            el.textContent = text;
            el.className = 'fixed bottom-4 right-4 px-4 py-2 rounded-lg text-sm font-medium transition-all';
            
            if (type === 'success') el.classList.add('bg-emerald-600');
            else if (type === 'error') el.classList.add('bg-red-600');
            else el.classList.add('bg-blue-600');
            
            el.style.opacity = '1';
            setTimeout(() => { el.style.opacity = '0'; }, 2000);
        }
        
        async function finishSection() {
            if (section === 'reading') {
                if (!confirm('Anda yakin ingin menyelesaikan ujian?')) return;
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/ept/quiz/${attemptId}/submit`;
                form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
                document.body.appendChild(form);
                form.submit();
            } else {
                if (!confirm(`Lanjut ke section ${section === 'listening' ? 'Structure' : 'Reading'}?`)) return;
                
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/ept/quiz/${attemptId}/next-section`;
                form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        async function handleTimeout() {
            const response = await fetch(`/ept/quiz/${attemptId}/ping`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            const data = await response.json();
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        }
        
        // Heartbeat setiap 30 detik
        setInterval(async () => {
            try {
                const response = await fetch(`/ept/quiz/${attemptId}/ping`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });
                const data = await response.json();
                if (data.remaining) remainingSeconds = data.remaining;
                if (data.redirect) window.location.href = data.redirect;
            } catch (e) {}
        }, 30000);
    </script>
</body>
</html>
