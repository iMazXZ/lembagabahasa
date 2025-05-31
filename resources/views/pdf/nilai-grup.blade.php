<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EPT Score</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
        }
        th, td {
            border: 1px solid black;
            padding: 4px 6px;
            text-align: center;
        }
        .no-border {
            border: none;
        }
        .title {
            font-size: 18px;
            text-align: center;
            font-weight: bold;
        }
        .mt-2 {
            margin-top: 2px;
        }
        .signature {
            margin-top: 30px;
            text-align: right;
        }
        .note {
            font-size: 12px;
            margin-top: 12px;
        }
        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>

    <div class="title">
        <h3>EPT SCORE<br>MUHAMMADIYAH UNIVERSITY OF METRO<br>LANGUAGE INSTITUTE</h3>
    </div>

    <table class="mt-2">
        <tr>
            <td class="no-border" style="text-align: left;">Group: {{ $grup->group_number }}/{{ $grup->kelas ?? 'Reguler Class' }}</td>
            <td class="no-border" style="text-align: right;">Instructional Year: Odd 2024/2025</td>
        </tr>
    </table>

    <table class="mt-2">
        <thead>
            <tr>
                <th>NO</th>
                <th>NAME</th>
                <th>R</th>
                <th>LISTENING<br>COMPREHENSION</th>
                <th>STRUCTURE AND<br>WRITTEN EXPRESSION</th>
                <th>READING<br>COMPREHENSION</th>
                <th>TOTAL<br>SCORE</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grup->pendaftaranGrupTes as $index => $peserta)
                @php
                    $user = $peserta->pendaftaranEpt->users;
                    $nilai = $peserta->dataNilaiTes;
                    $total = optional($nilai)->listening_comprehension + optional($nilai)->structure_written_expr + optional($nilai)->reading_comprehension;
                @endphp
                <tr>
                    <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td style="text-align: left;">{{ $user->name }}</td>
                    <td>{{ $nilai?->rank === 'Pass' ? 'P' : ($nilai?->rank === 'Fail' ? 'F' : '-') }}</td>
                    <td>{{ $nilai->listening_comprehension ?? '-' }}</td>
                    <td>{{ $nilai->structure_written_expr ?? '-' }}</td>
                    <td>{{ $nilai->reading_comprehension ?? '-' }}</td>
                    <td>{{ $total ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="note">
        Sertifikat EPT dapat diambil sebulan kemudian setelah pengumuman ini dgn menunjukkan KWITANSI EPT ke petugas pada jam kerja di <br>
        Gedung Laboratorium Bahasa Universitas Muhammadiyah Metro <br>
        R = Recommendation : P = Pass/Lulus, F = Fail/Gagal
    </div>

    <div class="signature">
        Metro, {{ \Carbon\Carbon::parse($grup->tanggal)->format('M d, Y') }} <br>
        Proctor <br><br><br><br><br>
        <strong>Khuldin Kusairi, S.Pd</strong>
    </div>

</body>
</html>
