<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningSession;
use App\Models\BasicListeningAttempt;
use Illuminate\Http\Request;

class BasicListeningController extends Controller
{
    public function index()
    {
        $sessions = BasicListeningSession::orderBy('number')->get();
        return view('bl.index', compact('sessions'));
    }

    public function show(BasicListeningSession $session)
    {
        return view('bl.show', compact('session'));
    }

    public function continue(BasicListeningAttempt $attempt)
    {
        if ($attempt->submitted_at) {
            return redirect()->route('bl.history.show', $attempt)->with('error', 'Quiz sudah disubmit.');
        }

        // redirect ke halaman quiz sesuai session-nya
        return redirect()->route('bl.quiz.show', [
            'session' => $attempt->session_id,
            'attempt' => $attempt->id,
        ]);
    }
}
