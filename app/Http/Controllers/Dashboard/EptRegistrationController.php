<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EptRegistration;
use App\Support\ImageTransformer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EptRegistrationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check S2 requirement
        $isS2 = $user->prody && str_starts_with($user->prody->name ?? '', 'S2');
        if (!$isS2) {
            abort(403, 'Fitur ini hanya tersedia untuk mahasiswa S2.');
        }
        
        $registration = EptRegistration::where('user_id', $user->id)
            ->latest()
            ->first();
        
        return view('dashboard.ept-registration.index', [
            'user' => $user,
            'registration' => $registration,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $isS2 = $user->prody && str_starts_with($user->prody->name ?? '', 'S2');
        if (!$isS2) {
            abort(403, 'Fitur ini hanya tersedia untuk mahasiswa S2.');
        }
        
        $existing = EptRegistration::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
            
        if ($existing) {
            return back()->with('error', 'Anda sudah memiliki pendaftaran aktif.');
        }
        
        $request->validate([
            'bukti_pembayaran' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:8192'],
        ], [
            'bukti_pembayaran.required' => 'Bukti pembayaran wajib diunggah.',
            'bukti_pembayaran.image' => 'File harus berupa gambar.',
            'bukti_pembayaran.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'bukti_pembayaran.max' => 'Ukuran file maksimal 8MB.',
        ]);
        
        $file = $request->file('bukti_pembayaran');
        $basename = 'ept_payment_' . Str::of($user->id)->padLeft(6, '0') . '_' . time() . '.webp';
        
        $result = ImageTransformer::toWebpFromUploaded(
            uploaded: $file,
            targetDisk: 'public',
            targetDir: 'ept-registrations/payments',
            quality: 85,
            maxWidth: 1600,
            maxHeight: null,
            basename: $basename
        );
        
        EptRegistration::create([
            'user_id' => $user->id,
            'bukti_pembayaran' => $result['path'],
            'status' => 'pending',
        ]);
        
        return redirect()->route('dashboard.ept-registration.index')
            ->with('success', 'Pendaftaran berhasil! Silakan tunggu verifikasi dari admin.');
    }

    public function kartuPeserta(Request $request)
    {
        $user = Auth::user();
        $jadwalNum = $request->query('jadwal', 1);
        
        $registration = EptRegistration::with(['grup1', 'grup2', 'grup3'])
            ->where('user_id', $user->id)
            ->approved()
            ->latest()
            ->first();
            
        if (!$registration) {
            abort(404, 'Pendaftaran tidak ditemukan.');
        }
        
        // Get the specific grup based on jadwal number
        $grup = match((int)$jadwalNum) {
            1 => $registration->grup1,
            2 => $registration->grup2,
            3 => $registration->grup3,
            default => null,
        };
        
        if (!$grup || !$grup->jadwal) {
            abort(404, 'Jadwal belum ditetapkan.');
        }
        
        $pdf = Pdf::loadView('pdf.kartu-peserta-ept', [
            'user' => $user,
            'registration' => $registration,
            'grup' => $grup,
            'jadwalNum' => $jadwalNum,
        ]);
        
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->download('Kartu_Peserta_EPT_' . Str::slug($user->name) . '_Jadwal' . $jadwalNum . '.pdf');
    }
}
