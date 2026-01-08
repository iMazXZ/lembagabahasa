<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Penerjemahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
        
        // Increase memory limit for large exports
        ini_set('memory_limit', '512M');
        set_time_limit(120);
        
        $request->validate([
            'rows' => 'required|string',
            'rows_per_page' => 'nullable|integer|min:1|max:10',
        ]);
        
        $rowsData = json_decode($request->input('rows'), true);
        $rowsPerPage = (int) $request->input('rows_per_page', 3);
        
        if (empty($rowsData)) {
            return response()->json(['error' => 'Tidak ada data'], 400);
        }
        
        // Count total items and limit to prevent memory issues (max 8 for 128MB server with high quality)
        $totalItems = collect($rowsData)->sum(fn($r) => count($r['items'] ?? []));
        if ($totalItems > 8) {
            return response()->json([
                'error' => "Terlalu banyak gambar ($totalItems). Maksimum 8 gambar per export dengan kualitas tinggi. Silakan export dalam batch lebih kecil."
            ], 400);
        }
        
        // Process rows - fetch records and pre-process images
        $processedRows = [];
        $manager = new ImageManager(new Driver());
        
        foreach ($rowsData as $row) {
            $columns = (int) ($row['columns'] ?? 2);
            $itemIds = $row['items'] ?? [];
            
            // Fetch records and process images
            $processedRecords = [];
            foreach ($itemIds as $id) {
                $record = Penerjemahan::with(['users', 'users.prody'])->find($id);
                
                if (!$record || !filled($record->bukti_pembayaran)) {
                    continue;
                }
                
                if (!Storage::disk('public')->exists($record->bukti_pembayaran)) {
                    continue;
                }
                
                // Process and compress image
                $imageData = $this->processImage(
                    $manager,
                    Storage::disk('public')->path($record->bukti_pembayaran),
                    $columns
                );
                
                $processedRecords[] = [
                    'name' => $record->users?->name ?? '-',
                    'srn' => $record->users?->srn ?? '-',
                    'imageData' => $imageData,
                ];
            }
            
            if (!empty($processedRecords)) {
                $processedRows[] = [
                    'columns' => $columns,
                    'items' => $processedRecords,
                ];
            }
            
            // Clear memory after each row
            gc_collect_cycles();
        }
        
        if (empty($processedRows)) {
            return response()->json(['error' => 'Tidak ada data valid'], 400);
        }
        
        // Split rows into pages
        $pages = array_chunk($processedRows, $rowsPerPage);
        
        $pdf = Pdf::loadView('exports.penerjemahan-bukti-rows-pdf', [
            'pages' => $pages,
        ])
        ->setPaper('legal', 'portrait')
        ->setOption('isRemoteEnabled', true);
        
        $filename = 'Bukti_Pembayaran_' . now()->format('Ymd_His') . '.pdf';
        
        return $pdf->stream($filename);
    }
    
    /**
     * Process and compress image for PDF embedding - balanced optimization
     */
    private function processImage(ImageManager $manager, string $filePath, int $columns): ?string
    {
        try {
            // High quality: readable but with reasonable resize
            $maxWidth = match($columns) {
                1 => 700,
                2 => 500,
                3 => 380,
                default => 500,
            };
            $maxHeight = match($columns) {
                1 => 600,
                2 => 450,
                3 => 350,
                default => 450,
            };
            
            // Read image
            $image = $manager->read($filePath);
            
            // Resize to fit within bounds (maintain aspect ratio)
            $image->scaleDown($maxWidth, $maxHeight);
            
            // Encode as JPEG - HIGH quality 85%
            $encoded = $image->toJpeg(85);
            
            // Convert to base64
            $base64 = base64_encode($encoded->toString());
            
            // Free memory immediately
            unset($image, $encoded);
            
            return 'data:image/jpeg;base64,' . $base64;
            
        } catch (\Exception $e) {
            return null;
        }
    }
}
