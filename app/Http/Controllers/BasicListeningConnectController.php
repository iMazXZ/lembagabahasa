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

        $now = now();

        // === Ambil SEMUA kode untuk sesi ini (termasuk yang sudah kedaluwarsa/nonaktif) ===
        $codes = BasicListeningConnectCode::query()
            ->where('session_id', $session->id)
            ->get();

        /** @var \App\Models\BasicListeningConnectCode|null $connect */
        $connect = $codes->first(fn ($c) => hash_equals($c->code_hash, $hash));

        if (! $connect) {
            // Tidak ada kode dengan hash ini sama sekali
            return back()
                ->withErrors(['code' => 'Kode salah atau kedaluwarsa.'])
                ->withInput();
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

        // Tentukan quiz yang akan dibuka (dipakai juga untuk cek attempt lama)
        $quiz = $connect->quiz
            ?? $session->quizzes()->active()->latest('id')->first();

        // Flag status "kode masih bisa dipakai untuk attempt baru"
        $isWithinWindow =
            (is_null($connect->starts_at) || $connect->starts_at <= $now) &&
            (is_null($connect->ends_at)   || $connect->ends_at   >= $now);

        $isUsable = $connect->is_active && $isWithinWindow;

        // === Kalau kode SUDAH TIDAK USABLE (expired/nonaktif) ===
        if (! $isUsable) {
            if ($quiz) {
                // Cek apakah user sudah pernah menyelesaikan kuis ini
                $completed = BasicListeningAttempt::where('user_id', $request->user()->id)
                    ->where('session_id', $session->id)
                    ->where('quiz_id', $quiz->id)
                    ->whereNotNull('submitted_at')
                    ->latest('submitted_at')
                    ->first();

                if ($completed) {
                    // Pakai connect code lama hanya untuk melihat hasil
                    return redirect()
                        ->route('bl.history.show', $completed->id)
                        ->with('warning', 'Kodenya sudah kedaluwarsa, menampilkan hasil attempt yang sudah Anda selesaikan.');
                }
            }

            // Tidak ada attempt yang sudah selesai → tetap error
            return back()
                ->withErrors(['code' => 'Kode salah atau kedaluwarsa.'])
                ->withInput();
        }

        // Pada titik ini, kode MASIH AKTIF dan boleh dipakai buat attempt baru
        if (! $quiz || ! $quiz->is_active) {
            return back()->withErrors(['code' => 'Quiz belum tersedia.'])->withInput();
        }

        // Batas pemakaian (hanya dihitung kalau kode masih usable)
        if (! is_null($connect->max_uses)) {
            $uses = BasicListeningCodeUsage::where('connect_code_id', $connect->id)->count();
            if ($uses >= (int) $connect->max_uses) {
                return back()->withErrors(['code' => 'Kode sudah mencapai batas pemakaian.'])->withInput();
            }
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

        // Catat penggunaan code (audit) — hanya jika kode masih usable
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
