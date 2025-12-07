<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Penerjemahan;
use App\Models\EptSubmission;
use App\Models\BasicListeningGrade;

class VerificationController extends Controller
{
    public function index()
    {
        return view('verification.index');
    }

    public function show(string $code)
    {
        // ==== A. Penerjemahan (lama) ====
        if ($rec = Penerjemahan::with(['users.prody'])->where('verification_code', $code)->first()) {
            $status = $rec->status === 'Selesai' ? 'VALID' : 'PENDING';
            $reason = $status === 'VALID'
                ? 'Data cocok dan status dokumen telah diselesaikan.'
                : 'Dokumen ditemukan, namun status belum selesai.';

            $vm = [
                'type'              => 'penerjemahan',
                'title'             => 'Cek Dokumen Penerjemahan Abstrak',
                'status'            => $status,
                'reason'            => $reason,

                'applicant_name'    => $rec->users->name ?? '-',
                'srn'               => $rec->users->srn ?? '-',
                'prody'             => $rec->users->prody->name ?? '-',

                'status_text'       => $rec->status ?? '-',
                'done_at'           => optional($rec->completion_date)->timezone(config('app.timezone', 'Asia/Jakarta')),

                'verification_code' => $rec->verification_code ?? '-',
                'verification_url'  => $rec->verification_url,

                // Jika file tersimpan di storage publik
                'pdf_url'           => ($rec->pdf_path && Storage::disk('public')->exists($rec->pdf_path))
                    ? asset('storage/' . $rec->pdf_path)
                    : null,

                // Field yang tidak relevan untuk tipe ini diset default
                'nomor_surat'       => null,
                'tanggal_surat'     => null,

                // Konsistensi struktur
                'scores'            => null,
            ];

            return view('verification.show', ['vm' => $vm]);
        }

        // ==== B. Sertifikat Basic Listening (on-the-fly, tanpa simpan file) ====
        if ($rec = BasicListeningGrade::with(['user.prody'])->where('verification_code', $code)->first()) {
            $u = $rec->user;

            // Dokumen dinyatakan VALID hanya jika attendance & final_test terisi angka
            $isComplete = is_numeric($rec->attendance) && is_numeric($rec->final_test);
            $status     = $isComplete ? 'VALID' : 'PENDING';
            $reason     = $isComplete
                ? 'Data cocok dan komponen nilai wajib sudah lengkap.'
                : 'Dokumen ditemukan, namun Attendance / Final Test belum lengkap.';

            // Link PDF on-the-fly (tidak tersimpan di storage)
            $pdfUrl = $isComplete
                ? route('bl.certificate.bycode', ['code' => $code, 'inline' => 1]) // preview; hapus inline utk unduh
                : null;

            $vm = [
                'type'              => 'basic_listening',
                'title'             => 'Verifikasi Sertifikat Basic Listening',
                'status'            => $status,
                'reason'            => $reason,

                'applicant_name'    => $u->name ?? '-',
                'srn'               => $u->srn ?? '-',
                'prody'             => $u->prody->name ?? '-',

                'status_text'       => $status,
                'done_at'           => now()->timezone(config('app.timezone', 'Asia/Jakarta')), // atau bisa kosongkan bila perlu

                'verification_code' => $rec->verification_code ?? '-',
                'verification_url'  => $rec->verification_url ?? route('verification.show', ['code' => $code], true),

                'pdf_url'           => $pdfUrl,

                // Tidak ada nomor/tanggal surat untuk sertifikat ini
                'nomor_surat'       => null,
                'tanggal_surat'     => null,

                // Tampilkan ringkas nilai (opsional, view bisa menyesuaikan)
                'scores'            => [
                    ['label' => 'Attendance',  'tanggal' => null, 'nilai' => $rec->attendance],
                    // Daily dihitung saat generate PDF; di sini opsional kalau ingin hitung lagi
                    // ['label' => 'Daily (avg S1–S5)', 'tanggal' => null, 'nilai' => app(\App\Support\BlCompute::class)::dailyAvgForUser($u->id, $u->year) ],
                    ['label' => 'Final Test',  'tanggal' => null, 'nilai' => $rec->final_test],
                ],
            ];

            return view('verification.show', ['vm' => $vm]);
        }

        // ==== C. EPT Submission (Surat Rekomendasi) ====
        if ($rec = EptSubmission::with(['user.prody'])->where('verification_code', $code)->first()) {
            $status = $rec->status === 'approved' ? 'VALID' : 'PENDING';
            $reason = $status === 'VALID'
                ? 'Surat telah disetujui.'
                : 'Pengajuan ditemukan, namun belum disetujui.';

            $vm = [
                'type'              => 'ept',
                'title'             => 'Cek Surat Rekomendasi EPT',
                'status'            => $status,
                'reason'            => $reason,

                'applicant_name'    => $rec->user->name ?? '-',
                'srn'               => $rec->user->srn ?? '-',
                'prody'             => $rec->user->prody->name ?? '-',

                'status_text'       => $rec->status ?? '-',
                'done_at'           => optional($rec->approved_at)->timezone(config('app.timezone', 'Asia/Jakarta')),

                'verification_code' => $rec->verification_code ?? '-',
                'verification_url'  => $rec->verification_url,

                // PDF EPT via route generator (on-the-fly atau tersimpan sesuai implementasi kamu)
                'pdf_url'           => route('verification.ept.pdf', ['code' => $code]),

                'nomor_surat'       => $rec->surat_nomor ?? '-',
                'tanggal_surat'     => optional($rec->approved_at)->timezone(config('app.timezone', 'Asia/Jakarta')),

                'scores'            => [
                    ['label' => 'Tes I',   'tanggal' => $rec->tanggal_tes_1, 'nilai' => $rec->nilai_tes_1],
                    ['label' => 'Tes II',  'tanggal' => $rec->tanggal_tes_2, 'nilai' => $rec->nilai_tes_2],
                    ['label' => 'Tes III', 'tanggal' => $rec->tanggal_tes_3, 'nilai' => $rec->nilai_tes_3],
                ],
            ];

            return view('verification.show', ['vm' => $vm]);
        }

        // ==== D. Tidak ditemukan ====
        $vm = [
            'type'   => null,
            'title'  => 'Verifikasi Dokumen',
            'status' => 'INVALID',
            'reason' => 'Kode verifikasi tidak ditemukan.',
        ];

        return response()->view('verification.show', ['vm' => $vm], 404);
    }
}
