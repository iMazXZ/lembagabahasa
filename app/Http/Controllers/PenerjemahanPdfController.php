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

        // Pastikan ada verification_code & verification_url seperti perilaku lama
        $this->ensureVerification($record);

        $record->load(['users', 'translator']);

        $viewData = $this->buildViewData($record);
        $pdf = Pdf::loadView('exports.terjemahan-pdf', $viewData)->setPaper('A4');
        $filename = 'Surat_Terjemahan_' . Str::of($record->users?->name ?? 'Pemohon')->slug('_') . '.pdf';

        if (request()->boolean('dl')) {
            // 100% force download + tampil progress di browser
            $binary = $pdf->output();
            return response()->streamDownload(
                fn () => print $binary,
                $filename,
                [
                    'Content-Type'            => 'application/pdf',
                    'Content-Disposition'     => 'attachment; filename="'.$filename.'"',
                    'X-Content-Type-Options'  => 'nosniff',
                    'Cache-Control'           => 'private, max-age=0, must-revalidate',
                    'Pragma'                  => 'public',
                ]
            );
        }

        // Default: inline preview
        return $pdf->stream($filename);
    }

    /**
     * /verification/{code}/penerjemahan.pdf  (public by code)
     */
    public function byCode(string $code)
    {
        $record = Penerjemahan::where('verification_code', $code)->firstOrFail();

        $this->ensureCanExport($record);
        // Pastikan verification_url terisi juga
        $this->ensureVerification($record);

        $record->load(['users', 'translator']);

        $viewData = $this->buildViewData($record);
        $pdf = Pdf::loadView('exports.terjemahan-pdf', $viewData)->setPaper('A4');
        $filename = 'Surat_Terjemahan_' . Str::of($record->users?->name ?? 'Pemohon')->slug('_') . '.pdf';

        if (request()->boolean('dl')) {
            $binary = $pdf->output();
            return response()->streamDownload(
                fn () => print $binary,
                $filename,
                [
                    'Content-Type'            => 'application/pdf',
                    'Content-Disposition'     => 'attachment; filename="'.$filename.'"',
                    'X-Content-Type-Options'  => 'nosniff',
                    'Cache-Control'           => 'private, max-age=0, must-revalidate',
                    'Pragma'                  => 'public',
                ]
            );
        }

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
     * Pastikan kolom verifikasi terisi (meniru perilaku lama PdfExportController).
     * - Jika model punya method ensureVerification(), pakai itu.
     * - Jika tidak, generate code & url dasar di sini.
     */
    private function ensureVerification(Penerjemahan $m): void
    {
        if (method_exists($m, 'ensureVerification')) {
            $m->ensureVerification();
            $m->refresh();
            return;
        }

        // Generate code jika kosong
        if (blank($m->verification_code)) {
            $m->verification_code = 'TERJ-' . $m->getKey() . '-' . Str::upper(Str::random(6));
            $m->save();
        }

        // Isi URL absolut jika kosong
        if (blank($m->verification_url) && filled($m->verification_code)) {
            // gunakan route resmi verifikasi (absolute URL)
            $m->verification_url = route('verification.show', ['code' => $m->verification_code], true);
            $m->save();
        }

        $m->refresh();
    }

    private function buildViewData(Penerjemahan $record): array
    {
        $verifyCode = $record->verification_code ?: null;
        $verifyUrl  = $record->verification_url
            ?: ($verifyCode ? route('verification.show', ['code' => $verifyCode], true) : null);

        // Gambar lokal (bukan base64)
        $logo  = public_path('images/logo-um.png');
        $stamp = public_path('images/stempel.png');
        $sign  = public_path('images/ttd_ketua.png');

        return [
            'record'    => $record,
            'verifyUrl' => $verifyUrl,
            'logoPath'  => is_file($logo)  ? $logo  : null,
            'stampPath' => is_file($stamp) ? $stamp : null,
            'signPath'  => is_file($sign)  ? $sign  : null,
            'ttdDate'   => $this->formatTtdDate($record),
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
     * (Tidak dipakai, tapi dibiarkan kalau suatu saat balik ke data URI)
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
