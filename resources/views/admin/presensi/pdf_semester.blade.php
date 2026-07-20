<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi AIS Semester</title>
    @php
        $numMapel = count($mapelList);
        // Tentukan apakah perlu dipecah ke 2 halaman
        $isSplit = $numMapel > 7;
        
        if ($isSplit) {
            $half = (int)ceil($numMapel / 2);
            $part1Mapels = $mapelList->slice(0, $half);
            $part2Mapels = $mapelList->slice($half);
        } else {
            $part1Mapels = $mapelList;
            $part2Mapels = collect();
        }

        // Optimasi lebar kolom: memperkecil sel AIS dan memperlebar kolom Nama Siswa
        $fontSizeBody = '8.5px';
        $fontSizeTh = '8px';
        $fontSizeMapel = '8px';
        $cellPadding = '4px 1px'; // Perkecil padding kiri-kanan agar teks angka muat di sel kecil
        $noWidth = '25px';
        $namaWidth = '220px'; // Diperlebar signifikan dari 160px agar nama tidak terpotong
        $cellWidth = '12px';  // Ukuran sel A, I, S
        $totalAisWidth = '45px';
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

        table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; margin-bottom: 15px; }
        th, td { border: 1px solid #cbd5e1; text-align: center; padding: {{ $cellPadding }}; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        
        th { background: #f1f5f9; font-weight: bold; font-size: {{ $fontSizeTh }}; }
        .th-nama { text-align: left; padding-left: 5px; }
        
        td { font-size: {{ $fontSizeBody }}; }
        .td-nama { text-align: left; padding-left: 5px; font-weight: 500; }
        .td-cell { font-weight: bold; }
        
        /* Highlight styles */
        .highlight-zero { color: #94a3b8; font-weight: normal; }
        .highlight-some { background: #fee2e2; color: #b91c1c; }
        .total-col { background: #fff1f2; color: #be123c; font-weight: bold; font-size: {{ $fontSizeTh }}; }

        .legend { margin-top: 15px; font-size: 7.5px; color: #475569; border-top: 1px solid #e2e8f0; padding-top: 8px; line-height: 1.3; }
        .empty-msg { padding: 30px; text-align: center; color: #94a3b8; font-style: italic; font-size: 11px; }
        
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    @if(empty($matrix) || count($matrix) === 0)
        <div class="header">
            <h1>REKAPITULASI KETIDAKHADIRAN (AIS) SEMESTER</h1>
            <p>{{ $kelas->nama }} &nbsp;|&nbsp; {{ $semesterLabel }} &nbsp;|&nbsp; {{ $tahunAjarLabel }}</p>
        </div>
        <div class="empty-msg">Tidak ada data presensi/siswa untuk kelas ini di semester ini.</div>
    @else
        <!-- BAGIAN 1 / HALAMAN 1 -->
        <div class="header">
            <h1>REKAPITULASI KETIDAKHADIRAN (AIS) SEMESTER @if($isSplit) (BAGIAN 1) @endif</h1>
            <p>{{ $kelas->nama }} &nbsp;|&nbsp; {{ $semesterLabel }} &nbsp;|&nbsp; {{ $tahunAjarLabel }}</p>
        </div>

        <div class="info-section">
            <span>Kelas:</span> {{ $kelas->nama }} &nbsp;&nbsp;|&nbsp;&nbsp; 
            <span>Semester:</span> {{ $tahunAjar->semester ?? 'Ganjil' }} &nbsp;&nbsp;|&nbsp;&nbsp; 
            <span>Tahun Ajaran:</span> {{ $tahunAjar->tahun ?? '-' }}
        </div>

        <table>
            <thead>
                <tr>
                    <th rowspan="2" style="width: {{ $noWidth }}; vertical-align: middle;">No</th>
                    <th rowspan="2" class="th-nama" style="width: {{ $namaWidth }}; vertical-align: middle;">Nama Siswa</th>
                    @foreach($part1Mapels as $mapel)
                        <th colspan="3" style="font-size: {{ $fontSizeMapel }}; padding: 3px 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $mapel->nama }}">{{ $mapel->nama }}</th>
                    @endforeach
                    @if(!$isSplit)
                        <th rowspan="2" style="width: {{ $totalAisWidth }}; vertical-align: middle;">Total AIS</th>
                    @endif
                </tr>
                <tr>
                    @foreach($part1Mapels as $mapel)
                        <th style="width: {{ $cellWidth }}; font-size: {{ $fontSizeTh }}; background: #f8fafc; font-weight: normal; color: #b91c1c;">A</th>
                        <th style="width: {{ $cellWidth }}; font-size: {{ $fontSizeTh }}; background: #f8fafc; font-weight: normal; color: #1d4ed8;">I</th>
                        <th style="width: {{ $cellWidth }}; font-size: {{ $fontSizeTh }}; background: #f8fafc; font-weight: normal; color: #c2410c;">S</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($matrix as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="td-nama" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $row['nama'] }}</td>
                        @foreach($part1Mapels as $mapel)
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
                        @endforeach
                        @if(!$isSplit)
                            <td class="total-col">{{ $row['total_ais'] ?: '-' }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- BAGIAN 2 / HALAMAN 2 (JIKA ADA SPLIT) -->
        @if($isSplit)
            <div class="page-break"></div>

            <div class="header">
                <h1>REKAPITULASI KETIDAKHADIRAN (AIS) SEMESTER (BAGIAN 2)</h1>
                <p>{{ $kelas->nama }} &nbsp;|&nbsp; {{ $semesterLabel }} &nbsp;|&nbsp; {{ $tahunAjarLabel }}</p>
            </div>

            <div class="info-section">
                <span>Kelas:</span> {{ $kelas->nama }} &nbsp;&nbsp;|&nbsp;&nbsp; 
                <span>Semester:</span> {{ $tahunAjar->semester ?? 'Ganjil' }} &nbsp;&nbsp;|&nbsp;&nbsp; 
                <span>Tahun Ajaran:</span> {{ $tahunAjar->tahun ?? '-' }}
            </div>

            <table>
                <thead>
                    <tr>
                        <th rowspan="2" style="width: {{ $noWidth }}; vertical-align: middle;">No</th>
                        <th rowspan="2" class="th-nama" style="width: {{ $namaWidth }}; vertical-align: middle;">Nama Siswa</th>
                        @foreach($part2Mapels as $mapel)
                            <th colspan="3" style="font-size: {{ $fontSizeMapel }}; padding: 3px 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $mapel->nama }}">{{ $mapel->nama }}</th>
                        @endforeach
                        <th rowspan="2" style="width: {{ $totalAisWidth }}; vertical-align: middle;">Total AIS</th>
                    </tr>
                    <tr>
                        @foreach($part2Mapels as $mapel)
                            <th style="width: {{ $cellWidth }}; font-size: {{ $fontSizeTh }}; background: #f8fafc; font-weight: normal; color: #b91c1c;">A</th>
                            <th style="width: {{ $cellWidth }}; font-size: {{ $fontSizeTh }}; background: #f8fafc; font-weight: normal; color: #1d4ed8;">I</th>
                            <th style="width: {{ $cellWidth }}; font-size: {{ $fontSizeTh }}; background: #f8fafc; font-weight: normal; color: #c2410c;">S</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($matrix as $index => $row)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td class="td-nama" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $row['nama'] }}</td>
                            @foreach($part2Mapels as $mapel)
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
                            @endforeach
                            <td class="total-col">{{ $row['total_ais'] ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="legend">
            <strong>Catatan:</strong><br>
            - Setiap mata pelajaran dibagi menjadi 3 sub-kolom: <strong>A</strong> (Alpha/Alpa), <strong>I</strong> (Izin), dan <strong>S</strong> (Sakit).<br>
            - Kolom <strong>Total AIS</strong> di bagian akhir menunjukkan akumulasi total hari ketidakhadiran (A + I + S) dari seluruh mata pelajaran.<br>
            - Tanda (-) menunjukkan tidak ada riwayat ketidakhadiran pada kolom tersebut.
        </div>
    @endif

</body>
</html>
