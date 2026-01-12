<?php

namespace App\Http\Controllers\Ept;

use App\Http\Controllers\Controller;
use App\Models\EptRegistration;
use App\Models\EptAttempt;
use App\Models\EptSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EptLauncherController extends Controller
{
    /**
     * Tampilkan halaman launcher CBT
     * User harus sudah punya token dan sesi aktif
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Cari registrasi yang approved dan punya token
        $registration = EptRegistration::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('cbt_token')
            ->with(['session.quiz'])
            ->latest()
            ->first();
        
        if (!$registration || !$registration->session) {
            return redirect()->route('ept.token')
                ->with('error', 'Anda belum memiliki token CBT atau sesi belum ditentukan.');
        }
        
        // Cek apakah sesi sedang berlangsung
        $session = $registration->session;
        if (!$session->isInProgress()) {
            return redirect()->route('ept.token')
                ->with('error', 'Sesi ujian belum dimulai atau sudah berakhir.');
        }
        
        // Cek apakah sudah ada attempt yang sedang berjalan
        $existingAttempt = EptAttempt::where('user_id', $user->id)
            ->where('quiz_id', $session->quiz_id)
            ->whereNull('submitted_at')
            ->first();
        
        if ($existingAttempt) {
            // Lanjutkan attempt yang ada
            return redirect()->route('ept.quiz.show', $existingAttempt);
        }
        
        return view('ept.launcher', compact('registration', 'session'));
    }

    /**
     * Verifikasi passcode dan simpan swafoto, lalu mulai ujian
     */
    public function start(Request $request)
    {
        $request->validate([
            'passcode' => 'required|string',
            'selfie' => 'required|string', // Base64 image
        ]);
        
        $user = $request->user();
        
        $registration = EptRegistration::where('user_id', $user->id)
            ->where('status', 'approved')
            ->whereNotNull('cbt_token')
            ->with(['session.quiz'])
            ->latest()
            ->first();
        
        if (!$registration || !$registration->session) {
            return response()->json([
                'success' => false,
                'message' => 'Registrasi atau sesi tidak ditemukan.'
            ], 404);
        }
        
        $session = $registration->session;
        
        // Verifikasi passcode
        if ($request->passcode !== $session->passcode) {
            return response()->json([
                'success' => false,
                'message' => 'Passcode salah. Tanyakan kepada pengawas.'
            ], 422);
        }
        
        // Simpan swafoto
        $selfieData = $request->selfie;
        $selfieData = preg_replace('/^data:image\/\w+;base64,/', '', $selfieData);
        $selfieData = base64_decode($selfieData);
        
        $filename = 'ept/selfies/' . $user->id . '_' . time() . '.jpg';
        Storage::disk('public')->put($filename, $selfieData);
        
        // Update registration dengan selfie
        $registration->update(['selfie_path' => $filename]);
        
        // Buat attempt baru
        $attempt = EptAttempt::create([
            'user_id' => $user->id,
            'registration_id' => $registration->id,
            'quiz_id' => $session->quiz_id,
            'session_id' => $session->id,
            'started_at' => now(),
            'listening_started_at' => now(),
            'current_section' => 'listening',
            'selfie_path' => $filename,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Ujian dimulai!',
            'redirect' => route('ept.quiz.show', $attempt),
        ]);
    }

    /**
     * Validasi token CBT (AJAX)
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);
        
        $user = $request->user();
        
        $registration = EptRegistration::where('user_id', $user->id)
            ->where('cbt_token', strtoupper($request->token))
            ->where('status', 'approved')
            ->with('session')
            ->first();
        
        if (!$registration) {
            return response()->json([
                'valid' => false,
                'message' => 'Token tidak valid.',
            ]);
        }
        
        if (!$registration->session) {
            return response()->json([
                'valid' => false,
                'message' => 'Sesi belum ditentukan untuk token ini.',
            ]);
        }
        
        return response()->json([
            'valid' => true,
            'session' => [
                'name' => $registration->session->name,
                'date' => $registration->session->date->translatedFormat('l, d F Y'),
                'time' => $registration->session->start_time . ' - ' . $registration->session->end_time,
            ],
        ]);
    }
}
