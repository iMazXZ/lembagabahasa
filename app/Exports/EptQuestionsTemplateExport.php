<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EptQuestionsTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Listening' => new EptQuestionsTemplateSheet('listening'),
            'Structure' => new EptQuestionsTemplateSheet('structure'),
            'Reading' => new EptQuestionsTemplateSheet('reading'),
        ];
    }
}
