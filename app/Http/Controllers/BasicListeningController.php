<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningSession;
use App\Models\BasicListeningAttempt;
use Illuminate\Http\Request;

class BasicListeningController extends Controller
{
    /**
     * Halaman index: daftar sesi + statistik partisipasi.
     * - attempts_count       : total attempt pada session tsb
     * - participants_count   : jumlah peserta unik (distinct user_id) pada session tsb
     */
    public function index()
    {
        $sessions = BasicListeningSession::query()
            ->withCount('attempts') // menghasilkan kolom attempts_count
            ->addSelect([
                'participants_count' => BasicListeningAttempt::query()
                    ->selectRaw('COUNT(DISTINCT user_id)')
                    ->whereColumn('basic_listening_attempts.session_id', 'basic_listening_sessions.id'),
            ])
            ->orderBy('number')
            ->get();

        return view('bl.index', compact('sessions'));
    }

    /**
     * Detail 1 session.
     */
    public function show(BasicListeningSession $session)
    {
        return view('bl.show', compact('session'));
    }

    /**
     * Lanjutkan attempt yang belum submit (alur MC lama).
     */
    public function continue(BasicListeningAttempt $attempt)
    {
        if ($attempt->submitted_at) {
            return redirect()
                ->route('bl.history.show', $attempt)
                ->with('error', 'Quiz sudah disubmit.');
        }

        // redirect ke halaman quiz sesuai attempt
        return redirect()->route('bl.quiz.show', [
            'attempt' => $attempt->id,
        ]);
    }
}
