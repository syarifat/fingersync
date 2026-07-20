<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi AIS Semester</title>
    @php
        $numMapel = count($mapelList);
        if ($numMapel > 10) {
            $fontSizeBody = '5.5px';
            $fontSizeTh = '5.5px';
            $fontSizeMapel = '5.5px';
            $cellPadding = '2px 0.5px';
            $noWidth = '18px';
            $namaWidth = '100px';
            $cellWidth = '9px';
            $totWidth = '12px';
            $totalAisWidth = '25px';
        } elseif ($numMapel > 7) {
            $fontSizeBody = '6.5px';
            $fontSizeTh = '6.5px';
            $fontSizeMapel = '6.5px';
            $cellPadding = '3px 1px';
            $noWidth = '22px';
            $namaWidth = '115px';
            $cellWidth = '11px';
            $totWidth = '14px';
            $totalAisWidth = '30px';
        } else {
            $fontSizeBody = '8px';
            $fontSizeTh = '7.5px';
            $fontSizeMapel = '7.5px';
            $cellPadding = '4px 2px';
            $noWidth = '25px';
            $namaWidth = '140px';
            $cellWidth = '13px';
            $totWidth = '18px';
            $totalAisWidth = '40px';
        }
    @endphp
    <style>
        @page {
            size: a4 landscape;
            margin: 15px 20px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: {{ $fontSizeBody }}; color: #1a1a1a; padding: 5px; }

        .header { text-align: center; margin-bottom: 10px; border-bottom: 2px solid #1a1a1a; padding-bottom: 5px; }
        .header h1 { font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { font-size: 9px; color: #555; margin-top: 2px; }

        .info-section { margin-bottom: 8px; font-size: 8.5px; color: #333; line-height: 1.3; }
        .info-section span { font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
        th, td { border: 1px solid #cbd5e1; text-align: center; padding: {{ $cellPadding }}; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        th { background: #f1f5f9; font-weight: bold; font-size: {{ $fontSizeTh }}; }
        .th-nama { text-align: left; padding-left: 4px; }
        
        td { font-size: {{ $fontSizeBody }}; }
        .td-nama { text-align: left; padding-left: 4px; font-weight: 500; }
        .td-cell { font-weight: bold; }
        
        /* Highlight styles */
        .highlight-zero { color: #94a3b8; font-weight: normal; }
        .highlight-some { background: #fee2e2; color: #b91c1c; }
        .total-col { background: #fff1f2; color: #be123c; font-weight: bold; font-size: {{ $fontSizeTh }}; }

        .legend { margin-top: 12px; font-size: 7px; color: #475569; border-top: 1px solid #e2e8f0; padding-top: 6px; line-height: 1.3; }
        .empty-msg { padding: 30px; text-align: center; color: #94a3b8; font-style: italic; font-size: 11px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>REKAPITULASI KETIDAKHADIRAN (AIS) SEMESTER</h1>
        <p>{{ $kelas->nama }} &nbsp;|&nbsp; {{ $semesterLabel }} &nbsp;|&nbsp; {{ $tahunAjarLabel }}</p>
    </div>

    <div class="info-section">
        <span>Kelas:</span> {{ $kelas->nama }} &nbsp;&nbsp;|&nbsp;&nbsp; 
        <span>Semester:</span> {{ $tahunAjar->semester ?? 'Ganjil' }} &nbsp;&nbsp;|&nbsp;&nbsp; 
        <span>Tahun Ajaran:</span> {{ $tahunAjar->tahun ?? '-' }}
    </div>

    @if(empty($matrix) || count($matrix) === 0)
        <div class="empty-msg">Tidak ada data presensi/siswa untuk kelas ini di semester ini.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: {{ $noWidth }}; vertical-align: middle;">No</th>
                    <th rowspan="2" class="th-nama" style="width: {{ $namaWidth }}; vertical-align: middle;">Nama Siswa</th>
                    @foreach($mapelList as $mapel)
                        <th colspan="4" style="font-size: {{ $fontSizeMapel }}; padding: 2px 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $mapel->nama }}">{{ $mapel->nama }}</th>
                    @endforeach
                    <th rowspan="2" style="width: {{ $totalAisWidth }}; vertical-align: middle;">Total AIS</th>
                </tr>
                <tr>
                    @foreach($mapelList as $mapel)
                        <th style="width: {{ $cellWidth }}; font-size: {{ $fontSizeTh }}; background: #f8fafc; font-weight: normal; color: #b91c1c;">A</th>
                        <th style="width: {{ $cellWidth }}; font-size: {{ $fontSizeTh }}; background: #f8fafc; font-weight: normal; color: #1d4ed8;">I</th>
                        <th style="width: {{ $cellWidth }}; font-size: {{ $fontSizeTh }}; background: #f8fafc; font-weight: normal; color: #c2410c;">S</th>
                        <th style="width: {{ $totWidth }}; font-size: {{ $fontSizeTh }}; background: #e2e8f0; font-weight: bold; color: #1e293b;">Tot</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($matrix as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="td-nama" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $row['nama'] }}</td>
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
