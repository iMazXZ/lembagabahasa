<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Pembayaran Penerjemahan</title>
    <style>
        @page {
            size: legal portrait;
            margin: 5mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
        }
        
        .page {
            page-break-after: always;
        }
        
        .page:last-child {
            page-break-after: avoid;
        }
        
        .row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 1mm;
        }
        
        .cell {
            display: table-cell;
            padding: 1mm;
            vertical-align: top;
        }
        
        .cols-1 .cell { width: 100%; }
        .cols-2 .cell { width: 50%; }
        .cols-3 .cell { width: 33.33%; }
        
        .item {
            border: 1px solid #ddd;
            padding: 1mm;
        }
        
        .item-label {
            font-size: 6pt;
            color: #333;
            margin-bottom: 1mm;
        }
        
        .item-label strong {
            font-size: 8pt;
        }
        
        .item-image {
            text-align: center;
        }
        
        .cols-1 .item-image img {
            max-width: 100%;
            max-height: 70mm;
        }
        
        .cols-2 .item-image img {
            max-width: 100%;
            max-height: 90mm;
        }
        
        .cols-3 .item-image img {
            max-width: 100%;
            max-height: 80mm;
        }
    </style>
</head>
<body>
    @foreach($pages as $pageRows)
        <div class="page">
            @foreach($pageRows as $rowData)
                @php
                    $columns = $rowData['columns'];
                    $records = $rowData['records'];
                @endphp
                
                <div class="row cols-{{ $columns }}">
                    @foreach($records as $record)
                        <div class="cell">
                            <div class="item">
                                <div class="item-label">
                                    <strong>{{ Str::limit($record->users?->name ?? '-', 30) }}</strong> — {{ $record->users?->srn ?? '-' }}
                                </div>
                                <div class="item-image">
                                    @php
                                        $imagePath = $record->bukti_pembayaran;
                                        $hasImage = $imagePath && Storage::disk('public')->exists($imagePath);
                                        $imageData = null;
                                        
                                        if ($hasImage) {
                                            try {
                                                $fullPath = Storage::disk('public')->path($imagePath);
                                                $imageContent = file_get_contents($fullPath);
                                                $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
                                                $imageData = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                                            } catch (\Exception $e) {
                                                $hasImage = false;
                                            }
                                        }
                                    @endphp
                                    
                                    @if($hasImage && $imageData)
                                        <img src="{{ $imageData }}" alt="Bukti">
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    @for($i = $records->count(); $i < $columns; $i++)
                        <div class="cell"></div>
                    @endfor
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
