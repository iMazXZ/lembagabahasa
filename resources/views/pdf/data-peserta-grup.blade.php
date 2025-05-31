<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Data EPT Examinees</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid black; padding: 4px; text-align: center; }
        .no-border {border: none;}
        .header { font-size: 18px; text-align: center; margin-bottom: 10px; }
        .note, .footer { font-size: 13px; margin-top: 10px; }
        .footer ol { margin: 5px 0; padding-left: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h3>DATA OF EPT EXAMINEES<br>{{ strtoupper(config('app.name')) }} LANGUAGE INSTITUTE</h3>
    </div>

    <table class="mt-2">
        <tr>
            <td class="no-border" style="text-align: left;">Group: {{ $grup->group_number }}/{{ $grup->kelas ?? 'Reguler Class' }}</td>
            <td class="no-border" style="text-align: right;">Instructional Year: {{ $grup->instructional_year }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Names</th>
                <th>R/C/M/G</th>
                <th>Year</th>
                <th>SRN</th>
                <th>PRODY</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pesertas as $index => $peserta)
                <tr>
                    <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $peserta->pendaftaranEpt->users->name }}</td>
                    <td>R</td>
                    <td>{{ $peserta->pendaftaranEpt->users->year }}</td>
                    <td>{{ $peserta->pendaftaranEpt->users->srn }}</td>
                    <td>{{ $peserta->pendaftaranEpt->users->prody->name }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="note">
        <em>Peserta ujian agar memperhatikan jadwal ujian. Jika setelah pengumuman ini ditetapkan, YBS tidak hadir maka YBS dianggap gugur dan harus daftar ulang lagi.</em>
    </p>

    <div class="footer">
        <strong>PAY ATTENTION/PERHATIKAN SBB</strong>
        <ol>
            <li>Ketika ujian EPT/masuk Lab. Bahasa :
                <ol type="a">
                    <li>Hp/alat elektronik lain di non aktifkan</li>
                    <li>Menunjukkan bukti pembayaran dan stempel LB + KTM</li>
                    <li>Memakai pakaian yang sopan</li>
                    <li>Duduk sesuai nomor urut</li>
                    <li>Wajib menggunakan masker</li>
                </ol>
            </li>
            <li>Proctor : .............. Ass. Lab : ..............</li>
            <li>Jadwal Ujian :<br>
                Hari, Tanggal : <b>{{ \Carbon\Carbon::parse($grup->tanggal_tes)->translatedFormat('l, d F Y') }}</b><br>
                Pukul : {{ \Carbon\Carbon::parse($grup->tanggal_tes)->format('H.i') }} - selesai<br>
                Ruangan : {{ $grup->ruangan_tes }}
            </li>
            <li>Hal yang kurang jelas dapat ditanyakan langsung Pada bagian Pendaftaran EPT di Lembaga Bahasa UM Metro</li>
        </ol>
    </div>
</body>
</html>
