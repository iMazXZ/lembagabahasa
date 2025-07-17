<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analitik</title>
    <style>
        @page {
            /* Defines the space for the header and footer on every page */
            margin: 100px 30px 60px 30px;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #333;
        }

        /* This ensures the header is fixed within the top margin of every page */
        header {
            position: fixed;
            top: -85px; /* Pulls the header up into the margin area */
            left: 0;
            right: 0;
            height: 80px;
            text-align: center;
        }

        /* This ensures the footer is fixed within the bottom margin of every page */
        footer {
            position: fixed;
            bottom: -50px; /* Pulls the footer down into the margin area */
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
            margin-top: 15px; /* Reduced space above section titles */
            margin-bottom: 10px;
            text-align: left;
            font-weight: bold;
        }

        main {
            /* No margin needed here as @page handles the spacing */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 7px;
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
            background-color: #28a745; /* A slightly darker green */
        }

        .badge-gagal {
            background-color: #dc3545; /* A slightly darker red */
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
    <p class="meta-info">Tanggal Cetak: {{ $tanggal_cetak }} | Periode: {{ $periode_laporan }}</p>
</header>

<footer>
    Dicetak oleh Sistem Informasi Lembaga Bahasa UM Metro
</footer>

<main>

    <h3>1. Detail Pendaftar English Proficiency Test (EPT)</h3>
    <table>
        <thead>
            <tr>
                <th class="center" style="width: 4%;">No</th>
                <th>Nama</th>
                <th>NPM</th>
                <th>Program Studi</th>
                <th class="center">Listening</th>
                <th class="center">Structure</th>
                <th class="center">Reading</th>
                <th class="center" style="width: 10%;">Skor Total</th>
                <th class="center" style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendaftarEptDetails as $index => $pendaftar)
                @php
                    $prodi = $pendaftar->users->prody->name ?? '';
                    $isPBI = $prodi === 'Pendidikan Bahasa Inggris';
                    $passing_grade = $isPBI ? 500 : 400;
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $pendaftar->users->name ?? 'N/A' }}</td>
                    <td>{{ $pendaftar->users->srn ?? 'N/A' }}</td>
                    <td>{{ $prodi ?: 'N/A' }}</td>
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
                    <td colspan="9" class="center">Tidak ada data pendaftar EPT pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="page-break"></div>

    <h3>2. Detail Layanan Penerjemahan</h3>
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
                    <td>{{ $penerjemahan->created_at->format('d F Y') }}</td>
                    <td>
                        {{ $penerjemahan->status == 'Selesai' ? $penerjemahan->updated_at->format('d F Y') : '-' }}
                    </td>
                    <td>{{ $penerjemahan->translator->name ?? 'Belum Ditugaskan' }}</td>
                    <td class="center">{{ $penerjemahan->status }}</td>
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