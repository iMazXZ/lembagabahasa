<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningQuiz;
use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningQuestion;
use App\Models\BasicListeningSession;
use Illuminate\Http\Request;

class BasicListeningQuizFibController extends Controller
{
    // START ATTEMPT (fallback—jarang dipakai jika lewat Connect)
    public function start(Request $request, $quizId)
    {
        $quiz = BasicListeningQuiz::findOrFail($quizId);

        // Jika ada session_id kiriman, pakai durasi session
        $session = null;
        if ($request->filled('session_id')) {
            $session = BasicListeningSession::find($request->input('session_id'));
        }
        $durationSeconds = $session
            ? max(60, (int)($session->duration_minutes ?? 10) * 60)
            : (int)($quiz->duration_seconds ?? 600);

        $attempt = BasicListeningAttempt::firstOrCreate(
            [
                'user_id'      => $request->user()->id,
                'quiz_id'      => $quiz->id,
                'session_id'   => $session?->id,
                'submitted_at' => null,
            ],
            [
                'started_at'   => now(),
                'expires_at'   => now()->addSeconds($durationSeconds),
            ]
        );

        // Koreksi jika attempt sudah ada tapi durasinya berbeda
        if ($attempt->expires_at && $attempt->started_at) {
            $span = $attempt->expires_at->diffInSeconds($attempt->started_at);
            if (abs($span - $durationSeconds) > 3) {
                $attempt->forceFill([
                    'expires_at' => $attempt->started_at->copy()->addSeconds($durationSeconds),
                ])->save();
            }
        }

        return redirect()->route('bl.quiz', $quiz->id);
    }

