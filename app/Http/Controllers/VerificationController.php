<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penerjemahan;
use App\Models\EptSubmission;

class VerificationController extends Controller
{
    public function index()
    {
        return view('verification.index');
    }

    public function show(string $code)
    {
        // ==== A. Coba cocokkan ke Penerjemahan (yang lama) ====
        if ($rec = Penerjemahan::with(['users.prody'])->where('verification_code', $code)->first()) {
            $status = $rec->status === 'Selesai' ? 'VALID' : 'PENDING';
            $reason = $status === 'VALID'
                ? 'Data cocok dan status dokumen telah diselesaikan.'
                : 'Dokumen ditemukan, namun status belum selesai.';

            $vm = [
                'type'             => 'penerjemahan',
                'title'            => 'Verifikasi Dokumen Penerjemahan Abstrak',
                'status'           => $status,
                'reason'           => $reason,
                'applicant_name'   => $rec->users->name ?? '-',
                'srn'              => $rec->users->srn ?? '-',
                'prody'            => $rec->users->prody->name ?? '-',
                'status_text'      => $rec->status ?? '-',
                'done_at'          => optional($rec->completion_date)->timezone(config('app.timezone','Asia/Jakarta')),
                'verification_code'=> $rec->verification_code ?? '-',
                'verification_url' => $rec->verification_url,
                'pdf_url'          => ($rec->pdf_path && \Storage::disk('public')->exists($rec->pdf_path))
                                        ? asset('storage/'.$rec->pdf_path) : null,
                // khusus penerjemahan tidak butuh nilai/nomor surat
                'nomor_surat'      => null,
                'tanggal_surat'    => null,
                'scores'           => null,
            ];

            return view('verification.show', ['vm' => $vm]);
        }

        // ==== B. Cek ke EPT Submission (Surat Rekomendasi) ====
        if ($rec = EptSubmission::with(['user.prody'])->where('verification_code', $code)->first()) {
            $status = $rec->status === 'approved' ? 'VALID' : 'PENDING';
            $reason = $status === 'VALID'
                ? 'Surat telah disetujui (approved).'
                : 'Pengajuan ditemukan, namun belum disetujui.';

            $vm = [
                'type'             => 'ept',
                'title'            => 'Verifikasi Surat Rekomendasi EPT',
                'status'           => $status,
                'reason'           => $reason,
                'applicant_name'   => $rec->user->name ?? '-',
                'srn'              => $rec->user->srn ?? '-',
                'prody'            => $rec->user->prody->name ?? '-',
                'status_text'      => $rec->status ?? '-',
                'done_at'          => optional($rec->approved_at)->timezone(config('app.timezone','Asia/Jakarta')),
                'verification_code'=> $rec->verification_code ?? '-',
                'verification_url' => $rec->verification_url,
                'pdf_url'          => route('verification.ept.pdf', ['code' => $code]),
                'nomor_surat'      => $rec->surat_nomor ?? '-',
                'tanggal_surat'    => optional($rec->approved_at)->timezone(config('app.timezone','Asia/Jakarta')),
                'scores'           => [
                    ['label' => 'Tes I',   'tanggal' => optional($rec->tanggal_tes_1), 'nilai' => $rec->nilai_tes_1],
                    ['label' => 'Tes II',  'tanggal' => optional($rec->tanggal_tes_2), 'nilai' => $rec->nilai_tes_2],
                    ['label' => 'Tes III', 'tanggal' => optional($rec->tanggal_tes_3), 'nilai' => $rec->nilai_tes_3],
                ],
            ];

            return view('verification.show', ['vm' => $vm]);
        }

        // ==== C. Tidak ditemukan ====
        $vm = [
            'type'   => null,
            'title'  => 'Verifikasi Dokumen',
            'status' => 'INVALID',
            'reason' => 'Kode verifikasi tidak ditemukan.',
        ];

        return response()->view('verification.show', ['vm' => $vm], 404);
    }
}
