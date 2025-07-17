<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranEpt;
use App\Models\Penerjemahan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function exportPdf(Request $request)
    {
        $tanggalMulai = Carbon::parse($request->query('mulai', now()->startOfMonth()));
        $tanggalSelesai = Carbon::parse($request->query('selesai', now()->endOfMonth()));

        // Mengambil data pendaftar EPT yang SUDAH memiliki nilai
        $pendaftarEptDetails = PendaftaranEpt::query()
            ->whereHas('pendaftaranGrupTes.dataNilaiTes')
            ->where('status_pembayaran', 'approved')
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
            ->get();

        // Mengambil detail layanan penerjemahan
        $dataPenerjemahan = Penerjemahan::with(['users', 'translator'])
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
            ->get();

        $data = [
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
            'periode_laporan' => $tanggalMulai->translatedFormat('d F Y') . ' - ' . $tanggalSelesai->translatedFormat('d F Y'),
            'pendaftarEptDetails' => $pendaftarEptDetails,
            'dataPenerjemahan' => $dataPenerjemahan,
        ];

        $pdf = Pdf::loadView('pdf.laporan-analitik', $data);
        return $pdf->stream('Laporan Analitik Lembaga Bahasa.pdf');
    }

    public function exportAllPdf()
    {
        // Mengambil data pendaftar EPT yang SUDAH memiliki nilai
        $pendaftarEptDetails = PendaftaranEpt::query()
            ->whereHas('pendaftaranGrupTes.dataNilaiTes') // <-- BARIS INI DITAMBAHKAN
            ->where('status_pembayaran', 'approved')
            ->get();
        
        // Mengambil detail layanan penerjemahan
        $dataPenerjemahan = Penerjemahan::with(['users', 'translator'])
            ->get();

        $data = [
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
            'periode_laporan' => 'Keseluruhan Data',
            'pendaftarEptDetails' => $pendaftarEptDetails,
            'dataPenerjemahan' => $dataPenerjemahan,
        ];

        $pdf = Pdf::loadView('pdf.laporan-analitik', $data);
        return $pdf->stream('Laporan Analitik Keseluruhan - Lembaga Bahasa.pdf');
    }
}