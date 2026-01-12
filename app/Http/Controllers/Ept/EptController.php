<?php

namespace App\Http\Controllers\Ept;

use App\Http\Controllers\Controller;
use App\Models\EptRegistration;
use App\Models\EptSession;
use Illuminate\Http\Request;

class EptController extends Controller
{
    /**
     * Dashboard EPT - menampilkan registrasi aktif, sesi mendatang
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Registrasi EPT user yang approved
        $registration = EptRegistration::where('user_id', $user->id)
            ->where('status', 'approved')
            ->with(['session.quiz'])
            ->latest()
            ->first();
        
        // Sesi mendatang yang aktif
        $upcomingSessions = EptSession::active()
            ->upcoming()
            ->with('quiz')
            ->orderBy('date')
            ->limit(5)
            ->get();
        
        return view('ept.index', compact('registration', 'upcomingSessions'));
    }

    /**
     * Jadwal sesi EPT yang tersedia
     */
    public function schedule(Request $request)
    {
        $sessions = EptSession::active()
            ->upcoming()
            ->with(['quiz', 'registrations'])
            ->withCount('registrations')
            ->orderBy('date')
            ->paginate(10);
        
        return view('ept.schedule', compact('sessions'));
    }

    /**
     * Halaman token CBT
     */
    public function token(Request $request)
    {
        $user = $request->user();
        
        $registration = EptRegistration::where('user_id', $user->id)
            ->where('status', 'approved')
            ->with('session.quiz')
            ->latest()
            ->first();
        
        return view('ept.token', compact('registration'));
    }

    /**
     * Alat diagnosa sistem
     */
    public function diagnostic()
    {
        return view('ept.diagnostic');
    }
}