    // SHOW QUIZ (1 soal FIB)
    public function show(Request $request, $quizId)
    {
        $quiz = BasicListeningQuiz::findOrFail($quizId);

        $attempt = BasicListeningAttempt::where('user_id', $request->user()->id)
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->first();

        if (!$attempt) {
            return redirect()->route('bl.start', $quiz->id);
        }

        $remaining = max(0, now()->diffInSeconds($attempt->expires_at, false));
        if ($remaining <= 0) {
            $attempt->update(['submitted_at' => now(), 'score' => $attempt->score ?? 0]);
            return redirect()->route('bl.history.show', $attempt->id)->with('warning', 'Time is up.');
        }

        $question = BasicListeningQuestion::where('quiz_id', $quiz->id)
            ->where('type', 'fib_paragraph')
            ->firstOrFail();

        // 🆕 DEBUG DENGAN KOLOM YANG BENAR
        \Log::info('FIB QUESTION DEBUG CORRECTED', [
            'question_id' => $question->id,
            'type' => $question->type,
            'paragraph_text' => $question->paragraph_text, // ← INI YANG BENAR
            'paragraph_text_length' => strlen($question->paragraph_text ?? ''),
            'has_blank_tags' => str_contains($question->paragraph_text ?? '', '[blank]'),
            'blank_count' => substr_count($question->paragraph_text ?? '', '[blank]'),
            'audio_url' => $question->audio_url,
        ]);

        // 🆕 PROSES PARAGRAPH DENGAN KOLOM YANG BENAR
        $processedParagraph = $this->processParagraph($question->paragraph_text, $question->id, $attempt);

        // 🆕 HITUNG JUMLAH BLANK DENGAN KOLOM YANG BENAR
        $blankCount = $this->countBlanks($question->paragraph_text);

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

    /**
     * 🆕 PROCESS PARAGRAPH - SUPPORT BOTH FORMATS DAN PRESERVE LINE BREAKS
     */
    private function processParagraph($paragraph, $questionId, $attempt)
    {
        // Jika paragraph null, beri fallback
        if (empty($paragraph)) {
            \Log::warning('Paragraph is empty, using fallback');
            $paragraph = "Please listen to the audio and fill in the missing words.\n\nThe weather today is [[1]]. I can hear [[2]] outside. The birds are [[3]].";
        }

        // 🆕 CONVERT LINE BREAKS TO HTML <br> TAGS
        $paragraph = nl2br($paragraph);

        // Ambil jawaban yang sudah disimpan sebelumnya
        $existingAnswers = [];
        $savedAnswers = $attempt->answers()
            ->where('question_id', $questionId)
            ->get();
        
        foreach ($savedAnswers as $answer) {
            $existingAnswers[$answer->blank_index] = $answer->answer;
        }

        $index = 0;
        $blankMap = []; // Map dari [[number]] ke sequential index
        
        // 🆕 SUPPORT BOTH FORMATS: [blank] DAN [[number]]
        $processed = preg_replace_callback(
            '/\[\[(\d+)\]\]|\[blank\]/',
            function($matches) use (&$index, $existingAnswers, &$blankMap) {
                if (isset($matches[1])) {
                    // Format [[number]] - simpan mapping
                    $blankNumber = $matches[1];
                    $blankMap[$blankNumber] = $index;
                    $inputIndex = $index;
                } else {
                    // Format [blank]
                    $inputIndex = $index;
                }
                
                $value = $existingAnswers[$inputIndex] ?? '';
                $input = '<input type="text" class="fib-input" name="answers[' . $inputIndex . ']" value="' . e($value) . '" placeholder="..." style="border: 2px solid #3b82f6; padding: 8px; margin: 0 4px; border-radius: 6px; min-width: 120px;">';
                $index++;
                return $input;
            },
            $paragraph
        );

        // 🆕 SIMPAN MAPPING KE SESSION UNTUK DIGUNAKAN SAAT PENILAIAN
        if (!empty($blankMap)) {
            session(['fib_blank_map_' . $questionId => $blankMap]);
        }

        \Log::info('Paragraph processing with line breaks', [
            'blank_map' => $blankMap,
            'blanks_created' => $index,
            'paragraph_preview' => substr($paragraph, 0, 100) . '...'
        ]);
        
        return $processed;
    }

    /**
     * 🆕 HITUNG JUMLAH BLANK - SUPPORT BOTH FORMATS
     */
    private function countBlanks($paragraph)
    {
        if (empty($paragraph)) {
            return 3; // Fallback default
        }
        
        // Hitung [[number]] dan [blank]
        preg_match_all('/\[\[(\d+)\]\]|\[blank\]/', $paragraph, $matches);
        $count = count($matches[0]);
        
        return $count > 0 ? $count : 3;
    }

    /**
     * 🆕 HANDLE JAWABAN FIB (SAVE & CONTINUE) - FIXED NULL ISSUE
     */
    public function answer(Request $request, $attemptId)
    {
        $attempt = BasicListeningAttempt::where('user_id', $request->user()->id)
            ->where('id', $attemptId)
            ->whereNull('submitted_at')
            ->firstOrFail();

        // ⏳ Cek waktu
        if (now()->greaterThan($attempt->expires_at)) {
            return redirect()
                ->route('bl.history.show', $attempt->id)
                ->with('warning', 'Time is up. Your answers were not saved.');
        }

        $question = BasicListeningQuestion::where('quiz_id', $attempt->quiz_id)
            ->where('type', 'fib_paragraph')
            ->firstOrFail();

        $questionId = $question->id;
        $userAnswers = (array) $request->input('answers', []);

        // 🆕 HANYA SIMPAN JAWABAN YANG TIDAK KOSONG
        foreach ($userAnswers as $index => $answer) {
            // Skip jika answer kosong atau null
            if (empty(trim($answer ?? ''))) {
                continue;
            }
            
            $attempt->answers()->updateOrCreate(
                [
                    'question_id' => $questionId,
                    'blank_index' => (string) $index
                ],
                [
                    'answer' => $answer
                ]
            );
        }

        return redirect()->route('bl.quiz', $attempt->quiz_id)
            ->with('success', 'Jawaban berhasil disimpan.');
    }

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
        if (is_array($key) && array_key_exists('regex',$key)) {
            return @preg_match('/'.$key['regex'].'/ui', $userInput) === 1;
        }
        $userN = $this->normalize($userInput, $scoring);
        $keys  = is_array($key) ? $key : [$key];
        foreach ($keys as $k) {
            if (is_array($k) && array_key_exists('regex',$k)) {
                if (@preg_match('/'.$k['regex'].'/ui', $userInput) === 1) return true;
                continue;
            }
            $kN = $this->normalize((string)$k, $scoring);
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

        // ⏳ Guard waktu server-side
        if (now()->greaterThan($attempt->expires_at)) {
            $attempt->update(['submitted_at' => now()]);
            return redirect()
                ->route('bl.history.show', $attempt->id)
                ->with('warning', 'Time is up. Your answers were not accepted.');
        }

        // Ambil 1 soal FIB
        $q = BasicListeningQuestion::where('quiz_id', $quiz->id)
            ->where('type', 'fib_paragraph')
            ->firstOrFail();

        $scoring      = $q->fib_scoring ?? [
            'mode'              => 'exact',
            'case_sensitive'    => false,
            'allow_trim'        => true,
            'strip_punctuation' => true,
        ];
        $weights      = $q->fib_weights ?? [];
        $placeholders = $q->fib_placeholders ?? [];
        $keys         = $q->fib_answer_key ?? [];

        // 🆕 AMBIL USER ANSWERS DENGAN MAPPING
        $userFib = (array) $request->input("answers", []);
        
        // 🆕 FILTER OUT EMPTY ANSWERS
        $userFib = array_filter($userFib, function($answer) {
            return !empty(trim($answer ?? ''));
        });
        
        // 🆕 APPLY MAPPING JIKA ADA
        $blankMap = session('fib_blank_map_' . $q->id, []);
        $mappedUserFib = [];
        
        if (!empty($blankMap)) {
            // Mapping dari sequential index ke blank number
            foreach ($blankMap as $blankNumber => $sequentialIndex) {
                if (isset($userFib[$sequentialIndex])) {
                    $mappedUserFib[$blankNumber] = $userFib[$sequentialIndex];
                }
            }
            \Log::info('Applied blank mapping', [
                'original_answers' => $userFib,
                'mapped_answers' => $mappedUserFib,
                'blank_map' => $blankMap
            ]);
        } else {
            // Jika tidak ada mapping, gunakan langsung
            $mappedUserFib = $userFib;
        }

        // ✅ WAJIB LENGKAP: semua blank harus terisi (setelah trim)
        $missing = [];
        $expectedBlanks = !empty($blankMap) ? array_keys($blankMap) : array_keys($keys);
        
        foreach ($expectedBlanks as $blankNumber) {
            $val = (string)($mappedUserFib[$blankNumber] ?? '');
            if (trim($val) === '') {
                $missing[] = $blankNumber;
            }
        }
        
        if (!empty($missing)) {
            return back()
                ->withErrors(['answers' => 'Lengkapi semua isian terlebih dahulu.'])
                ->withInput();
        }

        // 🔢 Penilaian dengan mapping
        $qScore  = 0.0;
        $qWeight = 0.0;

        foreach ($expectedBlanks as $blankNumber) {
            $w = (float) ($weights[$blankNumber] ?? 1);
            $qWeight += $w;

            $userVal = (string) ($mappedUserFib[$blankNumber] ?? '');
            $key     = $keys[$blankNumber] ?? null;
            $correct = $key ? $this->matchAnswer($userVal, $key, $scoring) : false;

            // Simpan dengan blank_index yang sesuai
            $blankIndex = !empty($blankMap) ? array_search($blankNumber, $blankMap) : $blankNumber;
            
            $attempt->answers()->updateOrCreate(
                ['question_id' => $q->id, 'blank_index' => (string) $blankIndex],
                ['answer' => $userVal, 'is_correct' => $correct]
            );

            if ($correct) {
                $qScore += $w;
            }
        }

        // Skor 0..100 untuk 1 soal FIB
        $finalScore = $qWeight > 0 ? round(($qScore / $qWeight) * 100, 2) : 0;

        // 🆕 CLEANUP SESSION
        session()->forget('fib_blank_map_' . $q->id);

        $attempt->update([
            'score'        => $finalScore,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('bl.history.show', $attempt->id)
            ->with('success', 'Your answers have been submitted.');
    }
}