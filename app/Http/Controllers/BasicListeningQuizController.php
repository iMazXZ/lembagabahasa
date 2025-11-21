<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningAnswer;
use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningQuestion;
use Illuminate\Http\Request;

class BasicListeningQuizController extends Controller
{
    public function show(BasicListeningAttempt $attempt, Request $request)
    {
        $user = $request->user();

        if (! $user || $attempt->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke attempt ini.');
        }

        $session = $attempt->session;

        if ($attempt->submitted_at) {
            return redirect()
                ->route('bl.history.show', $attempt->id)
                ->with('warning', 'Quiz sudah dikumpulkan.');
        }

        if (! $session->isOpen()) {
            return redirect()
                ->route('bl.history')
                ->with('error', 'Waktu quiz sudah berakhir.');
        }

        if (empty($attempt->started_at)) {
            $attempt->forceFill(['started_at' => now()])->save();
        }

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

        // NOTE: Jika di view muncul 16 kotak, berarti di DB ada 16 baris untuk quiz_id ini.
        // Cek tabel basic_listening_questions dan hapus baris yang berlebih/kosong.
        $questions = $attempt->quiz->questions()->get(); 
        $currentIndex = max(0, (int) $request->query('q', 0));
        $currentIndex = min($currentIndex, max(0, $questions->count() - 1));
        
        $question = $questions[$currentIndex] ?? abort(404);

        $answeredIds = $attempt->answers()
            ->whereNotNull('answer')
            ->pluck('question_id')
            ->all();

        $unansweredCount = $questions->count() - count($answeredIds);
        $isAllAnswered = $unansweredCount === 0;

        // --- LOGIKA TAMPILAN ---
        $processedParagraph = null;
        
        if ($question->type === 'fib_paragraph') {
            $savedAnswers = $attempt->answers()
                ->where('question_id', $question->id)
                ->get()
                ->mapWithKeys(function ($item) {
                    // Map answer berdasarkan blank_index (0, 1, 2...)
                    return [$item->blank_index => $item->answer];
                })
                ->toArray();
            
            $processedParagraph = $this->processParagraph($question->paragraph_text, $savedAnswers);
        } 
        
        $answer = BasicListeningAnswer::firstOrNew([
            'attempt_id'  => $attempt->id,
            'question_id' => $question->id,
        ]);

        return view('bl.quiz', compact(
            'attempt', 'question', 'currentIndex', 'questions', 'answer', 
            'processedParagraph', 'remainingSeconds', 'answeredIds', 'unansweredCount', 'isAllAnswered'
        ));
    }

