<?php
// app/Http/Controllers/VerificationController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penerjemahan;

class VerificationController extends Controller
{
    // NEW: halaman input kode
    public function index()
    {
        // view tanpa data (hanya form)
        return view('verification.index');
    }

    // existing: tampilkan hasil verifikasi untuk {code}
    public function show(string $code)
    {
        $record = Penerjemahan::where('verification_code', $code)->first();

        if (! $record) {
            return view('verification.show', [
                'status' => 'INVALID',
                'reason' => 'Kode verifikasi tidak ditemukan.',
                'record' => null,
            ]);
        }

        // contoh logika status
        $status = $record->status === 'Selesai' ? 'VALID' : 'PENDING';
        $reason = $status === 'VALID'
            ? 'Data cocok dan status dokumen telah diselesaikan.'
            : 'Dokumen ditemukan, namun status belum selesai.';

        return view('verification.show', compact('status', 'reason', 'record'));
    }
}
