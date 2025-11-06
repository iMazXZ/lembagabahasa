<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningQuiz;
use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningQuestion;
use App\Models\BasicListeningSession;
use Illuminate\Http\Request;

class BasicListeningQuizFibController extends Controller
{
    // =========================
    //  START ATTEMPT (opsional)
    // =========================
    public function start(Request $request, $quizId)
    {
        $quiz = BasicListeningQuiz::findOrFail($quizId);

        // Jika ada session_id kiriman, pakai durasi session
        $session = null;
        if ($request->filled('session_id')) {
            $session = BasicListeningSession::find($request->input('session_id'));
        }

        $durationSeconds = $session
            ? max(60, (int) ($session->duration_minutes ?? 10) * 60)
            : (int) ($quiz->duration_seconds ?? 600);

        $attempt = BasicListeningAttempt::firstOrCreate(
            [
                'user_id'      => $request->user()->id,
                'quiz_id'      => $quiz->id,
                'session_id'   => $session?->id,
                'submitted_at' => null,
            ],
            [
                'started_at' => now(),
                'expires_at' => now()->addSeconds(max(60, $durationSeconds)),
            ]
        );

        // Sinkronkan expire jika durasi berubah
        if ($attempt->expires_at && $attempt->started_at) {
            $span = $attempt->expires_at->diffInSeconds($attempt->started_at);
            if (abs($span - $durationSeconds) > 3) {
                $attempt->forceFill([
                    'expires_at' => $attempt->started_at->copy()->addSeconds($durationSeconds),
                ])->save();
            }
        }

        // Untuk FIB, halaman kerja = GET bl.quiz (by quiz id)
        return redirect()->route('bl.quiz', $quiz->id);
    }

    // =========================
    //  SHOW QUIZ (FIB 1 paragraf)
    // =========================
    public function show(Request $request, $quizId)
    {
        $quiz = BasicListeningQuiz::findOrFail($quizId);

        // --- Wajib punya session terkait
        $session = $quiz->session ?? null;
        if (!$session) {
            return redirect()
                ->route('bl.index')
                ->with('error', 'Quiz ini tidak terhubung ke sesi mana pun. Hubungi tutor/admin.');
        }

        $userId    = $request->user()->id;
        $sessionId = $session->id;

        // --- Attempt unik per (user, session, quiz)
        $attempt = BasicListeningAttempt::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->first();

        if (!$attempt) {
            // (Opsional) Blokir jika masih ada attempt aktif untuk kuis lain di sesi yang sama
            $otherActive = BasicListeningAttempt::where('user_id', $userId)
                ->where('session_id', $sessionId)
                ->where('quiz_id', '!=', $quiz->id)
                ->whereNull('submitted_at')
                ->first();

            if ($otherActive) {
                return redirect()
                    ->route('bl.history.show', $otherActive->id)
                    ->with('error', 'Anda masih memiliki attempt aktif untuk kuis lain di sesi ini. Selesaikan terlebih dahulu.');
            }

            $durationSeconds = (int) ($session->duration_minutes ? $session->duration_minutes * 60 : ($quiz->duration_seconds ?? 600));
            $durationSeconds = max(60, $durationSeconds);

            $attempt = BasicListeningAttempt::firstOrCreate(
                [
                    'user_id'      => $userId,
                    'session_id'   => $sessionId,
                    'quiz_id'      => $quiz->id,   // ← kunci unik per kuis
                    'submitted_at' => null,
                ],
                [
                    'started_at' => now(),
                    'expires_at' => now()->addSeconds($durationSeconds),
                ]
            );
        }

        // Sudah submitted? ke riwayat
        if ($attempt->submitted_at) {
            return redirect()
                ->route('bl.history.show', $attempt->id)
                ->with('warning', 'Attempt untuk kuis ini sudah dikumpulkan.');
        }

        // Sinkronkan expiry bila durasi berubah
        $durationSeconds = (int) ($session->duration_minutes ? $session->duration_minutes * 60 : ($quiz->duration_seconds ?? 600));
        $durationSeconds = max(60, $durationSeconds);

        if (empty($attempt->started_at)) {
            $attempt->forceFill(['started_at' => now()])->save();
        }
        if ($attempt->started_at && $attempt->expires_at) {
            $span = $attempt->expires_at->diffInSeconds($attempt->started_at);
            if (abs($span - $durationSeconds) > 3) {
                $attempt->forceFill([
                    'expires_at' => $attempt->started_at->copy()->addSeconds($durationSeconds),
                ])->save();
            }
        }

        // Guard waktu
        $remaining = max(0, now()->diffInSeconds($attempt->expires_at, false));
        if ($remaining <= 0) {
            $this->gradeFibAttempt($attempt); // grade saat timeout
            return redirect()
                ->route('bl.history.show', $attempt->id)
                ->with('warning', 'Time is up. Answers saved & graded at timeout.');
        }

        // Ambil soal FIB
        $question = BasicListeningQuestion::where('quiz_id', $quiz->id)
            ->where('type', 'fib_paragraph')
            ->firstOrFail();

        // Process paragraf → input; simpan blank-map ke session
        $processedParagraph = $this->processParagraph($question->paragraph_text, $question->id, $attempt);
        $blankCount         = $this->countBlanks($question->paragraph_text);

        return view('bl.quiz_fib', [
            'quiz'               => $quiz,
            'attempt'            => $attempt,
            'question'           => $question,
            'remainingSeconds'   => $remaining,
            'processedParagraph' => $processedParagraph,
            'blankCount'         => $blankCount,
            'currentIndex'       => 0,
            'totalQuestions'     => 1,
            'isLastQuestion'     => true,
        ]);
    }

