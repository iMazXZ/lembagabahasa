<?php

namespace App\Http\Controllers;

use App\Models\EptSubmission;
use Barryvdh\DomPDF\Facade\Pdf;

class EptSubmissionPdfController extends Controller
{
    public function show(EptSubmission $submission)
    {
        abort_unless($submission->status === 'approved', 403, 'Belum disetujui');

        $submission->load(['user.prody']);

        $nomorSurat   = $submission->surat_nomor
            ?? ('001/II.3.AU/F/KET/LB_UMM/' . now()->year);
        $tanggalSurat = $submission->approved_at?->timezone(config('app.timezone', 'Asia/Jakarta'))
            ?->translatedFormat('d F Y');

        $pdf = Pdf::loadView('exports.surat-rekomendasi', [
            'submission'   => $submission,
            'nomorSurat'   => $nomorSurat,
            'tanggalSurat' => $tanggalSurat,
        ])->setPaper('A4');

        return $pdf->stream("Surat_Rekomendasi_{$submission->user->name}.pdf");
    }

    public function byCode(string $code)
        {
            $submission = EptSubmission::query()
                ->where('verification_code', $code)
                ->firstOrFail();

            abort_unless($submission->status === 'approved', 403, 'Belum disetujui');

            // Opsional: jika kamu ingin membatasi ke pemilik saja saat login
            // abort_if(auth()->check() && auth()->id() !== $submission->user_id && !auth()->user()->hasAnyRole(['Admin','Staf Administrasi','Kepala Lembaga']), 403);

            $submission->load(['user.prody']);

            $nomorSurat   = $submission->surat_nomor ?? ('001/II.3.AU/F/KET/LB_UMM/' . now()->year);
            $tanggalSurat = optional($submission->approved_at)
                ?->timezone(config('app.timezone','Asia/Jakarta'))
                ?->translatedFormat('d F Y');

            $pdf = Pdf::loadView('exports.surat-rekomendasi', [
                'submission'   => $submission,
                'nomorSurat'   => $nomorSurat,
                'tanggalSurat' => $tanggalSurat,
            ])->setPaper('A4');

            // Untuk public link, lebih enak inline → bisa dilihat di browser
            return $pdf->stream("Surat_Rekomendasi_{$submission->user->name}.pdf");
        }
}