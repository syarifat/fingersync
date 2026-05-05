<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Absensi Siswa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1a1a1a; }

        .header { text-align: center; margin-bottom: 14px; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; }
        .header h1 { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header p { font-size: 10px; color: #555; margin-top: 3px; }

        .mapel-section { margin-bottom: 22px; page-break-inside: avoid; }
        .mapel-title {
            background: #1e293b;
            color: white;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
            border-radius: 4px 4px 0 0;
            margin-bottom: 0;
        }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #cbd5e1; text-align: center; }

        /* Header row */
        .th-no    { width: 28px; padding: 4px 2px; font-size: 9px; background: #f1f5f9; }
        .th-nama  { width: 130px; text-align: left; padding: 4px 6px; font-size: 9px; background: #f1f5f9; }
        .th-tgl   { width: 22px; padding: 4px 2px; font-size: 9px; background: #f1f5f9; }
        .th-total { width: 28px; padding: 4px 2px; font-size: 9px; background: #f1f5f9; }

        /* Data rows */
        .td-no   { padding: 3px 2px; font-size: 9px; color: #64748b; }
        .td-nama { padding: 3px 6px; text-align: left; font-size: 9px; font-weight: 500; white-space: nowrap; overflow: hidden; max-width: 130px; }
        .td-cell { padding: 3px 2px; font-size: 9px; font-weight: bold; }
        .td-total { padding: 3px 2px; font-size: 9px; }

        /* Status colors */
        .H { background: #dcfce7; color: #15803d; }   /* Hadir - hijau */
        .T { background: #fef9c3; color: #854d0e; }   /* Terlambat - kuning */
        .I { background: #dbeafe; color: #1d4ed8; }   /* Izin - biru */
        .S { background: #ffedd5; color: #c2410c; }   /* Sakit - oranye */
        .A { background: #fee2e2; color: #b91c1c; }   /* Alpa - merah */
        .dash { color: #d1d5db; }

        /* Legend */
        .legend { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; font-size: 8.5px; }
        .leg-item { display: flex; align-items: center; gap: 4px; }
        .leg-box { width: 14px; height: 14px; border-radius: 3px; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 8px; }

        .empty-msg { padding: 20px; text-align: center; color: #94a3b8; font-style: italic; }
    </style>
</head>
<body>

    <div class="header">
        <h1>REKAP ABSENSI BULANAN</h1>
        <p>{{ $kelas->nama }} &nbsp;|&nbsp; {{ $bulanLabel }}</p>
    </div>

    @forelse($dataPerMapel as $item)
    <div class="mapel-section">
        <div class="mapel-title">📚 {{ $item['nama_mapel'] }}</div>
        <table>
            <thead>
                <tr>
                    <th class="th-no">No</th>
                    <th class="th-nama">Nama Siswa</th>
                    @foreach($item['tanggal_aktif'] as $tgl)
                    <th class="th-tgl">{{ date('d', strtotime($tgl)) }}</th>
                    @endforeach
                    <th class="th-total">H</th>
                    <th class="th-total">T</th>
                    <th class="th-total">I</th>
                    <th class="th-total">S</th>
                    <th class="th-total">A</th>
                </tr>
            </thead>
            <tbody>
                @foreach($siswaList as $i => $siswa)
                <tr>
                    <td class="td-no">{{ $i + 1 }}</td>
                    <td class="td-nama">{{ $siswa->nama }}</td>
                    @php
                        $matrixRow = $item['matrix'][$siswa->id] ?? [];
                        $countH = 0; $countT = 0; $countI = 0; $countS = 0; $countA = 0;
                    @endphp
                    @foreach($item['tanggal_aktif'] as $tgl)
                        @php
                            $status = $matrixRow[$tgl] ?? '-';
                            if ($status == 'H') $countH++;
                            elseif ($status == 'T') $countT++;
                            elseif ($status == 'I') $countI++;
                            elseif ($status == 'S') $countS++;
                            elseif ($status == 'A') $countA++;
                        @endphp
                        <td class="td-cell {{ $status != '-' ? $status : '' }} {{ $status == '-' ? 'dash' : '' }}">
                            {{ $status }}
                        </td>
                    @endforeach
                    <td class="td-total H">{{ $countH ?: '' }}</td>
                    <td class="td-total T">{{ $countT ?: '' }}</td>
                    <td class="td-total I">{{ $countI ?: '' }}</td>
                    <td class="td-total S">{{ $countS ?: '' }}</td>
                    <td class="td-total A">{{ $countA ?: '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="empty-msg">Tidak ada data presensi yang cocok dengan filter yang dipilih.</div>
    @endforelse

    <div class="legend">
        <span><strong>Keterangan:</strong></span>
        <span class="leg-item"><span class="leg-box H">H</span> Hadir</span>
        <span class="leg-item"><span class="leg-box T">T</span> Terlambat</span>
        <span class="leg-item"><span class="leg-box I">I</span> Izin</span>
        <span class="leg-item"><span class="leg-box S">S</span> Sakit</span>
        <span class="leg-item"><span class="leg-box A">A</span> Alpa</span>
        <span class="leg-item">- = Tidak ada sesi</span>
    </div>

</body>
</html>
