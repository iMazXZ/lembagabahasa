<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranEpt;
use App\Models\Penerjemahan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function exportPdf(Request $request)
    {
        $tanggalMulai = Carbon::parse($request->query('mulai', now()->startOfMonth()));
        $tanggalSelesai = Carbon::parse($request->query('selesai', now()->endOfMonth()));

        $pendaftarEptDetails = PendaftaranEpt::query()
            ->where('status_pembayaran', 'approved')
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
            ->get();

        $dataPenerjemahan = Penerjemahan::with(['users', 'translator'])
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
            ->get();

        $data = [
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
            'periode_laporan' => $tanggalMulai->translatedFormat('d F Y') . ' - ' . $tanggalSelesai->translatedFormat('d F Y'),
            'pendaftarEptDetails' => $pendaftarEptDetails,
            'dataPenerjemahan' => $dataPenerjemahan,
        ];

        $pdf = Pdf::loadView('pdf.laporan-analitik', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan Analitik Lembaga Bahasa.pdf');
    }

    public function exportAllPdf()
    {
        $pendaftarEptDetails = PendaftaranEpt::query()
            ->where('status_pembayaran', 'approved')
            ->get();

        $dataPenerjemahan = Penerjemahan::with(['users', 'translator'])
            ->get();

        $data = [
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
            'periode_laporan' => 'Keseluruhan Data',
            'pendaftarEptDetails' => $pendaftarEptDetails,
            'dataPenerjemahan' => $dataPenerjemahan,
        ];

        $pdf = Pdf::loadView('pdf.laporan-analitik', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan Analitik Keseluruhan - Lembaga Bahasa.pdf');
    }
}
