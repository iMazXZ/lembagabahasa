<?php

namespace App\Filament\Resources\BasicListeningAttemptResource\Pages;

use App\Filament\Resources\BasicListeningAttemptResource;
use App\Models\BasicListeningAnswer;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Edit attempt FIB:
 * - Tidak lagi mengandalkan key array sebagai ID; selalu pakai $row['id'].
 * - Normalisasi boolean untuk is_correct dari berbagai bentuk input ("1"/"0"/true/false/"on"/"yes").
 * - Recalculate score secara konsisten di mutateFormDataBeforeSave() dan disinkronkan lagi setelah save.
 * - Biarkan Repeater::relationship() menyimpan anak; afterSave hanya melakukan sinkronisasi & sanity check.
 */
class EditBasicListeningAttempt extends EditRecord
{
    protected static string $resource = BasicListeningAttemptResource::class;

    /**
     * (Opsional) Jika perlu normalisasi sebelum form diisi.
     * Di sini kita biarkan apa adanya supaya tidak bentrok dengan tampilan.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    /**
     * Normalisasi state dan hitung skor sebelum disimpan oleh Filament.
     * Penting: jangan bergantung pada key array sebagai ID; kita perbaiki isi $data['answers'] saja.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $answers = $data['answers'] ?? [];
        $correct = 0;
        $total   = 0;

        foreach ($answers as &$row) {
            $raw = $row['is_correct'] ?? null;
            $bool = $this->toBool($raw);
            $row['is_correct'] = $bool;

            $total++;
            if ($bool) {
                $correct++;
            }
        }
        unset($row);

        // Hindari bagi 0
        if ($total > 0) {
            $data['score'] = round(($correct / $total) * 100, 2);
        } else {
            $data['score'] = 0.0;
        }

        // Kembalikan answers yang telah dinormalisasi agar Repeater::relationship() menyimpan dengan benar
        $data['answers'] = $answers;

        Log::info('mutateFormDataBeforeSave - recalc score', [
            'correct' => $correct,
            'total'   => $total,
            'score'   => $data['score'],
        ]);

        return $data;
    }

    /**
     * Setelah Filament menyimpan parent + children (via relationship()),
     * kita pastikan setiap child tersinkronisasi dan ID digunakan dengan benar.
     * Lalu kita lakukan sanity re-calc skor dari database sebagai final check.
     */
    protected function afterSave(): void
    {
        /** @var \App\Models\BasicListeningAttempt $attempt */
        $attempt = $this->getRecord();

        $answersState = $this->form->getState()['answers'] ?? [];
        Log::info('afterSave - incoming answers state', [
            'attempt_id' => $attempt->id,
            'count'      => count($answersState),
        ]);

        // Sinkronisasi jawaban berdasarkan ID (bila ada). Ini aman untuk kasus Select boolean -> string.
        foreach ($answersState as $row) {
            $id = $row['id'] ?? null;
            if (!$id) {
                Log::warning('afterSave - skip row without id', ['row' => $row]);
                continue;
            }

            $isCorrect = $this->toBool($row['is_correct'] ?? null);

            BasicListeningAnswer::query()
                ->where('id', $id)
                ->where('attempt_id', $attempt->id)
                ->update([
                    'answer'     => $row['answer'] ?? null,
                    'is_correct' => $isCorrect,
                ]);
        }

        // Sanity check: hitung ulang skor dari DB
        $total   = max(1, (int) $attempt->answers()->count());
        $correct = (int) $attempt->answers()->where('is_correct', true)->count();
        $attempt->score = round(($correct / $total) * 100, 2);
        $attempt->save();

        Log::info('afterSave - final score recomputed', [
            'attempt_id' => $attempt->id,
            'correct'    => $correct,
            'total'      => $total,
            'score'      => $attempt->score,
        ]);
    }

    /**
     * Judul notifikasi sukses.
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Attempt berhasil disimpan';
    }

    /**
     * (Opsional) Arahkan kembali ke halaman edit yang sama atau ke index.
     * Kembalikan ke halaman sebelumnya supaya nyaman lanjut edit.
     */
    protected function getRedirectUrl(): string
    {
        // Kembali ke halaman detail/edit yang sama
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }

    /**
     * Helper robust untuk konversi berbagai representasi nilai form menjadi boolean murni.
     */
    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value === 1;
        }
        $str = strtolower((string) $value);
        return in_array($str, ['1', 'true', 'on', 'yes', 'y'], true);
    }
}
