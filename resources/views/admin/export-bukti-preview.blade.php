<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Preview Export Bukti Pembayaran</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        .row-container { min-height: 80px; }
        .item-card { transition: all 0.15s; }
        .item-card:hover { transform: scale(1.02); }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-6 max-w-6xl">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Layout Designer - Export Bukti</h1>
                    <p class="text-sm text-gray-500">Drag gambar ke baris • Atur kolom per baris • {{ $records->count() }} gambar</p>
                </div>
                <a href="{{ url('/admin/penerjemahan') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Available Items (Source) --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-gray-700"><i class="fa-solid fa-images mr-2"></i>Gambar Tersedia</h2>
                <button onclick="autoDistribute()" class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded text-sm font-medium hover:bg-blue-200">
                    <i class="fa-solid fa-magic mr-1"></i> Auto Susun
                </button>
            </div>
            <div id="source-items" class="flex flex-wrap gap-2 min-h-[60px] p-2 bg-gray-50 rounded border-2 border-dashed border-gray-200">
                @foreach($records as $record)
                    <div class="item-card bg-white border rounded-lg p-2 cursor-move shadow-sm flex items-center gap-2" 
                         data-id="{{ $record->id }}" 
                         data-name="{{ $record->users?->name ?? '-' }}"
                         data-srn="{{ $record->users?->srn ?? '-' }}"
                         data-date="{{ $record->created_at?->format('d/m/Y') ?? '-' }}">
                        @if(Storage::disk('public')->exists($record->bukti_pembayaran))
                            <img src="{{ Storage::disk('public')->url($record->bukti_pembayaran) }}" class="w-12 h-12 object-cover rounded">
                        @endif
                        <div class="text-xs">
                            <div class="font-medium truncate max-w-[150px]">{{ Str::limit($record->users?->name ?? '-', 20) }}</div>
                            <div class="text-gray-500">{{ $record->users?->srn ?? '-' }}</div>
                            <div class="text-gray-400 text-[10px]">{{ $record->created_at?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Rows Layout --}}
        <div id="rows-container" class="space-y-4 mb-6">
            {{-- Rows will be added dynamically --}}
        </div>

        {{-- Add Row Button --}}
        <div class="flex justify-center mb-6">
            <button onclick="addRow()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium hover:bg-gray-300 transition">
                <i class="fa-solid fa-plus mr-2"></i> Tambah Baris
            </button>
        </div>

        {{-- PDF Settings & Download --}}
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <span class="font-medium text-gray-700">Baris per Halaman:</span>
                    <select id="rowsPerPage" class="border rounded-lg px-3 py-2 text-sm">
                        <option value="2">2 Baris</option>
                        <option value="3" selected>3 Baris</option>
                        <option value="4">4 Baris</option>
                        <option value="5">5 Baris</option>
                    </select>
                </div>
                <button type="button" onclick="previewPdf()" id="downloadBtn" class="px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition disabled:opacity-50">
                    <i class="fa-solid fa-eye mr-2"></i> Preview PDF
                </button>
            </div>
        </div>

        {{-- Loading Overlay --}}
        <div id="loading" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg p-6 text-center">
                <div class="animate-spin w-10 h-10 border-4 border-green-600 border-t-transparent rounded-full mx-auto mb-4"></div>
                <p class="text-gray-700">Membuat PDF...</p>
            </div>
        </div>
    </div>

    <script>
        let rowId = 0;
        let sourceSortable;
        let rowSortables = {};
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize source sortable
            sourceSortable = new Sortable(document.getElementById('source-items'), {
                group: 'items',
                animation: 150,
                ghostClass: 'opacity-50',
            });
            
            // Add initial row
            addRow();
        });
        
        function addRow(columns = 2) {
            const container = document.getElementById('rows-container');
            const id = ++rowId;
            
            const rowHtml = `
                <div class="row-wrapper bg-white rounded-lg shadow p-4" data-row-id="${id}">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-700">Baris ${id}</span>
                            <div class="flex border rounded overflow-hidden">
                                <button onclick="setRowColumns(${id}, 1)" class="col-btn px-2 py-1 text-xs ${columns === 1 ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}" data-col="1">1 Kol</button>
                                <button onclick="setRowColumns(${id}, 2)" class="col-btn px-2 py-1 text-xs ${columns === 2 ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}" data-col="2">2 Kol</button>
                                <button onclick="setRowColumns(${id}, 3)" class="col-btn px-2 py-1 text-xs ${columns === 3 ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}" data-col="3">3 Kol</button>
                            </div>
                        </div>
                        <button onclick="removeRow(${id})" class="text-red-400 hover:text-red-600 text-sm">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                    <div id="row-${id}" class="row-container grid grid-cols-${columns} gap-2 p-3 bg-gray-50 rounded border-2 border-dashed border-gray-300 min-h-[80px]" data-columns="${columns}">
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', rowHtml);
            
            // Initialize sortable for the new row
            rowSortables[id] = new Sortable(document.getElementById(`row-${id}`), {
                group: 'items',
                animation: 150,
                ghostClass: 'opacity-50',
            });
        }
        
        function setRowColumns(rowId, columns) {
            const rowEl = document.getElementById(`row-${rowId}`);
            const wrapper = rowEl.closest('.row-wrapper');
            
            // Update grid classes
            rowEl.classList.remove('grid-cols-1', 'grid-cols-2', 'grid-cols-3');
            rowEl.classList.add(`grid-cols-${columns}`);
            rowEl.dataset.columns = columns;
            
            // Update buttons
            wrapper.querySelectorAll('.col-btn').forEach(btn => {
                if (parseInt(btn.dataset.col) === columns) {
                    btn.className = 'col-btn px-2 py-1 text-xs bg-blue-500 text-white';
                } else {
                    btn.className = 'col-btn px-2 py-1 text-xs bg-gray-100 text-gray-600 hover:bg-gray-200';
                }
            });
        }
        
        function removeRow(id) {
            const wrapper = document.querySelector(`[data-row-id="${id}"]`);
            const rowEl = document.getElementById(`row-${id}`);
            
            // Move items back to source
            const items = rowEl.querySelectorAll('.item-card');
            const source = document.getElementById('source-items');
            items.forEach(item => source.appendChild(item));
            
            // Remove row
            wrapper.remove();
            delete rowSortables[id];
        }
        
        function autoDistribute() {
            const source = document.getElementById('source-items');
            const items = Array.from(source.querySelectorAll('.item-card'));
            
            if (items.length === 0) return;
            
            // Clear existing rows
            document.querySelectorAll('.row-wrapper').forEach(row => {
                const rowEl = row.querySelector('.row-container');
                const rowItems = rowEl.querySelectorAll('.item-card');
                rowItems.forEach(item => source.appendChild(item));
                row.remove();
            });
            rowId = 0;
            rowSortables = {};
            
            // Create rows with 2 items each (2 columns)
            const itemsPerRow = 2;
            for (let i = 0; i < items.length; i += itemsPerRow) {
                addRow(2);
                const rowEl = document.getElementById(`row-${rowId}`);
                for (let j = i; j < Math.min(i + itemsPerRow, items.length); j++) {
                    rowEl.appendChild(items[j]);
                }
            }
        }
        
        function previewPdf() {
            const rows = [];
            
            document.querySelectorAll('.row-container').forEach(rowEl => {
                const columns = parseInt(rowEl.dataset.columns);
                const items = Array.from(rowEl.querySelectorAll('.item-card')).map(item => item.dataset.id);
                
                if (items.length > 0) {
                    rows.push({ columns, items });
                }
            });
            
            if (rows.length === 0) {
                alert('Tidak ada gambar yang diatur. Drag gambar ke baris terlebih dahulu.');
                return;
            }
            
            const rowsPerPage = document.getElementById('rowsPerPage').value;
            const downloadBtn = document.getElementById('downloadBtn');
            const loading = document.getElementById('loading');
            
            downloadBtn.disabled = true;
            loading.classList.remove('hidden');
            
            // Create form and submit in new tab
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.export-bukti.generate") }}';
            form.target = '_blank'; // Open in new tab
            form.style.display = 'none';
            
            // CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfInput);
            
            // Rows data as JSON
            const rowsInput = document.createElement('input');
            rowsInput.type = 'hidden';
            rowsInput.name = 'rows';
            rowsInput.value = JSON.stringify(rows);
            form.appendChild(rowsInput);
            
            // Rows per page
            const rppInput = document.createElement('input');
            rppInput.type = 'hidden';
            rppInput.name = 'rows_per_page';
            rppInput.value = rowsPerPage;
            form.appendChild(rppInput);
            
            document.body.appendChild(form);
            form.submit();
            
            setTimeout(() => {
                loading.classList.add('hidden');
                downloadBtn.disabled = false;
            }, 1500);
        }
    </script>
</body>
</html>
