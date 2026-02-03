<?php

namespace App\Imports;

use App\Models\CertificateCategory;
use App\Models\ManualCertificate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;

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
     * New CSV format has header on row 1, data starts at row 2.
     */
    public function startRow(): int
    {
        return 2;
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
     * New CSV column positions:
     * 0: NO INDUK, 1: GROUP, 2: NO ABSN
     * 3: SRN, 4: NAME
     * 5: LIST, 6: SPEAK, 7: READ, 8: WRIT, 9: PHON, 10: VOC, 11: STRU
     * 12: TTL, 13: AVE, 14: HRF, 15: BLN, 16: TAHUN, 17: SEM, 18: PRED
     */
    public function model(array $row): ?ManualCertificate
    {
        $name = trim((string) ($row[4] ?? ''));
        if (empty($name)) {
            return null;
        }

        // Skip failed participants in the new format.
        $prediction = strtoupper(trim((string) ($row[18] ?? '')));
        $letterGrade = strtoupper(trim((string) ($row[14] ?? '')));
        if ($prediction === 'FAIL' || $letterGrade === 'E') {
            return null;
        }

        $srn = trim((string) ($row[3] ?? ''));

        // Extract component scores from LIST/SPEAK/READ/WRIT/PHON/VOC/STRU.
        $scores = [
            'listening' => $this->parseScore($row[5] ?? null),
            'speaking' => $this->parseScore($row[6] ?? null),
            'reading' => $this->parseScore($row[7] ?? null),
            'writing' => $this->parseScore($row[8] ?? null),
            'phonetics' => $this->parseScore($row[9] ?? null),
            'vocabulary' => $this->parseScore($row[10] ?? null),
            'structure' => $this->parseScore($row[11] ?? null),
        ];

        $scores = array_filter($scores, fn($v) => $v !== null && $v > 0);

        if (empty($scores)) {
            return null;
        }

        $category = CertificateCategory::find($this->categoryId);
        if (!$category) {
            return null;
        }

        // Prefer semester from CSV (SEM), fallback to the form value.
        $semester = $this->parseSemester($row[17] ?? null) ?? $this->semester;
        $certificateNumber = $category->generateCertificateNumber($semester);

        return new ManualCertificate([
            'category_id' => $this->categoryId,
            'semester' => $semester,
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

        $value = trim((string) $value);
        if ($value === '' || $value === '###') {
            return null;
        }

        // Handle comma as decimal separator (European format)
        $value = str_replace(',', '.', $value);
        
        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }

    protected function parseSemester(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);
        if (!is_numeric($value)) {
            return null;
        }

        $semester = (int) $value;
        return $semester > 0 ? $semester : null;
    }
}
