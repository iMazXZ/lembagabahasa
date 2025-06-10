<?php

namespace App\Http\Controllers;

use App\Models\DataNilaiTes;
use App\Models\PendaftaranEpt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function exportPdf(Request $request)
    {
        // Ambil tanggal dari URL, jika tidak ada, gunakan bulan ini sebagai default
        $tanggalMulai = Carbon::parse($request->query('mulai', now()->startOfMonth()));
        $tanggalSelesai = Carbon::parse($request->query('selesai', now()->endOfMonth()));

        // 1. Data Tren Pendaftar (Tidak ada perubahan)
        $trenPendaftar = PendaftaranEpt::query()
            ->where('status_pembayaran', 'approved')
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
            ->selectRaw('DATE_FORMAT(created_at, "%M %Y") as bulan, COUNT(*) as jumlah')
            ->groupBy('bulan')
            ->orderByRaw('MIN(created_at)')
            ->get();

        // 2. Data Sebaran Prodi (Koreksi: `prodies.name`)
        $sebaranProdi = PendaftaranEpt::query()
            ->where('status_pembayaran', 'approved')
            ->whereBetween('pendaftaran_epts.created_at', [$tanggalMulai, $tanggalSelesai])
            ->join('users', 'pendaftaran_epts.user_id', '=', 'users.id')
            ->join('prodies', 'users.prody_id', '=', 'prodies.id')
            ->select('prodies.name', DB::raw('count(pendaftaran_epts.id) as total'))
            ->groupBy('prodies.name')
            ->get();

        // 3. Data Rata-rata Skor (Koreksi: `AVG(total_score)`)
        $rataRataSkor = DataNilaiTes::query()
            ->whereBetween('created_at', [$tanggalMulai, $tanggalSelesai])
            ->where('created_at', '>=', now()->subYear())
            ->selectRaw('DATE_FORMAT(created_at, "%M %Y") as bulan, AVG(total_score) as rerata_skor')
            ->groupBy('bulan')
            ->orderByRaw('MIN(created_at)')
            ->get();

        $data = [
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
            'periode_laporan' => $tanggalMulai->translatedFormat('d F Y') . ' - ' . $tanggalSelesai->translatedFormat('d F Y'),
            'trenPendaftar' => $trenPendaftar,
            'sebaranProdi' => $sebaranProdi,
            'rataRataSkor' => $rataRataSkor,
        ];

        $pdf = Pdf::loadView('pdf.laporan-analitik', $data);
        return $pdf->stream('Laporan Analitik Lembaga Bahasa.pdf');
    }

    // TAMBAHKAN METHOD BARU DI BAWAH INI
    public function exportAllPdf()
    {
        // 1. Data Tren Pendaftar (tanpa filter tanggal)
        $trenPendaftar = PendaftaranEpt::query()
            ->where('status_pembayaran', 'approved')
            ->selectRaw('DATE_FORMAT(created_at, "%M %Y") as bulan, COUNT(*) as jumlah')
            ->groupBy('bulan')
            ->orderByRaw('MIN(created_at)')
            ->get();

        // 2. Data Sebaran Prodi (tanpa filter tanggal)
        $sebaranProdi = PendaftaranEpt::query()
            ->where('status_pembayaran', 'approved')
            ->join('users', 'pendaftaran_epts.user_id', '=', 'users.id')
            ->join('prodies', 'users.prody_id', '=', 'prodies.id')
            ->select('prodies.name', DB::raw('count(pendaftaran_epts.id) as total'))
            ->groupBy('prodies.name')
            ->get();

        // 3. Data Rata-rata Skor (tanpa filter tanggal)
        $rataRataSkor = DataNilaiTes::query()
            ->selectRaw('DATE_FORMAT(created_at, "%M %Y") as bulan, AVG(total_score) as rerata_skor')
            ->groupBy('bulan')
            ->orderByRaw('MIN(created_at)')
            ->get();

        $data = [
            'tanggal_cetak' => now()->translatedFormat('d F Y'),
            'periode_laporan' => 'Keseluruhan Data', // Ubah teks periode
            'trenPendaftar' => $trenPendaftar,
            'sebaranProdi' => $sebaranProdi,
            'rataRataSkor' => $rataRataSkor,
        ];

        $pdf = Pdf::loadView('pdf.laporan-analitik', $data);
        return $pdf->stream('Laporan Analitik Keseluruhan - Lembaga Bahasa.pdf');
    }
}