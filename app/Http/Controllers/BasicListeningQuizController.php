<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningAnswer;
use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningQuestion;
use Illuminate\Http\Request;

class BasicListeningQuizController extends Controller
{
    /**
     * Tampilkan Halaman Quiz (Menangani MC dan FIB).
     */
    public function show(BasicListeningAttempt $attempt, Request $request)
    {
        $user = $request->user();

        // 🔒 Authorize
        if (! $user || $attempt->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke attempt ini.');
        }

        $session = $attempt->session;

        // 🚫 Cek Status
        if ($attempt->submitted_at) {
            return redirect()
                ->route('bl.history.show', $attempt->id)
                ->with('warning', 'Kuis sudah dikumpulkan.');
        }

        if (! $session->isOpen()) {
            return redirect()
                ->route('bl.history')
                ->with('error', 'Waktu sesi sudah habis.');
        }

        // ⏱️ Start Timer
        if (empty($attempt->started_at)) {
            $attempt->forceFill(['started_at' => now()])->save();
        }

        // ⏳ Hitung Sisa Waktu
        $remainingSeconds = null;
        $durationMin = (int) ($session->duration_minutes ?? 0);
        if ($durationMin === 0 && $attempt->quiz->duration_minutes) {
             $durationMin = (int) $attempt->quiz->duration_minutes;
        }

        if ($durationMin > 0 && $attempt->started_at) {
            $deadline = $attempt->started_at->clone()->addMinutes($durationMin);
            if (now()->greaterThanOrEqualTo($deadline)) {
                return $this->finalize($attempt);
            }
            $remainingSeconds = now()->diffInSeconds($deadline, false);
        }

        // 📚 Load Data Soal
        $questions = $attempt->quiz->questions()->get();
        $currentIndex = max(0, (int) $request->query('q', 0));
        $currentIndex = min($currentIndex, max(0, $questions->count() - 1));
        
        $question = $questions[$currentIndex] ?? abort(404);

        // 🧮 Cek Progress
        $answeredIds = $attempt->answers()
            ->whereNotNull('answer')
            ->where('answer', '!=', '') // Anggap string kosong sbg belum dijawab utk navigasi
            ->pluck('question_id')
            ->all();

        $unansweredCount = $questions->count() - count($answeredIds);

        // --- LOGIKA FIB ---
        $processedParagraph = null;
        if ($question->type === 'fib_paragraph') {
            $savedAnswers = $attempt->answers()
                ->where('question_id', $question->id)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->blank_index => $item->answer];
                })
                ->toArray();
            
            // Render paragraf dengan input
            $processedParagraph = $this->processParagraph($question->paragraph_text, $savedAnswers);
        } 
        
        // Jawaban MC (Single)
        $answer = BasicListeningAnswer::firstOrNew([
            'attempt_id'  => $attempt->id,
            'question_id' => $question->id,
        ]);

        return view('bl.quiz', compact(
            'attempt', 'question', 'currentIndex', 'questions', 'answer', 
            'processedParagraph', 'remainingSeconds', 'answeredIds', 'unansweredCount'
        ));
    }

    /**
     * Simpan Jawaban (Support AJAX & Auto-Save).
     */
    public function answer(BasicListeningAttempt $attempt, Request $request)
    {
        // 1. Cek Akses & AJAX
        $isAjax = $request->wantsJson() || $request->ajax();

        if (! $request->user() || $attempt->user_id !== $request->user()->id) {
            return $isAjax ? response()->json(['error' => 'Unauthorized'], 403) : abort(403);
        }

        // 2. Cek Timeout (dengan toleransi 10 detik)
        $session = $attempt->session;
        $durationMin = (int) ($session->duration_minutes ?? 0);
        if ($durationMin > 0 && $attempt->started_at) {
            $deadline = $attempt->started_at->clone()->addMinutes($durationMin);
            if (now()->greaterThan($deadline->addSeconds(10))) {
                return $isAjax 
                    ? response()->json(['status' => 'expired'], 408)
                    : $this->finalize($attempt);
            }
        }

        // 3. Validasi Input
        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'q'           => ['nullable', 'integer'],
            'answer'      => ['nullable'], 
            'answers'     => ['nullable', 'array'],
        ]);

        $question = BasicListeningQuestion::findOrFail($data['question_id']);

        // 4. Simpan Jawaban
        if ($question->type === 'fib_paragraph') {
            // === FIB ===
            $userAnswers = $request->input('answers', []);
            foreach ($userAnswers as $index => $val) {
                $val = (string) $val;
                
                // PENTING: Simpan updateOrCreate meskipun nilai kosong
                // Agar record tetap ada di database (untuk Admin Panel)
                BasicListeningAnswer::updateOrCreate(
                    [
                        'attempt_id'  => $attempt->id,
                        'question_id' => $question->id,
                        'blank_index' => $index
                    ],
                    [
                        'answer'      => $val, 
                        'is_correct'  => false // Reset status
                    ]
                );
            }
        } else {
            // === MC / TRUE FALSE ===
            $ans = BasicListeningAnswer::firstOrNew([
                'attempt_id'  => $attempt->id,
                'question_id' => $question->id,
            ]);
            $ans->blank_index = 0;
            $ans->answer      = $data['answer'] ?? null;
            $ans->is_correct  = false; // Reset status
            $ans->save();
        }

        // 5. Respon
        // Jika AJAX (Auto-save), kirim JSON sukses
        if ($isAjax) {
            return response()->json(['status' => 'saved']);
        }

        // Jika tombol Finish ditekan
        if ($request->has('finish_attempt')) {
            return $this->finalize($attempt);
        }

        // Redirect Normal (Next Question)
        $currentIndex = max(0, (int) ($data['q'] ?? 0));
        $total = $attempt->quiz->questions()->count();
        $nextIndex = min($currentIndex + 1, max(0, $total - 1));
        
        return redirect()->route('bl.quiz.show', [
            'attempt' => $attempt->id,
            'q'       => $nextIndex,
        ]);
    }

    /**
     * Submit Akhir (Cek kelengkapan).
     */
    public function submit(BasicListeningAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        $questionsCount = $attempt->quiz->questions()->count();
        // Hitung soal yang sudah ada jawabannya (tidak null/kosong)
        $answeredQuestionsCount = $attempt->answers()
            ->whereNotNull('answer')
            ->where('answer', '!=', '')
            ->distinct('question_id')
            ->count('question_id');

        $unansweredCount = $questionsCount - $answeredQuestionsCount;

        if ($unansweredCount > 0) {
            return redirect()->back()
                ->with('warning', "Masih ada <strong>{$unansweredCount} soal</strong> yang belum terjawab.")
                ->with('showSubmitConfirm', true);
        }

        return $this->finalize($attempt);
    }

    /**
     * Submit Paksa (Timer habis).
     */
    public function forceSubmit(BasicListeningAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);
        return $this->finalize($attempt);
    }

    /**
     * Heartbeat — dipanggil setiap 20 detik.
     * Jika waktu habis & user koneksi terputus, server tetap finalize otomatis.
     */
    public function ping(BasicListeningAttempt $attempt, Request $request)
    {
        // Pastikan user benar
        $this->authorizeAttempt($attempt, $request);

        // Jika sudah submit, langsung kembalikan redirect
        if ($attempt->submitted_at) {
            return response()->json([
                'expired' => true,
                'redirect' => route('bl.history.show', $attempt)
            ]);
        }

        $session = $attempt->session;
        $duration = (int) ($session->duration_minutes ?? $attempt->quiz->duration_minutes);

        if ($duration > 0 && $attempt->started_at) {
            $deadline = $attempt->started_at->clone()->addMinutes($duration);

            // Jika waktu habis (server yang memutuskan)
            if (now()->greaterThanOrEqualTo($deadline)) {
                // Pastikan finalize dijalankan di server, tanpa butuh browser
                $this->finalize($attempt);

                return response()->json([
                    'expired' => true,
                    'redirect' => route('bl.history.show', $attempt)
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Finalisasi & Penilaian.
     */
    protected function finalize(BasicListeningAttempt $attempt)
    {
        if ($attempt->submitted_at) {
            return redirect()->route('bl.history.show', $attempt->id);
        }

        $questions = $attempt->quiz->questions()->get();
        // Group jawaban berdasarkan question_id
        $allAnswers = $attempt->answers()->get()->groupBy('question_id');

        $totalScore = 0;
        $totalMaxScore = 0;

        foreach ($questions as $q) {
            if ($q->type !== 'fib_paragraph') {
                // --- PENILAIAN MC ---
                $ans = $allAnswers->get($q->id)?->first();
                $isCorrect = $ans && ($ans->answer === $q->correct);
                
                if ($ans) {
                    $ans->is_correct = $isCorrect;
                    $ans->save();
                }
                if ($isCorrect) $totalScore++;
                $totalMaxScore++;
            } 
            else {
                // --- PENILAIAN FIB (FIXED LOGIC) ---
                $userAnswers = $allAnswers->get($q->id);
                
                // Kunci Jawaban
                $keys = $q->fib_answer_key ?? [];
                $weights = $q->fib_weights ?? [];
                $scoring = $q->fib_scoring ?? [];

                // Normalisasi Kunci ke 0-based Sequential Array
                // Agar cocok dengan input answers[0], answers[1]...
                $normalizedKeys = array_values($keys); 
                $normalizedWeights = array_values($weights);

                $qScore = 0;
                $qMaxWeight = 0;

                // Loop berdasarkan urutan kunci (0, 1, 2...)
                foreach ($normalizedKeys as $seqIndex => $correctKey) {
                    $w = (float)($normalizedWeights[$seqIndex] ?? 1);
                    $qMaxWeight += $w;

                    // Cari jawaban user di index urutan tersebut
                    $uAns = $userAnswers?->where('blank_index', (string)$seqIndex)->first();
                    $uVal = $uAns ? $uAns->answer : '';

                    $isCorrect = $this->checkFibAnswer($uVal, $correctKey, $scoring);

                    if ($uAns) {
                        $uAns->is_correct = $isCorrect;
                        $uAns->save();
                    }

                    if ($isCorrect) $qScore += $w;
                }

                // Hitung proporsi skor FIB (Max 1 poin per soal paragraf)
                if ($qMaxWeight > 0) {
                    $totalScore += ($qScore / $qMaxWeight);
                }
                $totalMaxScore++;
            }
        }

        // Hitung Skor Akhir (Skala 100)
        $finalScore = $totalMaxScore > 0 
            ? (int) round(($totalScore / $totalMaxScore) * 100)
            : 0;

        $attempt->update([
            'score'        => $finalScore,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('bl.history.show', $attempt->id)
            ->with('success', "Submit selesai. Skor kamu: {$finalScore}");
    }

    protected function authorizeAttempt($attempt, $request): void
    {
        abort_unless($request->user() && $attempt->user_id === $request->user()->id, 403);
    }

    /**
     * Helper: Render HTML Input FIB (Sequential Index 0..N)
     */
    private function processParagraph($paragraph, array $existingAnswers = [])
    {
        if (empty($paragraph)) return '';

        $counter = 0; // Mulai dari 0 agar sinkron dengan array_values() di finalize
        
        $processed = preg_replace_callback(
            '/\[\[(\d+)\]\]|\[blank\]/',
            function ($matches) use (&$counter, $existingAnswers) {
                
                $index = $counter++; // 0, 1, 2...
                $value = $existingAnswers[$index] ?? '';
                
                // Style input
                return '<input type="text" class="fib-input" '
                       . 'name="answers[' . $index . ']" '
                       . 'value="' . e($value) . '" '
                       . 'placeholder="..." '
                       . 'autocomplete="off" '
                       . 'style="display: inline-block; vertical-align: baseline; min-width: 80px; width: auto; margin: 0 2px; border-bottom: 2px solid #93c5fd; background: #eff6ff; padding: 2px 6px; border-radius: 4px; font-weight: 600; color: #1e3a8a;">';
            },
            $paragraph
        );

        return nl2br($processed);
    }

    /**
     * Helper: Cek Jawaban
     */
    private function checkFibAnswer($userVal, $key, $scoring)
    {
        $caseSensitive  = filter_var($scoring['case_sensitive'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $allowTrim      = filter_var($scoring['allow_trim'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $stripPunct     = filter_var($scoring['strip_punctuation'] ?? true, FILTER_VALIDATE_BOOLEAN);

        // Normalisasi User Input
        $u = (string)$userVal;
        if ($allowTrim) $u = trim($u);
        if (!$caseSensitive) $u = mb_strtolower($u);
        if ($stripPunct) $u = preg_replace('/[\p{P}\p{S}]+/u', '', $u);
        $u = preg_replace('/\s+/u', ' ', $u);

        $keys = is_array($key) ? $key : [$key];

        foreach ($keys as $k) {
            if (is_array($k) && isset($k['regex'])) {
                if (@preg_match('/' . $k['regex'] . '/ui', $userVal)) return true;
                continue;
            }

            $kStr = (string)$k;
            if ($allowTrim) $kStr = trim($kStr);
            if (!$caseSensitive) $kStr = mb_strtolower($kStr);
            if ($stripPunct) $kStr = preg_replace('/[\p{P}\p{S}]+/u', '', $kStr);
            $kStr = preg_replace('/\s+/u', ' ', $kStr);

            if ($u === $kStr) return true;
        }

        return false;
    }
}