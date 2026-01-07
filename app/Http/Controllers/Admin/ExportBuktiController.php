<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penerjemahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportBuktiController extends Controller
{
    public function preview(Request $request)
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi']),
            403
        );
        
        $ids = $request->input('ids', []);
        
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        
        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }
        
        $records = Penerjemahan::with(['users', 'users.prody'])
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn ($r) => filled($r->bukti_pembayaran) && Storage::disk('public')->exists($r->bukti_pembayaran));
        
        if ($records->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada bukti pembayaran yang tersedia.');
        }
        
        return view('admin.export-bukti-preview', [
            'records' => $records,
        ]);
    }
    
    public function generate(Request $request)
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['Admin', 'Staf Administrasi']),
            403
        );
        
        $request->validate([
            'rows' => 'required|string', // JSON string of rows
            'rows_per_page' => 'nullable|integer|min:1|max:10',
        ]);
        
        $rowsData = json_decode($request->input('rows'), true);
        $rowsPerPage = (int) $request->input('rows_per_page', 3);
        
        if (empty($rowsData)) {
            return response()->json(['error' => 'Tidak ada data'], 400);
        }
        
        // Process rows - fetch records and build structure
        $processedRows = [];
        foreach ($rowsData as $row) {
            $columns = (int) ($row['columns'] ?? 2);
            $itemIds = $row['items'] ?? [];
            
            // Fetch records in order
            $records = collect($itemIds)->map(fn ($id) => Penerjemahan::with(['users', 'users.prody'])->find($id))
                ->filter(fn ($r) => $r && filled($r->bukti_pembayaran) && Storage::disk('public')->exists($r->bukti_pembayaran));
            
            if ($records->isNotEmpty()) {
                $processedRows[] = [
                    'columns' => $columns,
                    'records' => $records,
                ];
            }
        }
        
        if (empty($processedRows)) {
            return response()->json(['error' => 'Tidak ada data valid'], 400);
        }
        
        // Split rows into pages based on rows_per_page setting
        $pages = array_chunk($processedRows, $rowsPerPage);
        
        $pdf = Pdf::loadView('exports.penerjemahan-bukti-rows-pdf', [
            'pages' => $pages,
        ])
        ->setPaper('legal', 'portrait')
        ->setOption('isRemoteEnabled', true);
        
        $filename = 'Bukti_Pembayaran_' . now()->format('Ymd_His') . '.pdf';
        
        // Stream = inline preview in browser (not download)
        return $pdf->stream($filename);
    }
}