    // ===================================
    //  Helper: derive map dari paragraf
    //  return: ['<blankNumber>' => <seqIndex 0..N-1>]
    // ===================================
    private function deriveBlankMapFromParagraph(string $paragraph): array
    {
        $map = [];
        $seq = 0;
        if (preg_match_all('/\[\[(\d+)\]\]|\[blank\]/', $paragraph, $m, PREG_SET_ORDER)) {
            foreach ($m as $hit) {
                if (!empty($hit[1])) {
                    // [[n]] → gunakan n sebagai key
                    $map[(string)$hit[1]] = $seq;
                } else {
                    // [blank] tanpa nomor → beri nomor sintetis U<seq>
                    $map['U'.$seq] = $seq;
                }
                $seq++;
            }
        }
        return $map;
    }

    /** Render paragraf FIB → HTML input */
    private function processParagraph($paragraph, $questionId, $attempt)
    {
        if (empty($paragraph)) {
            \Log::warning('Paragraph is empty, using fallback');
            $paragraph = "Please listen to the audio and fill in the missing words.\n\nThe weather today is [[1]]. I can hear [[2]] outside. The birds are [[3]].";
        }

        // Simpan blank map berdasarkan urutan kemunculan token
        $blankMap = $this->deriveBlankMapFromParagraph($paragraph);
        if (!empty($blankMap)) {
            session(['fib_blank_map_' . $questionId => $blankMap]);
        }

        // jaga line breaks
        $paragraph = nl2br($paragraph);

        // Ambil jawaban tersimpan (keyed by blank_index 0..N-1)
        $existingAnswers = [];
        $savedAnswers = $attempt->answers()
            ->where('question_id', $questionId)
            ->get(['blank_index', 'answer']);

        foreach ($savedAnswers as $answer) {
            $existingAnswers[(int)$answer->blank_index] = (string)$answer->answer;
        }

        // Render input
        $index = 0;
        $processed = preg_replace_callback(
            '/\[\[(\d+)\]\]|\[blank\]/',
            function () use (&$index, $existingAnswers) {
                $value = $existingAnswers[$index] ?? '';
                $html  = '<input type="text" class="fib-input" name="answers[' . $index . ']" value="' . e($value) . '" placeholder="..." style="border:2px solid #3b82f6;padding:8px;margin:0 4px;border-radius:6px;min-width:120px;">';
                $index++;
                return $html;
            },
            $paragraph
        );

        \Log::info('Paragraph processed', [
            'blank_map'       => $blankMap,
            'blanks_created'  => $index,
            'paragraph_first' => mb_substr($paragraph, 0, 100) . '...',
        ]);

        return $processed;
    }

