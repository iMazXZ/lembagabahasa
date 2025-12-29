<?php

namespace App\Imports;

use App\Models\CertificateCategory;
use App\Models\ManualCertificate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ManualCertificateImport implements ToModel, WithStartRow, WithCustomCsvSettings, SkipsEmptyRows
{
    protected int $categoryId;
    protected ?int $semester;
    protected string $issuedAt;
    protected ?string $studyProgram;

    public function __construct(int $categoryId, ?int $semester, string $issuedAt, ?string $studyProgram = null)
    {
        $this->categoryId = $categoryId;
        $this->semester = $semester;
        $this->issuedAt = $issuedAt;
        $this->studyProgram = $studyProgram;
    }

    /**
     * Start from row 4 (skip 3 header rows)
     */
    public function startRow(): int
    {
        return 4;
    }

    /**
     * CSV uses semicolon delimiter
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ';',
            'enclosure' => '"',
            'input_encoding' => 'UTF-8',
        ];
    }

    /**
     * Column positions based on CSV structure:
     * 0: NO
     * 1: Names
     * 2: SRN
     * 3: Listening I, 4: Listening O, 5: Listening Ave
     * 6: Speaking I, 7: Speaking O, 8: Speaking Ave  
     * 9: Reading I, 10: Reading O, 11: Reading Ave
     * 12: Writing I, 13: Writing O, 14: Writing Ave
     * 15: Phonetics I, 16: Phonetics O, 17: Phonetics Ave
     * 18: Grammar I, 19: Grammar O, 20: Grammar Ave
     * 21: Vocabulary I, 22: Vocabulary O, 23: Vocabulary Ave
     * 24: Grand Total
     * 25: Average
     * 26: Alphabetical Score
     */
    public function model(array $row): ?ManualCertificate
    {
        // Get name from column 1
        $name = trim($row[1] ?? '');
        if (empty($name)) {
            return null;
        }

        // Get SRN from column 2
        $srn = trim($row[2] ?? '');

        // Extract Ave scores (columns 5, 8, 11, 14, 17, 20, 23)
        $scores = [
            'listening' => $this->parseScore($row[5] ?? null),
            'speaking' => $this->parseScore($row[8] ?? null),
            'reading' => $this->parseScore($row[11] ?? null),
            'writing' => $this->parseScore($row[14] ?? null),
            'phonetics' => $this->parseScore($row[17] ?? null),
            'structure' => $this->parseScore($row[20] ?? null),
            'vocabulary' => $this->parseScore($row[23] ?? null),
        ];

        // Filter out null/zero scores
        $scores = array_filter($scores, fn($v) => $v !== null && $v > 0);

        // Skip if no valid scores
        if (empty($scores)) {
            return null;
        }

        // Get category for certificate number generation
        $category = CertificateCategory::find($this->categoryId);
        $certificateNumber = $category?->generateCertificateNumber($this->semester);

        return new ManualCertificate([
            'category_id' => $this->categoryId,
            'semester' => $this->semester,
            'certificate_number' => $certificateNumber,
            'name' => $name,
            'srn' => !empty($srn) ? $srn : null,
            'study_program' => $this->studyProgram,
            'scores' => $scores,
            'issued_at' => $this->issuedAt,
        ]);
    }

    /**
     * Parse score value, handling various formats
     */
    protected function parseScore(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Handle comma as decimal separator (European format)
        $value = str_replace(',', '.', (string) $value);
        
        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }
}
