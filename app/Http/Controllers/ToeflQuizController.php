<?php

namespace App\Http\Controllers;

use App\Models\ToeflAnswer;
use App\Models\ToeflAttempt;
use App\Models\ToeflQuestion;
use App\Services\ToeflScoringService;
use Illuminate\Http\Request;

class ToeflQuizController extends Controller
{
    /**
     * Show the current section of the exam.
     */
    public function show(ToeflAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        if ($attempt->isSubmitted()) {
            return redirect()->route('toefl.result', $attempt);
        }

        $section = $attempt->currentSection();

        if (!$section) {
            // All sections completed, finalize
            return $this->finalize($attempt);
        }

        $package = $attempt->exam->package;

        // Check if section started - if not, show intro page
        $startedAtField = $section . '_started_at';
        if (!$attempt->$startedAtField) {
            $questionCount = $package->questions()->where('section', $section)->count();
            $duration = $attempt->getSectionDuration($section);

            return view('toefl.exam.section-intro', [
                'attempt' => $attempt,
                'section' => $section,
                'questionCount' => $questionCount,
                'duration' => $duration,
            ]);
        }

        // Check if section expired
        if ($attempt->isSectionExpired($section)) {
            return $this->endSection($attempt, $section);
        }

        $questions = $package->questions()
            ->where('section', $section)
            ->orderBy('question_number')
            ->get();

        // Get existing answers
        $answers = $attempt->answers()
            ->whereIn('question_id', $questions->pluck('id'))
            ->pluck('answer', 'question_id')
            ->toArray();

        $expiresAt = $attempt->getSectionExpiresAt($section);
        $remainingSeconds = max(0, now()->diffInSeconds($expiresAt, false));

        return view('toefl.exam.' . $section, [
            'attempt' => $attempt,
            'section' => $section,
            'questions' => $questions,
            'answers' => $answers,
            'expiresAt' => $expiresAt,
            'remainingSeconds' => $remainingSeconds,
            'package' => $package,
        ]);
    }

    /**
     * Start a section (timer begins now).
     */
    public function startSection(ToeflAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        $section = $request->input('section');
        $currentSection = $attempt->currentSection();

        // Validate section matches current section
        if ($section !== $currentSection) {
            return redirect()->route('toefl.quiz', $attempt);
        }

        // Start the timer
        $startedAtField = $section . '_started_at';
        if (!$attempt->$startedAtField) {
            $attempt->update([$startedAtField => now()]);
        }

        return redirect()->route('toefl.quiz', $attempt);
    }

    /**
     * Save answer (AJAX auto-save).
     */
    public function saveAnswer(ToeflAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        if ($attempt->isSubmitted()) {
            return response()->json(['error' => 'Ujian sudah selesai'], 400);
        }

        $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'nullable|string|size:1|in:A,B,C,D,a,b,c,d',
        ]);

        $questionId = $request->input('question_id');
        $answer = strtoupper($request->input('answer'));

        // Verify question belongs to this exam's package
        $question = ToeflQuestion::find($questionId);
        if (!$question || $question->package_id !== $attempt->exam->package_id) {
            return response()->json(['error' => 'Soal tidak valid'], 400);
        }

        ToeflAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $questionId,
            ],
            [
                'answer' => $answer ?: null,
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Submit current section and move to next.
     */
    public function submitSection(ToeflAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        $section = $attempt->currentSection();

        if (!$section) {
            return redirect()->route('toefl.result', $attempt);
        }

        return $this->endSection($attempt, $section);
    }

    /**
     * End a section and move to next.
     */
    protected function endSection(ToeflAttempt $attempt, string $section)
    {
        $endedAtField = $section . '_ended_at';
        $attempt->update([$endedAtField => now()]);

        $nextSection = $attempt->currentSection();

        if (!$nextSection) {
            return $this->finalize($attempt);
        }

        return redirect()->route('toefl.quiz', $attempt);
    }

    /**
     * Force submit (timeout).
     */
    public function forceSubmit(ToeflAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        $section = $attempt->currentSection();
        if ($section) {
            return $this->endSection($attempt, $section);
        }

        return $this->finalize($attempt);
    }

    /**
     * Ping/heartbeat for timer sync.
     */
    public function ping(ToeflAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        $section = $attempt->currentSection();

        if (!$section || $attempt->isSubmitted()) {
            return response()->json([
                'expired' => true,
                'redirect' => route('toefl.result', $attempt),
            ]);
        }

        if ($attempt->isSectionExpired($section)) {
            $this->endSection($attempt, $section);
            $nextSection = $attempt->fresh()->currentSection();

            return response()->json([
                'expired' => true,
                'redirect' => $nextSection
                    ? route('toefl.quiz', $attempt)
                    : route('toefl.result', $attempt),
            ]);
        }

        $expiresAt = $attempt->getSectionExpiresAt($section);

        return response()->json([
            'expired' => false,
            'remaining' => max(0, now()->diffInSeconds($expiresAt, false)),
        ]);
    }

    /**
     * Finalize the exam and calculate scores.
     */
    protected function finalize(ToeflAttempt $attempt)
    {
        if ($attempt->isSubmitted()) {
            return redirect()->route('toefl.result', $attempt);
        }

        // Calculate scores using scoring service
        $scorer = new ToeflScoringService();
        $scores = $scorer->calculate($attempt);

        $attempt->update([
            'submitted_at' => now(),
            'listening_correct' => $scores['listening_correct'],
            'structure_correct' => $scores['structure_correct'],
            'reading_correct' => $scores['reading_correct'],
            'listening_score' => $scores['listening_score'],
            'structure_score' => $scores['structure_score'],
            'reading_score' => $scores['reading_score'],
            'total_score' => $scores['total_score'],
        ]);

        return redirect()->route('toefl.result', $attempt);
    }

    /**
     * Show result page.
     */
    public function result(ToeflAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        if (!$attempt->isSubmitted()) {
            return redirect()->route('toefl.quiz', $attempt);
        }

        return view('toefl.exam.result', compact('attempt'));
    }

    /**
     * Authorize that the user owns this attempt.
     */
    protected function authorizeAttempt(ToeflAttempt $attempt, Request $request): void
    {
        if ($attempt->user_id !== $request->user()->id) {
            abort(403, 'Akses ditolak.');
        }
    }
}