    public function answer(BasicListeningAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        // Cek validasi waktu
        $session = $attempt->session;
        $durationMin = (int) ($session->duration_minutes ?? 0);
        if ($durationMin > 0 && $attempt->started_at) {
            $deadline = $attempt->started_at->clone()->addMinutes($durationMin);
            if (now()->greaterThanOrEqualTo($deadline)) {
                return $this->finalize($attempt);
            }
        }

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'q'           => ['nullable', 'integer'],
            'answer'      => ['nullable'], 
            'answers'     => ['nullable', 'array'],
        ]);

        $question = BasicListeningQuestion::findOrFail($data['question_id']);

        // --- SIMPAN JAWABAN ---
        if ($question->type === 'fib_paragraph') {
            $userAnswers = $request->input('answers', []);
            
            foreach ($userAnswers as $index => $val) {
                $val = (string) $val;
                
                // PERBAIKAN DI SINI: 
                // Hapus logika "if empty -> delete". 
                // Kita paksa simpan (updateOrCreate) apapun isinya agar record tetap ada di DB.
                
                BasicListeningAnswer::updateOrCreate(
                    [
                        'attempt_id'  => $attempt->id,
                        'question_id' => $question->id,
                        'blank_index' => $index 
                    ],
                    [
                        'answer'      => $val, // Simpan apa adanya (termasuk string kosong)
                        'is_correct'  => false // Reset status jadi salah dulu (atau biarkan logic penilaian nanti)
                    ]
                );
            }
        } else {
            // Logic MC/TF
            $ans = BasicListeningAnswer::firstOrNew([
                'attempt_id'  => $attempt->id,
                'question_id' => $question->id,
            ]);
            $ans->blank_index = 0;
            $ans->answer      = $data['answer'] ?? null;
            $ans->is_correct  = ($data['answer'] ?? null) === $question->correct;
            $ans->save();
        }

        // Cek Tombol Finish
        if ($request->has('finish_attempt')) {
            return $this->finalize($attempt);
        }

        $currentIndex = max(0, (int) ($data['q'] ?? 0));
        $total = $attempt->quiz->questions()->count();
        $nextIndex = min($currentIndex + 1, max(0, $total - 1));
        
        return redirect()->route('bl.quiz.show', [
            'attempt' => $attempt->id,
            'q'       => $nextIndex,
        ]);
    }

    public function submit(BasicListeningAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);
        
        // Hitung soal belum terjawab
        $questionsCount = $attempt->quiz->questions()->count();
        // Logic: hitung unique question_id yg punya jawaban
        $answeredQuestionsCount = $attempt->answers()
            ->whereNotNull('answer')
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

    public function forceSubmit(BasicListeningAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);
        return $this->finalize($attempt);
    }

    /**
     * Finalize dengan LOGIKA INDEX 0.
     */
    protected function finalize(BasicListeningAttempt $attempt)
    {
        if ($attempt->submitted_at) {
            return redirect()->route('bl.history.show', $attempt->id);
        }

        $questions = $attempt->quiz->questions()->get();
        $allAnswers = $attempt->answers()->get()->groupBy('question_id');

        $totalScore = 0;
        $totalMaxScore = 0;

        foreach ($questions as $q) {
            if ($q->type !== 'fib_paragraph') {
                // --- PENILAIAN MC / TF ---
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
                // --- PENILAIAN FIB (INDEX 0) ---
                $userAnswers = $allAnswers->get($q->id);
                
                // Keys: {"1":"Are", "2":"Is"}
                $keys = $q->fib_answer_key ?? [];
                $weights = $q->fib_weights ?? [];
                $scoring = $q->fib_scoring ?? [];

                $qScore = 0;
                $qMaxWeight = 0;

                foreach ($keys as $keyIndex => $correctKey) {
                    // $keyIndex adalah "1", "2", "3"... (string dari JSON)
                    // KITA KONVERSI KE INDEX 0-BASED: "1" -> 0, "2" -> 1
                    $zeroBasedIndex = ((int)$keyIndex) - 1;

                    $w = (float)($weights[$keyIndex] ?? 1);
                    $qMaxWeight += $w;

                    // Cari jawaban user di index 0-based tersebut
                    $uAns = $userAnswers?->where('blank_index', (string)$zeroBasedIndex)->first();
                    $uVal = $uAns ? $uAns->answer : '';

                    $isCorrect = $this->checkFibAnswer($uVal, $correctKey, $scoring);

                    if ($uAns) {
                        $uAns->is_correct = $isCorrect;
                        $uAns->save();
                    }

                    if ($isCorrect) $qScore += $w;
                }

                if ($qMaxWeight > 0) {
                    $totalScore += ($qScore / $qMaxWeight);
                }
                $totalMaxScore++;
            }
        }

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
     * Render paragraf FIB dengan INDEX SEQUENTIAL (0, 1, 2...)
     * Abaikan angka dalam kurung [[n]], hitung saja urutannya.
     */
    private function processParagraph($paragraph, array $existingAnswers = [])
    {
        if (empty($paragraph)) return '';

        $counter = 0; // Start dari 0
        
        $processed = preg_replace_callback(
            '/\[\[(\d+)\]\]|\[blank\]/',
            function ($matches) use (&$counter, $existingAnswers) {
                
                // Gunakan counter sebagai index, bukan $matches[1]
                $index = $counter++; 
                
                $value = $existingAnswers[$index] ?? '';
                
                // Style fix: display inline-block agar tidak turun
                $html  = '<input type="text" class="fib-input" '
                       . 'name="answers[' . $index . ']" '
                       . 'value="' . e($value) . '" '
                       . 'placeholder="..." '
                       . 'autocomplete="off" '
                       . 'style="display: inline-block; vertical-align: baseline; min-width: 80px; width: auto; margin: 0 2px; border-bottom: 2px solid #93c5fd; background: #eff6ff; padding: 2px 6px; border-radius: 4px; font-weight: 600; color: #1e3a8a;">';
                
                return $html;
            },
            $paragraph
        );

        return nl2br($processed);
    }

    private function checkFibAnswer($userVal, $key, $scoring)
    {
        $mode           = $scoring['mode'] ?? 'exact';
        $caseSensitive  = filter_var($scoring['case_sensitive'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $allowTrim      = filter_var($scoring['allow_trim'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $stripPunct     = filter_var($scoring['strip_punctuation'] ?? true, FILTER_VALIDATE_BOOLEAN);

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