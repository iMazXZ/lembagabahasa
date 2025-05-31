<?php

namespace App\Http\Controllers;

use App\Models\MasterGrupTes;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakNilaiGrupController extends Controller
{
    public function cetak($id)
    {
        $grup = MasterGrupTes::with([
            'pendaftaranGrupTes.pendaftaranEpt.users',
            'pendaftaranGrupTes.dataNilaiTes'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.nilai-grup', compact('grup'))->setPaper('a4', 'portrait');

        return $pdf->stream('ept_nilai_grup_' . $grup->group_number . '.pdf');
    }
}