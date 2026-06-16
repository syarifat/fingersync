<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Presensi Siswa</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1a1a1a; }

        .header { text-align: center; margin-bottom: 14px; border-bottom: 2px solid #1a1a1a; padding-bottom: 8px; }
        .header h1 { font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header p { font-size: 10px; color: #555; margin-top: 3px; }

        .filter-info { margin-bottom: 10px; font-size: 11px; color: #333; }
        .filter-info span { font-weight: bold; }

        .mapel-section { margin-bottom: 22px; }
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
        .th-no    { width: 25px; padding: 4px 2px; font-size: 9px; background: #f1f5f9; }
        .th-nama  { width: 110px; text-align: left; padding: 4px 6px; font-size: 9px; background: #f1f5f9; }
        .th-tgl   { width: 18px; padding: 4px 1px; font-size: 8px; background: #f1f5f9; }
        .th-total { width: 20px; padding: 4px 1px; font-size: 8px; background: #f1f5f9; }

        /* Data rows */
        .td-no   { padding: 3px 2px; font-size: 8.5px; color: #64748b; }
        .td-nama { padding: 3px 6px; text-align: left; font-size: 8.5px; font-weight: 500; white-space: nowrap; overflow: hidden; max-width: 110px; }
        .td-cell { padding: 3px 1px; font-size: 8.5px; font-weight: bold; }
        .td-total { padding: 3px 1px; font-size: 8.5px; }

        /* Status colors */
        .H { background: #dcfce7; color: #15803d; }   /* Hadir - hijau */
        .T { background: #fef9c3; color: #854d0e; }   /* Terlambat - kuning */
        .I { background: #dbeafe; color: #1d4ed8; }   /* Izin - biru */
        .S { background: #ffedd5; color: #c2410c; }   /* Sakit - oranye */
        .A { background: #fee2e2; color: #b91c1c; }   /* Alpa - merah */
        .weekend { background: #fca5a5 !important; } /* Weekend - merah tebal */
        .dash { color: #d1d5db; }

        /* Legend */
        .legend { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px; font-size: 8.5px; }
        .leg-item { display: inline-flex; align-items: center; gap: 4px; margin-right: 10px; }
        .leg-box { width: 14px; height: 14px; border-radius: 3px; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; font-size: 8px; }

        .empty-msg { padding: 20px; text-align: center; color: #94a3b8; font-style: italic; }
    </style>
</head>
<body>

    <div class="header">
        <h1>REKAP PRESENSI BULANAN</h1>
        <p>{{ $kelas->nama }} &nbsp;|&nbsp; {{ $bulanLabel }}</p>
    </div>

    <div class="filter-info">
        Keterangan Filter: 
        <span>Bulan:</span> {{ $bulanLabel }} | 
        <span>Kelas:</span> {{ $kelas->nama }} | 
        <span>Mata Pelajaran:</span> {{ $mapelInfo ?? 'Semua Mata Pelajaran' }}
    </div>

    @if($siswaList->isEmpty())
        <div class="empty-msg">Tidak ada siswa di kelas ini.</div>
    @else
        @foreach($dataPerMapel as $item)
        <div class="mapel-section" style="{{ !$loop->last ? 'page-break-after: always;' : '' }}">
            <div class="mapel-title">📚 {{ $item['nama_mapel'] }}</div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" class="th-no">No</th>
                        <th rowspan="2" class="th-nama">Nama Siswa</th>
                        <th colspan="{{ count($item['datesInfo']) }}" class="th-tgl" style="border-bottom: 1px solid #cbd5e1;">Tanggal</th>
                        <th colspan="5" class="th-total" style="border-bottom: 1px solid #cbd5e1;">Total</th>
                    </tr>
                    <tr>
                        @foreach($item['datesInfo'] as $info)
                            <th class="th-tgl {{ $info['isWeekend'] ? 'weekend' : '' }}">{{ $info['day'] }}</th>
                        @endforeach
                        <th class="th-total">H</th>
                        <th class="th-total">I</th>
                        <th class="th-total">S</th>
                        <th class="th-total">A</th>
                        <th class="th-total" style="background: #fee2e2; color: #b91c1c; font-weight: bold;">AIS</th>
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
                        
                        @foreach($item['datesInfo'] as $info)
                            @php
                                $status = $matrixRow[$info['day']] ?? '';
                                if ($status == 'H') $countH++;
                                elseif ($status == 'T') $countT++;
                                elseif ($status == 'I') $countI++;
                                elseif ($status == 'S') $countS++;
                                elseif ($status == 'A') $countA++;
                            @endphp
                            <td class="td-cell {{ $status != '' ? $status : '' }} {{ $info['isWeekend'] ? 'weekend' : '' }} {{ $status == '' ? 'dash' : '' }}">
                                {{ $status != '' ? $status : '-' }}
                            </td>
                        @endforeach
                        
                        {{-- H is combined Hadir + Terlambat in this unified view, or just H --}}
                        <td class="td-total H">{{ ($countH + $countT) ?: '' }}</td>
                        <td class="td-total I">{{ $countI ?: '' }}</td>
                        <td class="td-total S">{{ $countS ?: '' }}</td>
                        <td class="td-total A">{{ $countA ?: '' }}</td>
                        <td class="td-total" style="font-weight: bold; background: #fff1f2; color: #be123c;">{{ ($countI + $countS + $countA) ?: '' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="legend">
                <span><strong>Keterangan:</strong></span>
                <span class="leg-item"><span class="leg-box H">H</span> Hadir / <span class="leg-box T">T</span> Terlambat (Dihitung Hadir)</span>
                <span class="leg-item"><span class="leg-box I">I</span> Izin</span>
                <span class="leg-item"><span class="leg-box S">S</span> Sakit</span>
                <span class="leg-item"><span class="leg-box A">A</span> Alpa</span>
                <span class="leg-item"><span class="leg-box" style="background: #fee2e2; color: #b91c1c; font-weight: bold; padding: 0 4px;">AIS</span> Alpha + Izin + Sakit</span>
                <span class="leg-item"><span class="leg-box weekend"></span> Hari Libur (Sabtu/Minggu)</span>
                <span class="leg-item">- = Tidak ada kegiatan</span>
            </div>
        </div>
        @endforeach
    @endif

</body>
</html>
