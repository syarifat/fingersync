<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi AIS Semester</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1a1a1a; padding: 20px; }

        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; }
        .header h1 { font-size: 16px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header p { font-size: 11px; color: #555; margin-top: 3px; }

        .info-section { margin-bottom: 15px; font-size: 11px; color: #333; }
        .info-section span { font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; text-align: center; padding: 6px 4px; }
        
        th { background: #f1f5f9; font-weight: bold; font-size: 9px; }
        .th-nama { text-align: left; padding-left: 8px; }
        
        td { font-size: 8.5px; }
        .td-nama { text-align: left; padding-left: 8px; font-weight: 500; }
        .td-cell { font-weight: bold; }
        
        /* Highlight styles */
        .highlight-zero { color: #94a3b8; font-weight: normal; }
        .highlight-some { background: #fee2e2; color: #b91c1c; }
        .total-col { background: #fff1f2; color: #be123c; font-weight: bold; font-size: 9px; }

        .legend { margin-top: 20px; font-size: 8.5px; color: #475569; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .empty-msg { padding: 30px; text-align: center; color: #94a3b8; font-style: italic; font-size: 11px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>REKAPITULASI KETIDAKHADIRAN (AIS) SEMESTER</h1>
        <p>{{ $kelas->nama }} &nbsp;|&nbsp; {{ $semesterLabel }} &nbsp;|&nbsp; {{ $tahunAjarLabel }}</p>
    </div>

    <div class="info-section">
        <span>Kelas:</span> {{ $kelas->nama }} <br>
        <span>Semester:</span> {{ $tahunAjar->semester ?? 'Ganjil' }} <br>
        <span>Tahun Ajaran:</span> {{ $tahunAjar->tahun ?? '-' }}
    </div>

    @if(empty($matrix) || count($matrix) === 0)
        <div class="empty-msg">Tidak ada data presensi/siswa untuk kelas ini di semester ini.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: 30px; vertical-align: middle;">No</th>
                    <th rowspan="2" class="th-nama" style="vertical-align: middle;">Nama Siswa</th>
                    @foreach($mapelList as $mapel)
                        <th colspan="4" style="font-size: 8px; padding: 4px 2px;">{{ $mapel->nama }}</th>
                    @endforeach
                    <th rowspan="2" style="width: 50px; vertical-align: middle;">Total AIS</th>
                </tr>
                <tr>
                    @foreach($mapelList as $mapel)
                        <th style="width: 15px; font-size: 7px; background: #f8fafc; font-weight: normal; color: #b91c1c;">A</th>
                        <th style="width: 15px; font-size: 7px; background: #f8fafc; font-weight: normal; color: #1d4ed8;">I</th>
                        <th style="width: 15px; font-size: 7px; background: #f8fafc; font-weight: normal; color: #c2410c;">S</th>
                        <th style="width: 20px; font-size: 7px; background: #e2e8f0; font-weight: bold; color: #1e293b;">Tot</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($matrix as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="td-nama">{{ $row['nama'] }}</td>
                        @foreach($mapelList as $mapel)
                            @php
                                $details = $row['mapel_ais'][$mapel->id] ?? ['A' => 0, 'I' => 0, 'S' => 0, 'Total' => 0];
                            @endphp
                            <td class="{{ $details['A'] > 0 ? 'highlight-some' : 'highlight-zero' }}">
                                {{ $details['A'] ?: '-' }}
                            </td>
                            <td class="{{ $details['I'] > 0 ? 'highlight-some' : 'highlight-zero' }}">
                                {{ $details['I'] ?: '-' }}
                            </td>
                            <td class="{{ $details['S'] > 0 ? 'highlight-some' : 'highlight-zero' }}">
                                {{ $details['S'] ?: '-' }}
                            </td>
                            <td class="td-cell {{ $details['Total'] > 0 ? 'highlight-some' : 'highlight-zero' }}" style="background: #f8fafc; font-weight: bold;">
                                {{ $details['Total'] ?: '-' }}
                            </td>
                        @endforeach
                        <td class="total-col">{{ $row['total_ais'] ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="legend">
            <strong>Catatan:</strong><br>
            - Setiap mata pelajaran dibagi menjadi 4 sub-kolom: <strong>A</strong> (Alpha/Alpa), <strong>I</strong> (Izin), <strong>S</strong> (Sakit), dan <strong>Tot</strong> (Total AIS per mata pelajaran).<br>
            - Kolom <strong>Total AIS</strong> di bagian akhir menunjukkan akumulasi total hari ketidakhadiran (A + I + S) dari seluruh mata pelajaran.<br>
            - Tanda (-) menunjukkan tidak ada riwayat ketidakhadiran pada kolom tersebut.
        </div>
    @endif

</body>
</html>
