<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EptQuestionsTemplateSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected string $section;

    public function __construct(string $section)
    {
        $this->section = $section;
    }

    public function title(): string
    {
        return ucfirst($this->section);
    }

    public function headings(): array
    {
        $base = ['question', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer'];
        
        if ($this->section === 'reading') {
            $base[] = 'passage';
            $base[] = 'passage_group';
        }
        
        return $base;
    }

    public function array(): array
    {
        if ($this->section === 'listening') {
            return [
                ['What does the man want to do?', 'Go to the library', 'Meet a friend', 'Attend a class', 'Buy some books', 'A'],
                ['What is the woman\'s problem?', 'She lost her keys', 'She missed the bus', 'She forgot her homework', 'She is feeling sick', 'B'],
                ['Where does this conversation take place?', 'At school', 'At the library', 'At home', 'At a restaurant', 'A'],
            ];
        }
        
        if ($this->section === 'structure') {
            return [
                ['The students _____ their homework before the class started.', 'have finished', 'had finished', 'has finished', 'finishing', 'B'],
                ['Neither the teacher nor the students _____ ready for the exam.', 'was', 'were', 'is', 'are', 'B'],
                ['She is one of the women who _____ for equal rights.', 'fight', 'fights', 'fighting', 'fought', 'A'],
            ];
        }
        
        // Reading
        return [
            ['According to the passage, what is the main idea?', 'The importance of education', 'The history of technology', 'Environmental protection', 'Economic development', 'C', 'Climate change is one of the most pressing issues of our time. Scientists around the world agree that human activities are contributing to global warming. Environmental protection has become a priority for many nations.', 'passage_1'],
            ['What does the author suggest about carbon emissions?', 'They should be increased', 'They have no effect', 'They should be reduced', 'They are beneficial', 'C', 'Climate change is one of the most pressing issues of our time. Scientists around the world agree that human activities are contributing to global warming. Environmental protection has become a priority for many nations.', 'passage_1'],
        ];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 50,  // question
            'B' => 25,  // option_a
            'C' => 25,  // option_b
            'D' => 25,  // option_c
            'E' => 25,  // option_d
            'F' => 15,  // correct_answer
        ];
        
        if ($this->section === 'reading') {
            $widths['G'] = 60;  // passage
            $widths['H'] = 15;  // passage_group
        }
        
        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $this->getHeaderColor()],
                ],
            ],
        ];
    }

    private function getHeaderColor(): string
    {
        return match($this->section) {
            'listening' => '3B82F6', // Blue
            'structure' => 'F59E0B', // Amber
            'reading' => '10B981',   // Green
            default => '6B7280',
        };
    }
}