    private function countBlanks($paragraph)
    {
        if (empty($paragraph)) return 3;
        preg_match_all('/\[\[(\d+)\]\]|\[blank\]/', $paragraph, $matches);
        $count = count($matches[0]);
        return $count > 0 ? $count : 3;
    }

    // ==========================
    //  SAVE (Simpan Sementara)
    // ==========================
    public function answer(Request $request, $attemptId)
    {
        $attempt = BasicListeningAttempt::where('user_id', $request->user()->id)
            ->where('id', $attemptId)
            ->whereNull('submitted_at')
            ->firstOrFail();

        // Grace period kecil untuk race-condition
        if (now()->greaterThan($attempt->expires_at->copy()->addSeconds(2))) {
            return redirect()
                ->route('bl.history.show', $attempt->id)
                ->with('warning', 'Time is up. Your answers were not saved.');
        }

        $question = BasicListeningQuestion::where('quiz_id', $attempt->quiz_id)
            ->where('type', 'fib_paragraph')
            ->firstOrFail();

        $questionId  = $question->id;
        $userAnswers = (array) $request->input('answers', []);

        foreach ($userAnswers as $index => $answer) {
            $idx = (string)$index;
            $existing = $attempt->answers()
                ->where('question_id', $questionId)
                ->where('blank_index', $idx)
                ->first();

            // Jika kosong → hapus record lama supaya tidak tersisa jawaban lama
            if (trim((string)$answer) === '') {
                if ($existing) $existing->delete();
                continue;
            }

            // Upsert jawaban
            $attempt->answers()->updateOrCreate(
                ['question_id' => $questionId, 'blank_index' => $idx],
                ['answer' => (string)$answer]
            );
        }

        return redirect()->route('bl.quiz', $attempt->quiz_id)
            ->with('success', 'Jawaban berhasil disimpan.');
    }

    // ==========================
    //  Utilities penilaian
    // ==========================
    private function normalize(string $s, array $scoring): string
    {
        if (($scoring['allow_trim'] ?? true)) $s = trim($s);
        if (!($scoring['case_sensitive'] ?? false)) $s = mb_strtolower($s);
        if (($scoring['strip_punctuation'] ?? true)) {
            // Hapus semua tanda baca & simbol (Unicode-aware)
            $s = preg_replace('/[\p{P}\p{S}]+/u', '', $s);
        }
        // Rapatkan spasi berlebih
        $s = preg_replace('/\s+/u', ' ', $s);
        return $s;
    }

    private function matchAnswer(string $userInput, array|string $key, array $scoring): bool
    {
        if (is_array($key) && array_key_exists('regex', $key)) {
            return @preg_match('/' . $key['regex'] . '/ui', $userInput) === 1;
        }
        $userN = $this->normalize($userInput, $scoring);
        $keys  = is_array($key) ? $key : [$key];
        foreach ($keys as $k) {
            if (is_array($k) && array_key_exists('regex', $k)) {
                if (@preg_match('/' . $k['regex'] . '/ui', $userInput) === 1) return true;
                continue;
            }
            $kN = $this->normalize((string) $k, $scoring);
            if ($kN === $userN) return true;
        }
        return false;
    }

