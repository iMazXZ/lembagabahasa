<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Penerjemahan;
use App\Models\EptSubmission;
use App\Models\BasicListeningGrade;
use App\Models\ManualCertificate;

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

            // Hitung Daily dari helper
            $dailyAvg = null;
            if (class_exists(\App\Support\BlCompute::class)) {
                $dailyAvg = \App\Support\BlCompute::dailyAvgForUser($u->id, $rec->user_year);
            }

            $vm = [
                'type'              => 'basic_listening',
                'title'             => 'Verifikasi Sertifikat Basic Listening',
                'status'            => $status,
                'reason'            => $reason,

                'applicant_name'    => $u->name ?? '-',
                'srn'               => $u->srn ?? '-',
                'prody'             => $u->prody->name ?? '-',

                'status_text'       => $status,
                'done_at'           => now()->timezone(config('app.timezone', 'Asia/Jakarta')),

                'verification_code' => $rec->verification_code ?? '-',
                'verification_url'  => $rec->verification_url ?? route('verification.show', ['code' => $code], true),

                'pdf_url'           => $pdfUrl,

                // Tidak ada nomor/tanggal surat untuk sertifikat ini
                'nomor_surat'       => null,
                'tanggal_surat'     => null,

                // Detail nilai BL lengkap
                'bl_scores' => [
                    'attendance'     => $rec->attendance,
                    'daily'          => $dailyAvg,
                    'final_test'     => $rec->final_test,
                    'final_numeric'  => $rec->final_numeric_cached,
                    'final_letter'   => $rec->final_letter_cached,
                ],

                // Konsistensi struktur
                'scores'            => null,
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

        // ==== D. Manual Certificate (Sertifikat Interactive Class, dll) ====
        // Sekarang cek SEMUA sertifikat dengan verification_code yang sama (untuk multi-semester)
        $certificates = ManualCertificate::with(['category'])
            ->where('verification_code', $code)
            ->orderBy('semester')
            ->get();

        if ($certificates->isNotEmpty()) {
            $firstCert = $certificates->first();
            $status = 'VALID';
            $reason = $certificates->count() > 1 
                ? "Ditemukan {$certificates->count()} sertifikat untuk SRN ini."
                : 'Sertifikat valid dan terverifikasi.';

            // Build list of certificates with their details
            $certificateList = $certificates->map(function ($cert) use ($code) {
                $scoresArray = [];
                if (!empty($cert->scores) && is_array($cert->scores)) {
                    foreach ($cert->scores as $field => $value) {
                        $scoresArray[$field] = $value;
                    }
                }

                return [
                    'id' => $cert->id,
                    'semester' => $cert->semester,
                    'certificate_number' => $cert->certificate_number,
                    'grade' => $cert->grade,
                    'average_score' => $cert->average_score,
                    'total_score' => $cert->total_score,
                    'issued_at' => $cert->issued_at?->format('d M Y'),
                    'scores' => $scoresArray,
                    'pdf_url' => route('manual-certificate.download-by-id', ['id' => $cert->id]),
                ];
            })->all();

            $vm = [
                'type'              => 'manual_certificate',
                'title'             => 'Verifikasi Sertifikat ' . ($firstCert->category?->name ?? 'Manual'),
                'status'            => $status,
                'reason'            => $reason,

                'applicant_name'    => $firstCert->name ?? '-',
                'srn'               => $firstCert->srn ?? '-',
                'prody'             => $firstCert->study_program ?? '-',

                'status_text'       => $certificates->count() . ' Sertifikat',
                'done_at'           => $firstCert->issued_at?->timezone(config('app.timezone', 'Asia/Jakarta')),

                'verification_code' => $firstCert->verification_code ?? '-',
                'verification_url'  => route('verification.show', ['code' => $code], true),

                'pdf_url'           => null, // Multiple PDFs handled in certificates array

                'nomor_surat'       => null,
                'tanggal_surat'     => null,

                'scores'            => null,
                
                // Data khusus untuk multi-certificate
                'certificates'      => $certificateList,
            ];

            return view('verification.show', ['vm' => $vm]);
        }

        // ==== E. Tidak ditemukan ====
        $vm = [
            'type'   => null,
            'title'  => 'Verifikasi Dokumen',
            'status' => 'INVALID',
            'reason' => 'Kode verifikasi tidak ditemukan.',
        ];

        return response()->view('verification.show', ['vm' => $vm], 404);
    }
}
