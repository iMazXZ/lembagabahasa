<?php

namespace App\Imports;

use App\Models\EptQuestion;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EptQuestionsSheetImport implements ToModel, WithHeadingRow, WithValidation
{
    protected int $quizId;
    protected string $section;
    protected int $order = 0;

    public function __construct(int $quizId, string $section)
    {
        $this->quizId = $quizId;
        $this->section = $section;
    }

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['question'])) {
            return null;
        }
        
        $this->order++;

        return new EptQuestion([
            'quiz_id' => $this->quizId,
            'section' => $this->section,
            'order' => $this->order,
            'question' => $row['question'] ?? '',
            'option_a' => $row['option_a'] ?? '',
            'option_b' => $row['option_b'] ?? '',
            'option_c' => $row['option_c'] ?? '',
            'option_d' => $row['option_d'] ?? '',
            'correct_answer' => strtoupper(trim($row['correct_answer'] ?? 'A')),
            'passage' => $row['passage'] ?? null,
            'passage_group' => $row['passage_group'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'question' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:A,B,C,D,a,b,c,d',
            'passage' => 'nullable|string',
            'passage_group' => 'nullable|string',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'correct_answer.in' => 'Correct answer harus: A, B, C, atau D',
        ];
    }
}
