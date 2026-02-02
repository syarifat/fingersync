@extends('layouts.admin') {{-- Sesuaikan dengan nama layout utama kamu --}}

@section('content')
<div class="container-fluid">

    <h1 class="h3 mb-4 text-gray-800">Data Presensi</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Daftar Kehadiran Siswa</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Waktu</th>
                            <th>Siswa</th>
                            <th>Jadwal / Mapel</th>
                            <th>Status</th>
                            <th>Tahun Ajar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dataPresensi as $key => $row)
                        <tr>
                            <td>{{ $dataPresensi->firstItem() + $key }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}<br>
                                <small class="text-muted">{{ $row->jam_scan }}</small>
                            </td>
                            <td>
                                {{ $row->siswa->nama ?? 'Siswa Tidak Ditemukan' }}
                            </td>
                            <td>
                                {{-- Pastikan relasi 'jadwal' ada di Model Presensi --}}
                                {{ $row->jadwal->nama_pelajaran ?? '-' }} <br>
                                <small>{{ $row->jadwal->jam_mulai ?? '' }} - {{ $row->jadwal->jam_selesai ?? '' }}</small>
                            </td>
                            <td>
                                @if($row->status == 'Hadir')
                                    <span class="badge badge-success">Hadir</span>
                                @elseif($row->status == 'Terlambat')
                                    <span class="badge badge-warning">Terlambat</span>
                                @else
                                    <span class="badge badge-danger">{{ $row->status }}</span>
                                @endif
                            </td>
                            <td>{{ $row->tahunAjar->tahun ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Data presensi kosong.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $dataPresensi->links() }}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection