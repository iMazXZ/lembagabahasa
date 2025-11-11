<?php

namespace App\Exports;

use App\Models\User;
use App\Support\BlCompute;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TutorMahasiswaTemplateExport implements
    FromCollection, WithHeadings, WithMapping, WithEvents, WithColumnWidths, WithStyles
{
    /** @var \Illuminate\Support\Collection<int,\App\Models\User> */
    protected Collection $users;
    protected ?string $groupNo;
    protected ?string $prodyName;
    protected int $rowIndex = 0; // untuk kolom "No."

    public function __construct(Collection $users, ?string $groupNo = null, ?string $prodyName = null)
    {
        // Urutkan SRN (NPM) terbesar di atas
        $this->users = $users
            ->sortByDesc(fn (User $u) => (int) preg_replace('/\D/', '', (string) $u->srn))
            ->values();

        $this->groupNo  = $groupNo;
        $this->prodyName= $prodyName;
    }

    public function collection()
    {
        return $this->users;
    }

    public function headings(): array
    {
        $groupLine = 'Group';
        if ($this->groupNo || $this->prodyName) {
            $groupLine = trim('Group ' . ($this->groupNo ?? '') . ($this->prodyName ? " — {$this->prodyName}" : ''));
        }

        // Baris 1: judul "Group …" (akan di-merge A1:H1)
        // Baris 2: kolom MEETING (akan di-merge D2:F2)
        // Baris 3: subheader nyata
        return [
            [$groupLine],
            ['', '', '', 'MEETING', '', '', '', ''],
            ['No', 'Name', 'SRN', 'Attendance', 'Daily', 'Final Test', 'SCORE', 'ALPHABETICAL SCORE'],
        ];
    }

    public function map($user): array
    {
        /** @var User $user */
        $s1 = $this->meetingScore($user, 1);
        $s2 = $this->meetingScore($user, 2);
        $s3 = $this->meetingScore($user, 3);
        $s4 = $this->meetingScore($user, 4);
        $s5 = $this->meetingScore($user, 5);

        $daily = BlCompute::dailyAvgForUser($user->id, $user->year);
        $att   = optional($user->basicListeningGrade)->attendance;
        $final = optional($user->basicListeningGrade)->final_test;

        // Final numeric & letter (pakai cache kalau ada; fallback rata-rata dari 3 komponen)
        $finalNumeric = optional($user->basicListeningGrade)->final_numeric_cached;
        if ($finalNumeric === null) {
            $parts = [];
            if (is_numeric($att))   $parts[] = (float)$att;
            if (is_numeric($daily)) $parts[] = (float)$daily;
            if (is_numeric($final)) $parts[] = (float)$final;
            $finalNumeric = $parts ? round(array_sum($parts) / count($parts), 2) : null;
        }
        $finalLetter = optional($user->basicListeningGrade)->final_letter_cached ?? '';

        return [
            ++$this->rowIndex,            // No
            $user->name,                  // Name
            (string) $user->srn,          // SRN (string supaya nol depan tidak hilang)
            $this->fmt($att),             // Attendance
            $this->fmt($daily),           // Daily
            $this->fmt($final),           // Final Test
            $this->fmt($finalNumeric),    // SCORE
            $finalLetter,                 // ALPHABETICAL SCORE
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // No
            'B' => 34,  // Name
            'C' => 14,  // SRN
            'D' => 13,  // Attendance
            'E' => 13,  // Daily
            'F' => 13,  // Final Test
            'G' => 12,  // SCORE
            'H' => 22,  // ALPHABETICAL SCORE
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Tebalkan baris judul & header
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A2:H3')->getFont()->setBold(true);

        // Rata tengah judul & header
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Rata tengah untuk kolom angka
        $sheet->getStyle('A:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Wrap text untuk judul panjang
        $sheet->getStyle('A1:H1')->getAlignment()->setWrapText(true);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge header
                $sheet->mergeCells('A1:H1'); // "Group …"
                $sheet->mergeCells('D2:F2'); // "MEETING"

                // Garis border untuk header + tabel
                $lastRow = 3 + $this->users->count(); // data mulai baris 4
                $sheet->getStyle("A3:H{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Tinggi baris header
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(22);
            },
        ];
    }

    private function fmt($val): string
    {
        return is_numeric($val) ? (string) $val : '';
    }

    private function meetingScore(User $user, int $meeting): ?float
    {
        // 1) Manual override
        $manual = $user->basicListeningManualScores
            ->firstWhere('meeting', $meeting)
            ->score ?? null;

        if (is_numeric($manual)) return (float) $manual;

        // 2) Attempt submitted terbaru
        $attempt = $user->basicListeningAttempts
            ->where('session_id', $meeting)
            ->filter(fn ($a) => !is_null($a->submitted_at))
            ->sortByDesc('submitted_at')
            ->first();

        return is_numeric($attempt?->score) ? (float) $attempt->score : null;
    }
}
