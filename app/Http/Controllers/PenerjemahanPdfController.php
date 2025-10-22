<?php

namespace App\Http\Controllers;

use App\Models\Penerjemahan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class PenerjemahanPdfController extends Controller
{
    /**
     * /penerjemahan/{record}/pdf  (protected)
     */
    public function show(Penerjemahan $record)
    {
        $this->ensureCanExport($record);
        $record->load(['users', 'translator']);

        $viewData = $this->buildViewData($record);

        $pdf = Pdf::loadView('exports.terjemahan-pdf', $viewData)->setPaper('A4');
        $filename = 'Surat_Terjemahan_' . Str::of($record->users?->name ?? 'Pemohon')->slug('_') . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * /verification/{code}/penerjemahan.pdf  (public by code, rate-limited)
     */
    public function byCode(string $code)
    {
        $record = Penerjemahan::where('verification_code', $code)->firstOrFail();

        $this->ensureCanExport($record);
        $record->load(['users', 'translator']);

        $viewData = $this->buildViewData($record);

        $pdf = Pdf::loadView('exports.terjemahan-pdf', $viewData)->setPaper('A4');
        $filename = 'Surat_Terjemahan_' . Str::of($record->users?->name ?? 'Pemohon')->slug('_') . '.pdf';

        return $pdf->stream($filename);
    }

    /* ============================================================
     | Helpers
     |============================================================ */

    /**
     * Pastikan dokumen boleh diekspor (status & ada hasil).
     */
    private function ensureCanExport(Penerjemahan $record): void
    {
        $status = strtolower((string) $record->status);
        $allowed = ['selesai', 'disetujui', 'completed', 'approved'];

        abort_unless(in_array($status, $allowed, true), 403, 'Belum selesai.');
        abort_unless(
            filled($record->translated_text) || filled($record->final_file_path),
            404,
            'Belum ada hasil.'
        );
    }

    /**
     * Data untuk view PDF, termasuk gambar, verify URL, dan tanggal ttd.
     */
    private function buildViewData(Penerjemahan $record): array
    {
        $verifyCode = $record->verification_code ?? null;
        $verifyUrl  = $record->verification_url ?: ($verifyCode ? route('verification.show', $verifyCode) : null);

        return [
            'record'    => $record,
            'verifyUrl' => $verifyUrl,
            'logoPath'  => $this->toDataUriIfExists(public_path('images/logo-um.png')),
            'stampPath' => $this->toDataUriIfExists(public_path('images/stempel.png')),
            'signPath'  => $this->toDataUriIfExists(public_path('images/ttd_ketua.png')),
            'ttdDate'   => $this->formatTtdDate($record), // <-- method ini sekarang ADA
        ];
    }

    /**
     * Format tanggal tanda tangan (pakai completion_date, fallback updated_at/now).
     */
    private function formatTtdDate(Penerjemahan $record): string
    {
        $date = $record->completion_date ?? $record->updated_at ?? now();
        return Carbon::parse($date)->locale('id')->translatedFormat('d F Y');
    }

    /**
     * Ubah path gambar menjadi data URI; null bila tidak ada.
     */
    private function toDataUriIfExists(?string $absolutePath): ?string
    {
        if (!$absolutePath || !is_file($absolutePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };

        $data = @file_get_contents($absolutePath);
        if ($data === false) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode($data);
    }
}
