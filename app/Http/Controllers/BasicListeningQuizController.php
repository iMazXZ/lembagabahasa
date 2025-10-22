<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningAnswer;
use App\Models\BasicListeningAttempt;
use Illuminate\Http\Request;

class BasicListeningQuizController extends Controller
{
    /**
     * Tampilkan halaman pengerjaan quiz (Multiple Choice).
     */
    public function show(BasicListeningAttempt $attempt, Request $request)
    {
        $user = $request->user();

        // 🔒 Pastikan pemilik attempt
        if (!$user || $attempt->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke attempt ini.');
        }

        $session = $attempt->session;

        // 🚫 Sudah dikumpulkan → ke hasil
        if ($attempt->submitted_at) {
            return redirect()
                ->route('bl.history.show', $attempt->id)
                ->with('warning', 'Quiz sudah dikumpulkan. Tidak bisa dikerjakan ulang.');
        }

        // 🚫 Sesi sudah tutup
        if (!$session->isOpen()) {
            return redirect()
                ->route('bl.history')
                ->with('error', 'Waktu quiz sudah berakhir.');
        }

        // ⏱️ Inisialisasi start time bila belum ada
        if (empty($attempt->started_at)) {
            $attempt->forceFill(['started_at' => now()])->save();
        }

        // ⏳ Hitung sisa waktu berdasarkan session->duration_minutes
        $remainingSeconds = null;
        $durationMin = (int) ($session->duration_minutes ?? 0);
        if ($durationMin > 0 && $attempt->started_at) {
            $deadline = $attempt->started_at->clone()->addMinutes($durationMin);
            if (now()->greaterThanOrEqualTo($deadline)) {
                // Waktu habis → finalize
                return $this->finalize($attempt);
            }
            $remainingSeconds = now()->diffInSeconds($deadline, false);
        }

        // 📚 Ambil semua soal & posisi sekarang
        $questions = $attempt->quiz->questions()->get();
        $currentIndex = max(0, (int) $request->query('q', 0));
        $currentIndex = min($currentIndex, max(0, $questions->count() - 1));
        $question = $questions[$currentIndex] ?? abort(404);

        // ✏️ Ambil / buat jawaban untuk soal ini
        $answer = BasicListeningAnswer::firstOrNew([
            'attempt_id'  => $attempt->id,
            'question_id' => $question->id,
        ]);

        $answeredIds = $attempt->answers()
            ->whereNotNull('answer')
            ->pluck('question_id')
            ->all();

        // 🆕 Hitung jumlah soal yang belum terjawab
        $unansweredCount = $questions->count() - count($answeredIds);
        $isAllAnswered = $unansweredCount === 0;

        // 🧭 Tampilkan halaman quiz
        return view('bl.quiz', compact(
            'attempt',
            'question',
            'currentIndex',
            'questions',
            'answer',
            'remainingSeconds',
            'answeredIds',
            'unansweredCount',
            'isAllAnswered'
        ));
    }

    /**
     * Simpan jawaban satu soal (Multiple Choice).
     */
    public function answer(BasicListeningAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);

        // ⏳ Cek batas waktu by session
        $session = $attempt->session;
        $durationMin = (int) ($session->duration_minutes ?? 0);
        if ($durationMin > 0 && $attempt->started_at) {
            $deadline = $attempt->started_at->clone()->addMinutes($durationMin);
            if (now()->greaterThanOrEqualTo($deadline)) {
                return $this->finalize($attempt);
            }
        }

        // ✅ Validasi input
        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'answer'      => ['nullable', 'in:A,B,C,D'],
            'q'           => ['nullable', 'integer'], // posisi sekarang
        ]);

        // 💾 Simpan jawaban
        $ans = BasicListeningAnswer::firstOrNew([
            'attempt_id'  => $attempt->id,
            'question_id' => (int) $data['question_id'],
        ]);
        $ans->answer = $data['answer'] ?? null;
        $ans->is_correct = ($data['answer'] ?? null) === $ans->question?->correct;
        $ans->save();

        // 🔢 Index berikutnya
        $currentIndex = max(0, (int) ($data['q'] ?? 0));
        $total = $attempt->quiz->questions()->count();
        $nextIndex = min($currentIndex + 1, max(0, $total - 1));

        // 🚀 Ke soal berikutnya
        return redirect()->route('bl.quiz.show', [
            'attempt' => $attempt->id,
            'q'       => $nextIndex,
        ]);
    }

    /**
     * Submit seluruh quiz (tombol "Kumpulkan Jawaban").
     */
    public function submit(BasicListeningAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);
        
        // 🆕 Validasi: Cek apakah semua soal sudah terjawab
        $questions = $attempt->quiz->questions()->get();
        $answeredCount = $attempt->answers()
            ->whereNotNull('answer')
            ->count();
        $unansweredCount = $questions->count() - $answeredCount;
        
        // Jika ada soal yang belum terjawab, tampilkan warning
        if ($unansweredCount > 0) {
            return redirect()->back()
                ->with('warning', "Masih ada <strong>{$unansweredCount} soal</strong> yang belum terjawab. Yakin ingin mengumpulkan?")
                ->with('showSubmitConfirm', true);
        }
        
        return $this->finalize($attempt);
    }

    /**
     * 🆕 Method baru untuk force submit (setelah konfirmasi)
     */
    public function forceSubmit(BasicListeningAttempt $attempt, Request $request)
    {
        $this->authorizeAttempt($attempt, $request);
        return $this->finalize($attempt);
    }

    /**
     * Hitung skor & tutup attempt.
     */
    protected function finalize(BasicListeningAttempt $attempt)
    {
        if ($attempt->submitted_at) {
            return redirect()
                ->route('bl.history.show', $attempt->id)
                ->with('status', 'Kuis sudah disubmit.');
        }

        $questions = $attempt->quiz->questions()->get();
        $answers = $attempt->answers()->get()->keyBy('question_id');

        $correct = 0;
        foreach ($questions as $q) {
            $ans = $answers->get($q->id);
            if (!$ans) continue;
            $ans->is_correct = $ans->answer === $q->correct;
            $ans->save();
            if ($ans->is_correct) $correct++;
        }

        $score = $questions->count()
            ? (int) round(($correct / $questions->count()) * 100)
            : 0;

        $attempt->update([
            'score'        => $score,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('bl.history.show', $attempt->id)
            ->with('success', "Submit selesai. Skor kamu: {$score}");
    }

    /**
     * Otorisasi kepemilikan attempt.
     */
    protected function authorizeAttempt(BasicListeningAttempt $attempt, Request $request): void
    {
        abort_unless(
            $request->user() && $attempt->user_id === $request->user()->id,
            403,
            'Anda tidak memiliki akses ke attempt ini.'
        );
    }
}