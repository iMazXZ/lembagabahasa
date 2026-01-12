<?php

namespace App\Http\Controllers\Ept;

use App\Http\Controllers\Controller;
use App\Models\EptAttempt;
use App\Models\EptAnswer;
use App\Models\EptQuestion;
use App\Support\ToeflScoring;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EptQuizController extends Controller
{
    /**
     * Tampilkan halaman quiz
     */
    public function show(EptAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);
        
        if ($attempt->submitted_at) {
            return redirect()->route('ept.history.show', $attempt)
                ->with('info', 'Ujian sudah selesai.');
        }
        
        $quiz = $attempt->quiz;
        $section = $attempt->current_section;
        $questions = $quiz->getQuestionsBySection($section);
        
        // Load answers untuk section ini
        $answers = $attempt->answers()
            ->whereIn('question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('question_id');
        
        // Hitung remaining time
        $remainingSeconds = $attempt->getRemainingSecondsForSection();
        
        // Question index dari query string
        $questionIndex = max(0, min((int) $request->get('q', 0), $questions->count() - 1));
        $currentQuestion = $questions[$questionIndex] ?? $questions->first();
        
        return view('ept.quiz', compact(
            'attempt', 
            'quiz', 
            'section', 
            'questions', 
            'answers', 
            'currentQuestion', 
            'questionIndex',
            'remainingSeconds'
        ));
    }

    /**
     * Simpan jawaban (AJAX)
     */
    public function answer(EptAttempt $attempt, Request $request)
    {
        $isAjax = $request->wantsJson() || $request->ajax();
        
        // Cek akses
        if (!$request->user() || $attempt->user_id !== $request->user()->id) {
            return $isAjax
                ? response()->json(['error' => 'Unauthorized'], 403)
                : abort(403);
        }
        
        // Cek sudah submit
        if ($attempt->submitted_at) {
            return $isAjax
                ? response()->json(['status' => 'already_submitted', 'redirect' => route('ept.history.show', $attempt)], 409)
                : redirect()->route('ept.history.show', $attempt);
        }
        
        // Cek timeout section
        if ($attempt->getRemainingSecondsForSection() <= 0) {
            $this->autoProgressSection($attempt);
            
            if ($attempt->submitted_at) {
                return $isAjax
                    ? response()->json(['status' => 'expired', 'redirect' => route('ept.history.show', $attempt)], 408)
                    : redirect()->route('ept.history.show', $attempt);
            }
            
            return $isAjax
                ? response()->json(['status' => 'section_changed', 'redirect' => route('ept.quiz.show', $attempt)])
                : redirect()->route('ept.quiz.show', $attempt);
        }
        
        // Validasi input
        $data = $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'nullable|string|in:A,B,C,D',
        ]);
        
        $question = EptQuestion::findOrFail($data['question_id']);
        
        if ($question->quiz_id !== $attempt->quiz_id) {
            return $isAjax
                ? response()->json(['error' => 'Soal tidak cocok'], 422)
                : abort(422);
        }
        
        // Simpan/update jawaban
        EptAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'answer' => $data['answer'],
                'is_correct' => $data['answer'] === $question->correct_answer,
            ]
        );
        
        return $isAjax
            ? response()->json(['status' => 'saved'])
            : back();
    }

    /**
     * Pindah ke section berikutnya
     */
    public function nextSection(EptAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);
        
        if ($attempt->submitted_at) {
            return redirect()->route('ept.history.show', $attempt);
        }
        
        $this->progressToNextSection($attempt);
        
        if ($attempt->submitted_at) {
            return redirect()->route('ept.history.show', $attempt);
        }
        
        return redirect()->route('ept.quiz.show', $attempt);
    }

    /**
     * Submit ujian secara manual
     */
    public function submit(EptAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);
        
        if ($attempt->submitted_at) {
            return redirect()->route('ept.history.show', $attempt);
        }
        
        $this->finalize($attempt);
        
        return redirect()->route('ept.history.show', $attempt)
            ->with('success', 'Ujian berhasil dikumpulkan!');
    }

    /**
     * Ping untuk heartbeat (cek timeout)
     */
    public function ping(EptAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);
        
        if ($attempt->submitted_at) {
            return response()->json([
                'expired' => true,
                'redirect' => route('ept.history.show', $attempt),
            ]);
        }
        
        $remaining = $attempt->getRemainingSecondsForSection();
        
        if ($remaining <= 0) {
            $this->autoProgressSection($attempt);
            
            return response()->json([
                'section_changed' => !$attempt->submitted_at,
                'expired' => $attempt->submitted_at !== null,
                'redirect' => $attempt->submitted_at 
                    ? route('ept.history.show', $attempt)
                    : route('ept.quiz.show', $attempt),
            ]);
        }
        
        return response()->json([
            'ok' => true,
            'remaining' => $remaining,
        ]);
    }

    // =============================================
    // PRIVATE METHODS
    // =============================================

    private function authorizeAttempt(EptAttempt $attempt, Request $request): void
    {
        if (!$request->user() || $attempt->user_id !== $request->user()->id) {
            abort(403, 'Tidak berhak mengakses ujian ini.');
        }
    }

    private function autoProgressSection(EptAttempt $attempt): void
    {
        $this->progressToNextSection($attempt);
    }

    private function progressToNextSection(EptAttempt $attempt): void
    {
        $current = $attempt->current_section;
        $next = match ($current) {
            'listening' => 'structure',
            'structure' => 'reading',
            'reading' => null, // Selesai
            default => null,
        };
        
        if ($next) {
            $attempt->current_section = $next;
            $attempt->{$next . '_started_at'} = now();
            $attempt->save();
        } else {
            $this->finalize($attempt);
        }
    }

    private function finalize(EptAttempt $attempt): void
    {
        if ($attempt->submitted_at) {
            return;
        }
        
        $attempt->submitted_at = now();
        
        // Hitung skor per section
        $quiz = $attempt->quiz;
        
        $listeningCorrect = $attempt->answers()
            ->whereHas('question', fn($q) => $q->where('section', 'listening'))
            ->where('is_correct', true)
            ->count();
        
        $structureCorrect = $attempt->answers()
            ->whereHas('question', fn($q) => $q->where('section', 'structure'))
            ->where('is_correct', true)
            ->count();
        
        $readingCorrect = $attempt->answers()
            ->whereHas('question', fn($q) => $q->where('section', 'reading'))
            ->where('is_correct', true)
            ->count();
        
        $attempt->score_listening = $listeningCorrect;
        $attempt->score_structure = $structureCorrect;
        $attempt->score_reading = $readingCorrect;
        
        // Convert ke scaled score (TOEFL ITP)
        $attempt->scaled_listening = ToeflScoring::scaleListening($listeningCorrect, $quiz->listening_count);
        $attempt->scaled_structure = ToeflScoring::scaleStructure($structureCorrect, $quiz->structure_count);
        $attempt->scaled_reading = ToeflScoring::scaleReading($readingCorrect, $quiz->reading_count);
        
        // Total score
        $attempt->total_score = ToeflScoring::totalScore(
            $attempt->scaled_listening,
            $attempt->scaled_structure,
            $attempt->scaled_reading
        );
        
        $attempt->save();
    }
}
