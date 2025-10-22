<?php

namespace App\Http\Controllers;

use App\Models\BasicListeningAttempt;
use Illuminate\Http\Request;

class BasicListeningHistoryController extends Controller
{
    public function index(Request $request)
    {
        $attempts = BasicListeningAttempt::with('session')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return view('bl.history', compact('attempts'));
    }

    public function show(BasicListeningAttempt $attempt, Request $request)
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
        $attempt->load(['session', 'quiz.questions', 'answers']);
        $questions = $attempt->quiz->questions()->orderBy('order')->get();
        $answers   = $attempt->answers()->get(); 

        return view('bl.history-show', [
            'attempt'   => $attempt,
            'questions' => $attempt->quiz->questions,
            'answers'   => $answers,
        ]);
    }
}
