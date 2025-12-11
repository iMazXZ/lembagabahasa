<?php

namespace App\Http\Controllers;

use App\Models\ToeflExam;
use Illuminate\Http\Request;

class ToeflExamController extends Controller
{
    /**
     * Show the exam entry page with connect code form.
     */
    public function show(ToeflExam $exam)
    {
        if (!$exam->is_active) {
            abort(404, 'Ujian tidak ditemukan atau tidak aktif.');
        }

        return view('toefl.connect-code', compact('exam'));
    }

    /**
     * Show list of available exams.
     */
    public function index()
    {
        $exams = ToeflExam::where('is_active', true)
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at', 'desc')
            ->get();

        return view('toefl.index', compact('exams'));
    }
}
