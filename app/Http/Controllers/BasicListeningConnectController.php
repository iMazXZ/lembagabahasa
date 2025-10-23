<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BasicListeningAttempt;
use App\Models\BasicListeningCodeUsage;
use App\Models\BasicListeningConnectCode;
use App\Models\BasicListeningSession;
use App\Models\BasicListeningQuestion;
use Illuminate\Database\UniqueConstraintViolationException;

class BasicListeningConnectController extends Controller
{
    public function showForm(BasicListeningSession $session)
    {
        if (! $session->isOpen()) {
            return back()->withErrors(['session' => 'Sesi belum dibuka atau sudah ditutup.']);
        }

        return view('bl.code', compact('session'));
    }

    /**
     * Verifikasi Connect Code & arahkan ke quiz yang tepat.
     * Guardrail yang ditambahkan:
     * - Validasi prodi jika connect code dibatasi (restrict_to_prody = true).
     * - Cegah lintas-attempt aktif dalam 1 session untuk quiz berbeda.
     */
    public function verify(Request $request, BasicListeningSession $session)
    {
        if (! $session->isOpen()) {
            return back()->withErrors(['session' => 'Sesi belum dibuka atau sudah ditutup.']);
        }

        $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ], [
            'code.required' => 'Silakan masukkan Kode Akses.',
        ]);

        $plain = trim((string) $request->input('code'));
        $hash  = hash('sha256', $plain);

        // Ambil daftar code aktif untuk session ini lalu cocokkan hash
        $now = now();

        $codes = BasicListeningConnectCode::query()
            ->where('session_id', $session->id)
            ->where('is_active', true)
            ->where(function ($q) use ($now) {
                // starts_at NULL dianggap sudah aktif
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                // ends_at NULL dianggap belum kedaluwarsa
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->get();

        /** @var \App\Models\BasicListeningConnectCode|null $connect */
        $connect = $codes->first(fn ($c) => hash_equals($c->code_hash, $hash));

        if (! $connect) {
            return back()->withErrors(['code' => 'Kode salah atau kedaluwarsa.'])->withInput();
        }

        // ===== Guardrail: Validasi pembatasan prodi =====
        if ($connect->restrict_to_prody && $connect->prody_id) {
            $user = $request->user();

            if (! $user || ! $user->prody_id) {
                return back()
                    ->withErrors(['code' => 'Kode ini khusus untuk mahasiswa prodi tertentu. Silakan login dan pastikan data prodi Anda sudah diisi.'])
                    ->withInput();
            }

            if ((int) $user->prody_id !== (int) $connect->prody_id) {
                $targetProdi = $connect->prody?->name ?? 'prodi tertentu';
                return back()
                    ->withErrors(['code' => "Kode ini hanya untuk mahasiswa {$targetProdi}."])
                    ->withInput();
            }
        }
        // ================================================

        // Batas pemakaian
        if (! is_null($connect->max_uses)) {
            $uses = BasicListeningCodeUsage::where('connect_code_id', $connect->id)->count();
            if ($uses >= (int) $connect->max_uses) {
                return back()->withErrors(['code' => 'Kode sudah mencapai batas pemakaian.'])->withInput();
            }
        }

        // Tentukan quiz yang akan dibuka
        $quiz = $connect->quiz
            ?? $session->quizzes()->active()->latest('id')->first();

        if (! $quiz || ! $quiz->is_active) {
            return back()->withErrors(['code' => 'Quiz belum tersedia.'])->withInput();
        }

        // Deteksi tipe: FIB atau MC
        $isFib = BasicListeningQuestion::where('quiz_id', $quiz->id)
            ->where('type', 'fib_paragraph')
            ->exists();

        // Cek attempt aktif untuk quiz yang sama
        $existingAttempt = BasicListeningAttempt::where('user_id', $request->user()->id)
            ->where('session_id', $session->id)
            ->where('quiz_id', $quiz->id)
            ->whereNull('submitted_at')
            ->first();

        if ($existingAttempt) {
            return $this->redirectToCorrectQuizType($existingAttempt, $isFib, $quiz->id);
        }

        // Cek attempt aktif untuk quiz lain dalam session yang sama
        $otherActiveAttempt = BasicListeningAttempt::where('user_id', $request->user()->id)
            ->where('session_id', $session->id)
            ->where('quiz_id', '!=', $quiz->id)
            ->whereNull('submitted_at')
            ->first();

        if ($otherActiveAttempt) {
            return back()
                ->withErrors(['code' => 'Anda masih memiliki attempt aktif untuk quiz lain dalam sesi ini. Silakan selesaikan attempt tersebut terlebih dahulu.'])
                ->withInput();
        }

        // Catat penggunaan code (audit)
        BasicListeningCodeUsage::create([
            'connect_code_id' => $connect->id,
            'user_id'         => $request->user()->id,
            'used_at'         => $now,
            'ip'              => $request->ip(),
            'ua'              => substr((string) $request->userAgent(), 0, 255),
        ]);

        // Buat (atau ambil) attempt lalu redirect sesuai tipe quiz
        try {
            if ($isFib) {
                // Alur FIB: gunakan durasi dari session (fallback 10 menit, min 60 detik)
                $durationSeconds = max(60, (int) ($session->duration_minutes ?? 10) * 60);

                $attempt = BasicListeningAttempt::firstOrCreate(
                    [
                        'user_id'    => $request->user()->id,
                        'session_id' => $session->id,
                        'quiz_id'    => $quiz->id,
                    ],
                    [
                        'connect_code_id' => $connect->id,
                        'started_at'      => now(),
                        'expires_at'      => now()->addSeconds($durationSeconds),
                        'submitted_at'    => null,
                    ]
                );

                return redirect()->route('bl.quiz', $quiz->id); // route FIB
            }

            // Alur MC
            $attempt = BasicListeningAttempt::firstOrCreate(
                [
                    'user_id'    => $request->user()->id,
                    'session_id' => $session->id,
                    'quiz_id'    => $quiz->id,
                ],
                [
                    'connect_code_id' => $connect->id,
                    'started_at'      => now(),
                    'submitted_at'    => null,
                ]
            );

            return redirect()->route('bl.quiz.show', $attempt); // route MC
        } catch (UniqueConstraintViolationException $e) {
            // Fallback: kalau race condition, ambil attempt yang sudah ada
            $existingAttempt = BasicListeningAttempt::where('user_id', $request->user()->id)
                ->where('session_id', $session->id)
                ->where('quiz_id', $quiz->id)
                ->first();

            if ($existingAttempt) {
                return $this->redirectToCorrectQuizType($existingAttempt, $isFib, $quiz->id);
            }

            return back()
                ->withErrors(['code' => 'Terjadi kesalahan sistem. Silakan coba lagi.'])
                ->withInput();
        }
    }

    /**
     * Redirect ke route yang sesuai tipe quiz.
     */
    private function redirectToCorrectQuizType(BasicListeningAttempt $attempt, bool $isFib, int $quizId)
    {
        if ($isFib) {
            return redirect()->route('bl.quiz', $quizId);      // FIB
        }

        return redirect()->route('bl.quiz.show', $attempt);    // MC
    }
}
