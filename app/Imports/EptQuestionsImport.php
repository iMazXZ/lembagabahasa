<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EptQuestionsImport implements WithMultipleSheets
{
    protected int $quizId;

    public function __construct(int $quizId)
    {
        $this->quizId = $quizId;
    }

    public function sheets(): array
    {
        return [
            'Listening' => new EptQuestionsSheetImport($this->quizId, 'listening'),
            'Structure' => new EptQuestionsSheetImport($this->quizId, 'structure'),
            'Reading' => new EptQuestionsSheetImport($this->quizId, 'reading'),
        ];
    }
}
