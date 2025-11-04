<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningQuiz;
use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningQuestion;
use App\Models\BasicListeningSession;
use Illuminate\Http\Request;

class BasicListeningQuizFibController extends Controller
{
    // START ATTEMPT (opsional; jarang dipakai jika lewat Connect)
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

    // SHOW QUIZ (FIB 1 paragraf)
    public function show(Request $request, $quizId)
    {
        $quiz = BasicListeningQuiz::findOrFail($quizId);

        // ----- Cari session dari quiz (wajib, karena session_id NOT NULL)
        $session = $quiz->session ?? null;
        if (!$session) {
            return redirect()
                ->route('bl.index')
                ->with('error', 'Quiz ini tidak terhubung ke sesi mana pun. Hubungi tutor/admin.');
        }

        $userId    = $request->user()->id;
        $sessionId = $session->id;

        // ----- Ambil attempt aktif untuk QUIZ INI (kalau ada)
        $attempt = BasicListeningAttempt::where('user_id', $userId)
            ->where('session_id', $sessionId)
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->first();

        if (!$attempt) {
            // 🔎 Cek apakah SUDAH ada attempt aktif lain di session yang sama (quiz berbeda)
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

            // ✅ Aman: belum ada attempt untuk session ini → buat satu attempt baru
            $durationSeconds = (int) ($session->duration_minutes ? $session->duration_minutes * 60 : ($quiz->duration_seconds ?? 600));
            $durationSeconds = max(60, $durationSeconds);

            // Penting: kunci pada (user_id, session_id) untuk menghindari duplikasi
            $attempt = BasicListeningAttempt::firstOrCreate(
                [
                    'user_id'    => $userId,
                    'session_id' => $sessionId,
                ],
                [
                    'quiz_id'      => $quiz->id,
                    'started_at'   => now(),
                    'expires_at'   => now()->addSeconds($durationSeconds),
                    'submitted_at' => null,
                ]
            );

            // Jika firstOrCreate menemukan attempt lama (created = false) tapi quiz_id beda dan belum submitted → blokir
            if ($attempt->wasRecentlyCreated === false && $attempt->quiz_id != $quiz->id && is_null($attempt->submitted_at)) {
                return redirect()
                    ->route('bl.history.show', $attempt->id)
                    ->with('error', 'Anda masih memiliki attempt aktif untuk kuis lain di sesi ini. Selesaikan terlebih dahulu.');
            }

            // Jika attempt lama tapi sudah submitted, arahkan ke riwayat
            if ($attempt->submitted_at) {
                return redirect()
                    ->route('bl.history.show', $attempt->id)
                    ->with('warning', 'Attempt di sesi ini sudah dikumpulkan.');
            }
        }

        // ⏳ Guard waktu
        $remaining = max(0, now()->diffInSeconds($attempt->expires_at, false));
        if ($remaining <= 0) {
            $attempt->update(['submitted_at' => now(), 'score' => $attempt->score ?? 0]);
            return redirect()->route('bl.history.show', $attempt->id)->with('warning', 'Time is up.');
        }

        $question = BasicListeningQuestion::where('quiz_id', $quiz->id)
            ->where('type', 'fib_paragraph')
            ->firstOrFail();

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

    /** Render paragraf FIB → input */
    private function processParagraph($paragraph, $questionId, $attempt)
    {
        if (empty($paragraph)) {
            \Log::warning('Paragraph is empty, using fallback');
            $paragraph = "Please listen to the audio and fill in the missing words.\n\nThe weather today is [[1]]. I can hear [[2]] outside. The birds are [[3]].";
        }

        // jaga line breaks
        $paragraph = nl2br($paragraph);

        // Ambil jawaban tersimpan (keyed by blank_index: 0,1,2,...)
        $existingAnswers = [];
        $savedAnswers = $attempt->answers()
            ->where('question_id', $questionId)
            ->get();

        foreach ($savedAnswers as $answer) {
            $existingAnswers[$answer->blank_index] = $answer->answer;
        }

        $index = 0;      // urutan kemunculan token
        $blankMap = [];  // map: blankNumber => seqIndex

        $processed = preg_replace_callback(
            '/\[\[(\d+)\]\]|\[blank\]/',
            function ($matches) use (&$index, $existingAnswers, &$blankMap) {
                if (isset($matches[1])) {
                    $blankNumber = $matches[1];     // [[n]]
                    $blankMap[$blankNumber] = $index;
                }
                $inputIndex = $index;

                $value = $existingAnswers[$inputIndex] ?? '';
                $input = '<input type="text" class="fib-input" name="answers[' . $inputIndex . ']" value="' . e($value) . '" placeholder="..." style="border: 2px solid #3b82f6; padding: 8px; margin: 0 4px; border-radius: 6px; min-width: 120px;">';
                $index++;
                return $input;
            },
            $paragraph
        );

        if (!empty($blankMap)) {
            session(['fib_blank_map_' . $questionId => $blankMap]); // contoh: [ '1' => 0, '3' => 1, '2' => 2 ]
        }

        \Log::info('Paragraph processed', [
            'blank_map'       => $blankMap,
            'blanks_created'  => $index,
            'paragraph_first' => substr($paragraph, 0, 100) . '...',
        ]);

        return $processed;
    }

    private function countBlanks($paragraph)
    {
        if (empty($paragraph)) {
            return 3;
        }
        preg_match_all('/\[\[(\d+)\]\]|\[blank\]/', $paragraph, $matches);
        $count = count($matches[0]);
        return $count > 0 ? $count : 3;
    }

    // SAVE & CONTINUE jawaban FIB
    public function answer(Request $request, $attemptId)
    {
        $attempt = BasicListeningAttempt::where('user_id', $request->user()->id)
            ->where('id', $attemptId)
            ->whereNull('submitted_at')
            ->firstOrFail();

        // Grace period kecil (2 detik) untuk mengurangi race-condition
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
            if (trim((string)$answer) === '') {
                // kosong → biarkan tidak tersimpan; dianggap salah saat penilaian akhir
                continue;
            }

            $attempt->answers()->updateOrCreate(
                [
                    'question_id' => $questionId,
                    'blank_index' => (string) $index, // index urut (0..N) sesuai urutan token
                ],
                [
                    'answer' => $answer,
                ]
            );
        }

        // Kembali ke halaman kerja FIB (GET)
        return redirect()->route('bl.quiz', $attempt->quiz_id)
            ->with('success', 'Jawaban berhasil disimpan.');
    }

