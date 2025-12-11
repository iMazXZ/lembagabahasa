<?php

namespace App\Http\Controllers;

use App\Models\ToeflAttempt;
use App\Models\ToeflConnectCode;
use App\Models\ToeflExam;
use Illuminate\Http\Request;

class ToeflConnectController extends Controller
{
    /**
     * Verify connect code and create attempt.
     */
    public function verify(Request $request, ToeflExam $exam)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ], [
            'code.required' => 'Silakan masukkan Kode Akses.',
        ]);

        $plain = trim((string) $request->input('code'));
        $hash = hash('sha256', $plain);
        $now = now();

        // Find matching connect code
        $codes = ToeflConnectCode::where('exam_id', $exam->id)->get();
        $connect = $codes->first(fn($c) => hash_equals($c->code_hash, $hash));

        if (!$connect) {
            return back()
                ->withErrors(['code' => 'Kode salah atau tidak ditemukan.'])
                ->withInput();
        }

        // Validate time window
        if (!$connect->withinWindow()) {
            return back()
                ->withErrors(['code' => 'Kode sudah kedaluwarsa atau belum aktif.'])
                ->withInput();
        }

        // Check max uses
        if ($connect->hasReachedLimit()) {
            return back()
                ->withErrors(['code' => 'Kode sudah mencapai batas pemakaian.'])
                ->withInput();
        }

        $user = $request->user();

        // Check if user already has an attempt
        $existingAttempt = ToeflAttempt::where('user_id', $user->id)
            ->where('exam_id', $exam->id)
            ->first();

        if ($existingAttempt) {
            if ($existingAttempt->isSubmitted()) {
                return redirect()
                    ->route('toefl.result', $existingAttempt)
                    ->with('info', 'Anda sudah menyelesaikan ujian ini.');
            }
            // Resume existing attempt
            return redirect()->route('toefl.quiz', $existingAttempt);
        }

        // Create new attempt
        $attempt = ToeflAttempt::create([
            'user_id' => $user->id,
            'exam_id' => $exam->id,
            'connect_code_id' => $connect->id,
        ]);

        return redirect()->route('toefl.quiz', $attempt);
    }
}
