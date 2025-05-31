<?php

namespace App\Http\Controllers;

use App\Models\MasterGrupTes;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakGrupTesController extends Controller
{
    public function cetak($id)
    {
        $grup = MasterGrupTes::with(['pendaftaranGrupTes.pendaftaranEpt.users'])->findOrFail($id);
        $pesertas = $grup->pendaftaranGrupTes;

        $pdf = Pdf::loadView('pdf.data-peserta-grup', compact('grup', 'pesertas'))
            ->setPaper('a4', 'portrait'); // bisa ubah ke ['210mm', '330mm'] untuk F4

        return $pdf->stream("Daftar_Peserta_Grup_{$grup->nomor_grup}.pdf");
    }
}