    // Utilities penilaian
    private function normalize(string $s, array $scoring): string
    {
        if (($scoring['allow_trim'] ?? true)) $s = trim($s);
        if (!($scoring['case_sensitive'] ?? false)) $s = mb_strtolower($s);
        if (($scoring['strip_punctuation'] ?? true)) {
            $s = preg_replace('/[[:punct:]]+/u', '', $s);
        }
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
            'mode' => 'exact', 'case_sensitive' => false,
            'allow_trim' => true, 'strip_punctuation' => true,
        ];
        $weights = $q->fib_weights ?? [];
        $keys    = $q->fib_answer_key ?? []; // contoh: ['1' => 'Are', '2'=>'Is', ...]
        $userFib = (array) $request->input('answers', []); // index: 0..N

        // ---- Ambil / bangun blank map (placeholder → seqIndex 0-based)
        $blankMap = session('fib_blank_map_' . $q->id, []);

        if (empty($blankMap)) {
            if (!empty($keys)) {
                // kunci 1..N → index 0..N-1
                foreach (array_keys($keys) as $bn) {
                    $blankMap[(string)$bn] = max(0, ((int)$bn) - 1);
                }
            } else {
                // fallback terakhir: turunan dari input user (0..N → 1..N)
                foreach (array_keys($userFib) as $i) {
                    $blankMap[(string)($i + 1)] = (int)$i;
                }
            }
        }

        // ---- Susun user answers (keyed by nomor placeholder: '1','2',...)
        $mappedUserFib = [];
        foreach ($blankMap as $blankNumber => $seq) {
            $mappedUserFib[$blankNumber] = $userFib[$seq] ?? '';
        }

        // ---- Penilaian
        $expectedBlanks = array_keys($blankMap); // urut kemunculan
        $qScore = 0.0; $qWeight = 0.0;

        foreach ($expectedBlanks as $blankNumber) {
            $w = (float) ($weights[$blankNumber] ?? 1);
            $qWeight += $w;

            $userVal = (string) ($mappedUserFib[$blankNumber] ?? '');
            $key     = $keys[$blankNumber] ?? null;
            $correct = $key ? $this->matchAnswer($userVal, $key, $scoring) : false;

            // simpan SELALU pakai index 0-based (seqIndex)
            $seqIndex = (string) $blankMap[$blankNumber]; // 0,1,2,...
            $attempt->answers()->updateOrCreate(
                ['question_id' => $q->id, 'blank_index' => $seqIndex],
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
