<?php

namespace App\Http\Controllers\Ept;

use App\Http\Controllers\Controller;
use App\Models\EptAttempt;
use App\Support\ToeflScoring;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EptHistoryController extends Controller
{
    /**
     * Daftar riwayat ujian user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $attempts = EptAttempt::where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->with(['quiz', 'session'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);
        
        return view('ept.history.index', compact('attempts'));
    }

    /**
     * Detail hasil ujian
     */
    public function show(EptAttempt $attempt, Request $request)
    {
        if ($attempt->user_id !== $request->user()->id) {
            abort(403);
        }
        
        if (!$attempt->submitted_at) {
            return redirect()->route('ept.quiz.show', $attempt);
        }
        
        $interpretation = ToeflScoring::getInterpretation($attempt->total_score);
        
        // Statistik jawaban
        $stats = [
            'listening' => [
                'correct' => $attempt->score_listening,
                'total' => $attempt->quiz->listening_count,
                'scaled' => $attempt->scaled_listening,
            ],
            'structure' => [
                'correct' => $attempt->score_structure,
                'total' => $attempt->quiz->structure_count,
                'scaled' => $attempt->scaled_structure,
            ],
            'reading' => [
                'correct' => $attempt->score_reading,
                'total' => $attempt->quiz->reading_count,
                'scaled' => $attempt->scaled_reading,
            ],
        ];
        
        return view('ept.history.show', compact('attempt', 'interpretation', 'stats'));
    }

    /**
     * Download sertifikat PDF
     */
    public function certificate(EptAttempt $attempt, Request $request)
    {
        if ($attempt->user_id !== $request->user()->id) {
            abort(403);
        }
        
        if (!$attempt->submitted_at) {
            abort(404, 'Ujian belum selesai.');
        }
        
        // Cek apakah sertifikat sudah di-approve (jika ada mekanisme approval)
        // Untuk sementara, langsung generate jika score >= 400
        if ($attempt->total_score < 400) {
            return back()->with('error', 'Skor minimum untuk sertifikat adalah 400.');
        }
        
        $user = $request->user();
        $interpretation = ToeflScoring::getInterpretation($attempt->total_score);
        
        $pdf = Pdf::loadView('ept.certificate', [
            'attempt' => $attempt,
            'user' => $user,
            'interpretation' => $interpretation,
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        $filename = 'EPT_Certificate_' . $user->srn . '_' . $attempt->id . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Preview sertifikat (inline)
     */
    public function certificatePreview(EptAttempt $attempt, Request $request)
    {
        if ($attempt->user_id !== $request->user()->id) {
            abort(403);
        }
        
        if (!$attempt->submitted_at || $attempt->total_score < 400) {
            abort(404);
        }
        
        $user = $request->user();
        $interpretation = ToeflScoring::getInterpretation($attempt->total_score);
        
        $pdf = Pdf::loadView('ept.certificate', [
            'attempt' => $attempt,
            'user' => $user,
            'interpretation' => $interpretation,
        ]);
        
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->stream('EPT_Certificate_Preview.pdf');
    }
}