    /**
     * Grading server-side untuk FIB berbasis jawaban yang tersimpan.
     * Dipanggil saat timeout maupun manual.
     */
    private function gradeFibAttempt(BasicListeningAttempt $attempt): void
    {
        $q = BasicListeningQuestion::where('quiz_id', $attempt->quiz_id)
            ->where('type', 'fib_paragraph')
            ->firstOrFail();

        $scoring = $q->fib_scoring ?? [
            'mode'              => 'exact',
            'case_sensitive'    => false,
            'allow_trim'        => true,
            'strip_punctuation' => true,
        ];
        $weights = $q->fib_weights ?? [];
        $keys    = $q->fib_answer_key ?? [];

        // Map placeholder → seqIndex berdasar paragraf (satu sumber kebenaran)
        $blankMap = $this->deriveBlankMapFromParagraph($q->paragraph_text);

        // Jawaban tersimpan keyed by seqIndex
        $saved = $attempt->answers()
            ->where('question_id', $q->id)
            ->get(['blank_index', 'answer'])
            ->pluck('answer', 'blank_index')
            ->all();

        $qScore = 0.0;
        $qWeight = 0.0;

        foreach ($blankMap as $blankNumber => $seqIndex) {
            $w = (float) ($weights[$blankNumber] ?? 1);
            $qWeight += $w;

            $userVal = (string) ($saved[$seqIndex] ?? '');
            $key     = $keys[$blankNumber] ?? null;
            $correct = $key ? $this->matchAnswer($userVal, $key, $scoring) : false;

            $attempt->answers()->updateOrCreate(
                ['question_id' => $q->id, 'blank_index' => (string) $seqIndex],
                ['answer' => $userVal, 'is_correct' => $correct]
            );

            if ($correct) $qScore += $w;
        }

        $final = $qWeight > 0 ? round(($qScore / $qWeight) * 100, 2) : 0;

        $attempt->forceFill([
            'score'        => $final,
            'submitted_at' => now(),
        ])->save();

        session()->forget('fib_blank_map_' . $q->id);
    }

    // ==========================
    //  SUBMIT (Kumpulkan)
    // ==========================
    public function submit(Request $request, $quizId)
    {
        $quiz = BasicListeningQuiz::findOrFail($quizId);

        $attempt = BasicListeningAttempt::where('user_id', $request->user()->id)
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->firstOrFail();

        $q = BasicListeningQuestion::where('quiz_id', $quiz->id)
            ->where('type', 'fib_paragraph')
            ->firstOrFail();

        $scoring = $q->fib_scoring ?? [
            'mode'              => 'exact',
            'case_sensitive'    => false,
            'allow_trim'        => true,
            'strip_punctuation' => true,
        ];
        $weights = $q->fib_weights ?? [];
        $keys    = $q->fib_answer_key ?? [];

        $userFib = (array) $request->input('answers', []); // index: 0..N-1

        // Ambil blank map dari session atau derive dari paragraf (konsisten)
        $blankMap = session('fib_blank_map_' . $q->id) ?: $this->deriveBlankMapFromParagraph($q->paragraph_text);

        // Susun user answers keyed by nomor placeholder
        $mappedUserFib = [];
        foreach ($blankMap as $blankNumber => $seq) {
            $mappedUserFib[$blankNumber] = $userFib[$seq] ?? '';
        }

        $qScore = 0.0;
        $qWeight = 0.0;

        foreach ($blankMap as $blankNumber => $seqIndex) {
            $w = (float) ($weights[$blankNumber] ?? 1);
            $qWeight += $w;

            $userVal = (string) ($mappedUserFib[$blankNumber] ?? '');
            $key     = $keys[$blankNumber] ?? null;
            $correct = $key ? $this->matchAnswer($userVal, $key, $scoring) : false;

            $attempt->answers()->updateOrCreate(
                ['question_id' => $q->id, 'blank_index' => (string)$seqIndex],
                ['answer' => $userVal, 'is_correct' => $correct]
            );

            if ($correct) $qScore += $w;
        }

        $finalScore = $qWeight > 0 ? round(($qScore / $qWeight) * 100, 2) : 0;

        session()->forget('fib_blank_map_' . $q->id);

        $attempt->update(['score' => $finalScore, 'submitted_at' => now()]);

        if (now()->greaterThan($attempt->expires_at)) {
            return redirect()->route('bl.history.show', $attempt->id)
                ->with('warning', 'Time is up. Answers saved & graded at timeout.');
        }

        return redirect()->route('bl.history.show', $attempt->id)
            ->with('success', 'Your answers have been submitted.');
    }
}
