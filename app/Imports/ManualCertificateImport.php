<?php

namespace App\Imports;

use App\Models\CertificateCategory;
use App\Models\ManualCertificate;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ManualCertificateImport implements ToModel, WithStartRow, WithCustomCsvSettings, SkipsEmptyRows
{
    use RemembersRowNumber;

    protected int $categoryId;
    protected ?int $semester;
    protected string $issuedAt;
    protected ?string $studyProgram;
    protected ?CertificateCategory $category = null;

    protected int $processedRows = 0;
    protected int $importedRows = 0;
    protected int $skippedRows = 0;

    /** @var array<int, array{row:int, name:string, reason:string}> */
    protected array $skippedDetails = [];

    public function __construct(int $categoryId, ?int $semester, string $issuedAt, ?string $studyProgram = null)
    {
        $this->categoryId = $categoryId;
        $this->semester = $semester;
        $this->issuedAt = $issuedAt;
        $this->studyProgram = $studyProgram;
        $this->category = CertificateCategory::find($categoryId);
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
     * Supported CSV formats:
     * 1) With PRODI column:
     *    0 NO INDUK, 1 GROUP, 2 ABSEN, 3 SRN, 4 NAME, 5 PRODI,
     *    6 LIST, 7 SPEAK, 8 READ, 9 WRIT, 10 PHON, 11 VOC, 12 STRU,
     *    13 TTL, 14 AVE, 15 HRF, 16 BLN, 17 TAHUN, 18 SEM, 19 PRED
     * 2) Legacy without PRODI:
     *    0 NO INDUK, 1 GROUP, 2 ABSEN, 3 SRN, 4 NAME,
     *    5 LIST, 6 SPEAK, 7 READ, 8 WRIT, 9 PHON, 10 VOC, 11 STRU,
     *    12 TTL, 13 AVE, 14 HRF, 15 BLN, 16 TAHUN, 17 SEM, 18 PRED
     */
    public function model(array $row): ?ManualCertificate
    {
        $this->processedRows++;
        $rowNumber = $this->getRowNumber();
        $analyzed = $this->analyzeRow($row);

        if ($analyzed['reason'] !== null) {
            $this->skippedRows++;
            $this->skippedDetails[] = [
                'row' => $rowNumber,
                'name' => $analyzed['name'] ?: '-',
                'reason' => $analyzed['reason'],
            ];

            return null;
        }

        if (! $this->category) {
            $this->skippedRows++;
            $this->skippedDetails[] = [
                'row' => $rowNumber,
                'name' => $analyzed['name'] ?: '-',
                'reason' => 'Kategori sertifikat tidak valid',
            ];

            return null;
        }

        $semester = $analyzed['semester'] ?? $this->semester;
        $certificateNumber = $this->category->generateCertificateNumber($semester);
        $this->importedRows++;

        return new ManualCertificate([
            'category_id' => $this->categoryId,
            'semester' => $semester,
            'certificate_number' => $certificateNumber,
            'name' => $analyzed['name'],
            'srn' => $analyzed['srn'],
            'study_program' => $analyzed['study_program'] ?? $this->studyProgram,
            'scores' => $analyzed['scores'],
            'issued_at' => $this->issuedAt,
        ]);
    }

    public function preview(string $filePath): array
    {
        $summary = [
            'total_rows' => 0,
            'valid_rows' => 0,
            'skipped_rows' => 0,
            'reason_counts' => [],
            'sample_skipped' => [],
        ];

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('CSV tidak bisa dibaca.');
        }

        // Skip header.
        fgetcsv($handle, 0, ';', '"');

        $line = 2;
        while (($row = fgetcsv($handle, 0, ';', '"')) !== false) {
            if ($this->isRowEmpty($row)) {
                $line++;
                continue;
            }

            $summary['total_rows']++;
            $analyzed = $this->analyzeRow($row);

            if ($analyzed['reason'] === null) {
                $summary['valid_rows']++;
            } else {
                $summary['skipped_rows']++;
                $reason = $analyzed['reason'];
                $summary['reason_counts'][$reason] = ($summary['reason_counts'][$reason] ?? 0) + 1;

                if (count($summary['sample_skipped']) < 8) {
                    $summary['sample_skipped'][] = [
                        'row' => $line,
                        'name' => $analyzed['name'] ?: '-',
                        'reason' => $reason,
                    ];
                }
            }

            $line++;
        }

        fclose($handle);

        return $summary;
    }

    public function getReportSummary(): array
    {
        return [
            'processed_rows' => $this->processedRows,
            'imported_rows' => $this->importedRows,
            'skipped_rows' => $this->skippedRows,
            'skip_reasons' => $this->getSkipReasonCounts(),
            'sample_skipped' => array_slice($this->skippedDetails, 0, 10),
        ];
    }

    public function toTextReport(): string
    {
        $summary = $this->getReportSummary();

        $lines = [
            'LAPORAN IMPORT SERTIFIKAT MANUAL',
            'Waktu: ' . now()->format('Y-m-d H:i:s'),
            'Total diproses: ' . $summary['processed_rows'],
            'Berhasil diimport: ' . $summary['imported_rows'],
            'Dilewati: ' . $summary['skipped_rows'],
            '',
            'Ringkasan alasan skip:',
        ];

        foreach ($summary['skip_reasons'] as $reason => $count) {
            $lines[] = "- {$reason}: {$count}";
        }

        if (empty($summary['skip_reasons'])) {
            $lines[] = '- Tidak ada';
        }

        $lines[] = '';
        $lines[] = 'Contoh baris skip:';

        foreach ($summary['sample_skipped'] as $item) {
            $lines[] = "- Baris {$item['row']} | {$item['name']} | {$item['reason']}";
        }

        if (empty($summary['sample_skipped'])) {
            $lines[] = '- Tidak ada';
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
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

    protected function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{name:string,srn:?string,study_program:?string,scores:array<string,float>,semester:?int,reason:?string}
     */
    protected function analyzeRow(array $row): array
    {
        $map = $this->resolveColumnMap($row);
        $name = trim((string) ($row[4] ?? ''));

        if ($name === '') {
            return [
                'name' => '',
                'srn' => null,
                'study_program' => null,
                'scores' => [],
                'semester' => null,
                'reason' => 'Nama kosong',
            ];
        }

        $prediction = strtoupper(trim((string) ($row[$map['prediction']] ?? '')));
        $letterGrade = strtoupper(trim((string) ($row[$map['letter_grade']] ?? '')));
        if ($prediction === 'FAIL' || $letterGrade === 'E') {
            return [
                'name' => $name,
                'srn' => null,
                'study_program' => null,
                'scores' => [],
                'semester' => null,
                'reason' => 'Status FAIL / grade E',
            ];
        }

        $srn = trim((string) ($row[3] ?? ''));
        $studyProgram = null;
        if ($map['study_program'] !== null) {
            $studyProgram = trim((string) ($row[$map['study_program']] ?? ''));
            $studyProgram = $studyProgram !== '' ? $studyProgram : null;
        }

        $scores = [
            'listening' => $this->parseScore($row[$map['scores']['listening']] ?? null),
            'speaking' => $this->parseScore($row[$map['scores']['speaking']] ?? null),
            'reading' => $this->parseScore($row[$map['scores']['reading']] ?? null),
            'writing' => $this->parseScore($row[$map['scores']['writing']] ?? null),
            'phonetics' => $this->parseScore($row[$map['scores']['phonetics']] ?? null),
            'vocabulary' => $this->parseScore($row[$map['scores']['vocabulary']] ?? null),
            'structure' => $this->parseScore($row[$map['scores']['structure']] ?? null),
        ];

        $scores = array_filter($scores, fn ($v) => $v !== null && $v > 0);

        if (empty($scores)) {
            return [
                'name' => $name,
                'srn' => $srn !== '' ? $srn : null,
                'study_program' => $studyProgram,
                'scores' => [],
                'semester' => null,
                'reason' => 'Nilai tidak valid / semua 0',
            ];
        }

        return [
            'name' => $name,
            'srn' => $srn !== '' ? $srn : null,
            'study_program' => $studyProgram,
            'scores' => $scores,
            'semester' => $this->parseSemester($row[$map['semester']] ?? null),
            'reason' => null,
        ];
    }

    protected function getSkipReasonCounts(): array
    {
        $counts = [];

        foreach ($this->skippedDetails as $detail) {
            $reason = $detail['reason'];
            $counts[$reason] = ($counts[$reason] ?? 0) + 1;
        }

        arsort($counts);

        return $counts;
    }

    /**
     * @return array{
     *   study_program:?int,
     *   prediction:int,
     *   letter_grade:int,
     *   semester:int,
     *   scores:array{listening:int,speaking:int,reading:int,writing:int,phonetics:int,vocabulary:int,structure:int}
     * }
     */
    protected function resolveColumnMap(array $row): array
    {
        $hasProdiColumn = $this->hasProdiColumn($row);

        if ($hasProdiColumn) {
            return [
                'study_program' => 5,
                'prediction' => 19,
                'letter_grade' => 15,
                'semester' => 18,
                'scores' => [
                    'listening' => 6,
                    'speaking' => 7,
                    'reading' => 8,
                    'writing' => 9,
                    'phonetics' => 10,
                    'vocabulary' => 11,
                    'structure' => 12,
                ],
            ];
        }

        return [
            'study_program' => null,
            'prediction' => 18,
            'letter_grade' => 14,
            'semester' => 17,
            'scores' => [
                'listening' => 5,
                'speaking' => 6,
                'reading' => 7,
                'writing' => 8,
                'phonetics' => 9,
                'vocabulary' => 10,
                'structure' => 11,
            ],
        ];
    }

    protected function hasProdiColumn(array $row): bool
    {
        $col5 = trim((string) ($row[5] ?? ''));

        // Strong signal: semester at col 18 and prediction text at col 19.
        if ($this->parseSemester($row[18] ?? null) !== null && $this->isPredictionValue($row[19] ?? null)) {
            return true;
        }

        // Fallback signal: col 5 is non-score text (usually study program).
        if ($col5 !== '' && $this->parseScore($col5) === null && $col5 !== '###') {
            return true;
        }

        return false;
    }

    protected function isPredictionValue(mixed $value): bool
    {
        $prediction = strtoupper(trim((string) $value));

        if ($prediction === '') {
            return false;
        }

        return in_array($prediction, [
            'EXCELLENT',
            'VERY GOOD',
            'GOOD',
            'ENOUGH',
            'FAIL',
            'BAD',
            'VERY BAD',
        ], true);
    }
}
