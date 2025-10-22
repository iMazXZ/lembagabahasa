<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningCodeUsage;
use App\Models\BasicListeningConnectCode;
use App\Models\BasicListeningSession;
use App\Models\BasicListeningQuestion;

class BasicListeningConnectController extends Controller
{
    public function showForm(BasicListeningSession $session)
    {
        if (!$session->isOpen()) {
            return back()->withErrors(['session' => 'Sesi belum dibuka atau sudah ditutup.']);
        }
        return view('bl.code', compact('session'));
    }

    public function verify(Request $request, BasicListeningSession $session)
    {
        if (!$session->isOpen()) {
            return back()->withErrors(['session' => 'Sesi belum dibuka atau sudah ditutup.']);
        }

        $request->validate(['code' => ['required', 'string', 'max:64']]);
        
        $hash = hash('sha256', trim($request->input('code')));

        $codes = BasicListeningConnectCode::where('session_id', $session->id)
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->get();

        $connect = $codes->first(fn ($c) => hash_equals($c->code_hash, $hash));
        
        if (!$connect) {
            return back()->withErrors(['code' => 'Kode salah atau kedaluwarsa.'])->withInput();
        }

        if ($connect->max_uses) {
            $uses = BasicListeningCodeUsage::where('connect_code_id', $connect->id)->count();
            if ($uses >= $connect->max_uses) {
                return back()->withErrors(['code' => 'Kode sudah mencapai batas pemakaian.'])->withInput();
            }
        }

        BasicListeningCodeUsage::create([
            'connect_code_id' => $connect->id,
            'user_id'         => $request->user()->id,
            'used_at'         => now(),
            'ip'              => $request->ip(),
            'ua'              => substr((string) $request->userAgent(), 0, 255),
        ]);

        // Ambil quiz yang dituju
        $quiz = $connect->quiz
            ?? $session->quizzes()->active()->latest('id')->first();
            
        if (!$quiz || !$quiz->is_active) {
            return back()->withErrors(['code' => 'Quiz belum tersedia.'])->withInput();
        }

        // 🆕 DETEKSI TIPE QUIZ: FIB atau MC
        $isFib = BasicListeningQuestion::where('quiz_id', $quiz->id)
            ->where('type', 'fib_paragraph')
            ->exists();

        // 🆕 CEK ATTEMPT YANG SUDAH ADA - DENGAN QUIZ YANG SAMA
        $existingAttempt = BasicListeningAttempt::where('user_id', $request->user()->id)
            ->where('session_id', $session->id)
            ->where('quiz_id', $quiz->id) // 🆕 PASTIKAN QUIZ ID SAMA
            ->whereNull('submitted_at')
            ->first();

        // 🆕 JIKA ADA ATTEMPT AKTIF UNTUK QUIZ INI, LANGSUNG REDIRECT
        if ($existingAttempt) {
            return $this->redirectToCorrectQuizType($existingAttempt, $isFib, $quiz);
        }

        // 🆕 CEK APAKAH ADA ATTEMPT AKTIF UNTUK QUIZ LAIN DI SESSION YANG SAMA
        $otherActiveAttempt = BasicListeningAttempt::where('user_id', $request->user()->id)
            ->where('session_id', $session->id)
            ->where('quiz_id', '!=', $quiz->id) // 🆕 QUIZ BERBEDA
            ->whereNull('submitted_at')
            ->first();

        if ($otherActiveAttempt) {
            return back()
                ->withErrors(['code' => 'Anda masih memiliki attempt aktif untuk quiz lain dalam sesi ini. Silakan selesaikan attempt tersebut terlebih dahulu.'])
                ->withInput();
        }

        try {
            if ($isFib) {
                // ==== ALUR FIB ====
                $durationSeconds = max(60, (int)($session->duration_minutes ?? 10) * 60);

                $attempt = BasicListeningAttempt::firstOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'session_id' => $session->id,
                        'quiz_id' => $quiz->id,
                    ],
                    [
                        'connect_code_id' => $connect->id ?? null,
                        'started_at' => now(),
                        'expires_at' => now()->addSeconds($durationSeconds),
                        'submitted_at' => null,
                    ]
                );

                // 🆕 REDIRECT KE ROUTE FIB
                return redirect()->route('bl.quiz', $quiz->id);
            }

            // ==== ALUR MC ====
            $attempt = BasicListeningAttempt::firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'session_id' => $session->id,
                    'quiz_id' => $quiz->id,
                ],
                [
                    'connect_code_id' => $connect->id ?? null,
                    'started_at' => now(),
                    'submitted_at' => null,
                ]
            );

            return redirect()->route('bl.quiz.show', $attempt);

        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Fallback: cari attempt yang sudah ada
            $existingAttempt = BasicListeningAttempt::where('user_id', $request->user()->id)
                ->where('session_id', $session->id)
                ->where('quiz_id', $quiz->id)
                ->first();

            if ($existingAttempt) {
                return $this->redirectToCorrectQuizType($existingAttempt, $isFib, $quiz);
            }

            return back()
                ->withErrors(['code' => 'Terjadi kesalahan sistem. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * 🆕 METHOD BARU: Redirect ke tipe quiz yang benar
     */
    private function redirectToCorrectQuizType($attempt, $isFib, $quiz)
    {
        if ($isFib) {
            // Redirect ke route FIB
            return redirect()->route('bl.quiz', $quiz->id);
        } else {
            // Redirect ke route MC
            return redirect()->route('bl.quiz.show', $attempt);
        }
    }
}