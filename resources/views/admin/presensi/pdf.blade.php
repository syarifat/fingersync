<!DOCTYPE html>
<html>
<head>
    <title>Laporan Presensi Siswa</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .status-hadir { color: #16a34a; font-weight: bold; }
        .status-izin { color: #2563eb; font-weight: bold; }
        .status-sakit { color: #ca8a04; font-weight: bold; }
        .status-alpa { color: #dc2626; font-weight: bold; }
        .status-terlambat { color: #ea580c; font-weight: bold; }
        .filters { margin-bottom: 15px; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN PRESENSI SISWA</h1>
        <p>FingerSync - Sistem Manajemen Kehadiran Berbasis Sidik Jari</p>
    </div>

    <div class="filters">
        <strong>Filter Aktif:</strong>
        @if(request('kelas_id')) | Kelas: {{ \App\Models\Kelas::find(request('kelas_id'))->nama ?? '-' }} @endif
        @if(request('mapel_id')) | Mapel: {{ \App\Models\MataPelajaran::find(request('mapel_id'))->nama ?? '-' }} @endif
        @if(request('tanggal')) | Harian: {{ \Carbon\Carbon::parse(request('tanggal'))->isoFormat('DD MMMM YYYY') }} @endif
        @if(request('bulan')) | Bulanan: {{ \Carbon\Carbon::parse(request('bulan').'-01')->isoFormat('MMMM YYYY') }} @endif
        @if(request('search')) | Cari: {{ request('search') }} @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>TANGGAL & WAKTU</th>
                <th>SISWA</th>
                <th>KELAS</th>
                <th>MATA PELAJARAN</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataPresensi as $index => $presensi)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    {{ \Carbon\Carbon::parse($presensi->tanggal)->format('d/m/Y') }}<br>
                    <small>{{ $presensi->waktu_absen }}</small>
                </td>
                <td>
                    <strong>{{ $presensi->siswa->nama ?? '-' }}</strong><br>
                    <small>NIS: {{ $presensi->siswa->nis ?? '-' }}</small>
                </td>
                <td>{{ $presensi->rombelJadwalPelajaran->rombelMataPelajaran->kelas->nama ?? '-' }}</td>
                <td>{{ $presensi->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran->nama ?? '-' }}</td>
                <td>
                    @php
                        $colorClass = 'status-' . strtolower($presensi->status);
                    @endphp
                    <span class="{{ $colorClass }}">{{ $presensi->status }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">Tidak ada data presensi yang sesuai dengan filter.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
