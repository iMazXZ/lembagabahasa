<!DOCTYPE html>
<html>
<head>
    <title>Laporan Analitik</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; }
        h1, h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <h1>Laporan Analitik</h1>
    <h2>Lembaga Bahasa UM Metro</h2>
    <p>Tanggal Cetak: {{ $tanggal_cetak }}</p>
    {{-- TAMBAHKAN BARIS INI --}}
    <p>Periode Laporan: {{ $periode_laporan }}</p>
    
    <hr>

    <h3>Pendaftar EPT (12 Bulan Terakhir)</h3>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Jumlah Pendaftar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trenPendaftar as $data)
                <tr>
                    <td>{{ $data->bulan }}</td>
                    <td>{{ $data->jumlah }}</td>
                </tr>
            @empty
                <tr><td colspan="2">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Sebaran Pendaftar EPT per Program Studi</h3>
    <table>
        <thead>
            <tr>
                <th>Program Studi</th>
                <th>Jumlah Pendaftar</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sebaranProdi as $data)
                <tr>
                    <td>{{ $data->name }}</td>
                    <td>{{ $data->total }}</td>
                </tr>
            @empty
                <tr><td colspan="2">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h3>Rata-rata Skor EPT (12 Bulan Terakhir)</h3>
    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th>Rata-rata Skor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rataRataSkor as $data)
                <tr>
                    <td>{{ $data->bulan }}</td>
                    <td>{{ round($data->rerata_skor, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh Sistem Informasi Lembaga Bahasa
    </div>
</body>
</html>