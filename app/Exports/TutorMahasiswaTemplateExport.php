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
    protected int $rowIndex = 0;

    public function __construct(Collection $users, ?string $groupNo = null, ?string $prodyName = null)
    {
        // JANGAN SORTING LAGI DI SINI.
        // Terima apa adanya dari Controller.
        $this->users = $users; 

        $this->groupNo   = $groupNo;
        $this->prodyName = $prodyName;
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

        return [
            [$groupLine],
            ['', '', '', 'MEETING', '', '', '', ''],
            ['No', 'Name', 'SRN', 'Attendance', 'Daily', 'Final Test', 'SCORE', 'ALPHABETICAL SCORE'],
        ];
    }

    public function map($user): array
    {
        /** @var User $user */
        $daily = BlCompute::dailyAvgForUser($user->id, $user->year);
        $att   = optional($user->basicListeningGrade)->attendance;
        $final = optional($user->basicListeningGrade)->final_test;

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
            ++$this->rowIndex,            
            $user->name,                  
            (string) $user->srn,          
            $this->fmt($att),             
            $this->fmt($daily),           
            $this->fmt($final),           
            $this->fmt($finalNumeric),    
            $finalLetter,                 
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   'B' => 34,  'C' => 14,  'D' => 13,
            'E' => 13,  'F' => 13,  'G' => 12,  'H' => 22,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A2:H3')->getFont()->setBold(true);
        $sheet->getStyle('A1:H3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:H1')->getAlignment()->setWrapText(true);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:H1'); 
                $sheet->mergeCells('D2:F2'); 

                $lastRow = 3 + $this->users->count(); 
                $sheet->getStyle("A3:H{$lastRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

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
}