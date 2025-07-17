<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analitik</title>
    <style>
        @page {
            /* Menentukan ruang untuk header dan footer di setiap halaman */
            margin: 100px 30px 60px 30px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 9px; /* Ukuran font dikecilkan sedikit agar muat */
            color: #333;
        }

        /* Memastikan header tetap di dalam margin atas setiap halaman */
        header {
            position: fixed;
            top: -85px; /* Menarik header ke area margin atas */
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
        }

        /* Memastikan footer tetap di dalam margin bawah setiap halaman */
        footer {
            position: fixed;
            bottom: -50px; /* Menarik footer ke area margin bawah */
            left: 0;
            right: 0;
            height: 40px;
            text-align: center;
            font-size: 9px;
            color: #777;
        }

        h1 {
            font-size: 16px;
            margin: 0;
            font-weight: bold;
        }

        h2 {
            font-size: 14px;
            margin: 5px 0;
        }

        .meta-info {
            font-size: 10px;
            margin-top: 5px;
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            padding: 5px 0;
        }

        h3 {
            font-size: 13px;
            margin-top: 15px;
            margin-bottom: 10px;
            text-align: left;
            font-weight: bold;
        }

        main {
            /* Tidak perlu margin karena @page sudah mengatur jarak */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        td.center, th.center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 9px;
            font-weight: bold;
            border-radius: 4px;
            color: #fff;
        }

        .badge-lulus {
            background-color: #28a745;
        }

        .badge-gagal {
            background-color: #dc3545;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

<header>
    <h1>LAPORAN ANALITIK</h1>
    <h2>LEMBAGA BAHASA UNIVERSITAS MUHAMMADIYAH METRO</h2>
    <p class="meta-info">Tanggal Cetak: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }} | Periode: {{ $periode_laporan }}</p>
</header>

<footer>
    Dicetak oleh Sistem Informasi Lembaga Bahasa UM Metro
</footer>

<main>

    <h3>Pendaftar English Proficiency Test (EPT)</h3>
    <table>
        <thead>
            <tr>
                <th class="center" style="width: 4%;">No</th>
                <th>Nama</th>
                <th class="center" style="width: 3%;">NPM</th>
                <th class="center">Prodi</th>
                <th class="center">Tgl. Daftar</th>
                <th class="center" style="width: 3%;">Grup</th>
                <th class="center" style="width: 3%;">Listening</th>
                <th class="center" style="width: 3%;">Structure</th>
                <th class="center" style="width: 3%;">Reading</th>
                <th class="center" style="width: 8%;">Skor Total</th>
                <th class="center" style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendaftarEptDetails as $index => $pendaftar)
                @php
                    $prodi = $pendaftar->users->prody->name ?? '';
                    // Logika kelulusan sesuai permintaan
                    $isPBI = $prodi === 'Pendidikan Bahasa Inggris';
                    $passing_grade = $isPBI ? 500 : 400;
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $pendaftar->users->name ?? 'N/A' }}</td>
                    <td class="center">{{ $pendaftar->users->srn ?? 'N/A' }}</td>
                    <td class="center">{{ $prodi ?: 'N/A' }}</td>
                    <td class="center">{{ $pendaftar->created_at->locale('id')->translatedFormat('d M Y') }}</td>
                    <td class="center">{{ $pendaftar->pendaftaranGrupTes->first()?->masterGrupTes?->group_number ?? '-' }}</td>
                    <td class="center">{{ $pendaftar->listening_comprehension }}</td>
                    <td class="center">{{ $pendaftar->structure_written_expr }}</td>
                    <td class="center">{{ $pendaftar->reading_comprehension }}</td>
                    <td class="center">{{ $pendaftar->total_score }}</td>
                    <td class="center">
                        @if($pendaftar->total_score != '-')
                            @if($pendaftar->total_score >= $passing_grade)
                                <span class="badge badge-lulus">LULUS</span>
                            @else
                                <span class="badge badge-gagal">GAGAL</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="center">Tidak ada data pendaftar EPT pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <h3>Penerjemahan Dokumen Abstrak</h3>
    <table>
        <thead>
            <tr>
                <th class="center" style="width: 4%;">No</th>
                <th>Pemohon</th>
                <th>Tanggal Upload</th>
                <th>Tanggal Selesai</th>
                <th>Diterjemahkan Oleh</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPenerjemahan as $index => $penerjemahan)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $penerjemahan->users->name ?? 'N/A' }}</td>
                    <td>{{ $penerjemahan->created_at->locale('id')->translatedFormat('d F Y') }}</td>
                    <td>
                        {{ $penerjemahan->status == 'Selesai' ? $penerjemahan->updated_at->locale('id')->translatedFormat('d F Y') : '-' }}
                    </td>
                    <td>{{ $penerjemahan->translator->name ?? 'Belum Ditugaskan' }}</td>
                    <td class="center">
                        {{-- ============================================== --}}
                        {{-- == PERUBAHAN LOGIKA UNTUK DOWNLOAD DI SINI == --}}
                        {{-- ============================================== --}}
                        @if($penerjemahan->status == 'Selesai' && !empty($penerjemahan->dokumen_terjemahan))
                            <a href="{{ Storage::url($penerjemahan->dokumen_terjemahan) }}" target="_blank" style="color: green; text-decoration: underline;">
                                Selesai
                            </a>
                        @else
                            {{ $penerjemahan->status }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Tidak ada data penerjemahan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</main>

</body>
</html>