<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\Device;
use App\Models\Presensi;
use App\Models\FingerprintInbox;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // Cari tanggal terakhir ada presensi untuk demo data, fallback ke hari ini jika kosong
        $tanggalTerakhir = Presensi::max('tanggal') ?? Carbon::today('Asia/Jakarta')->toDateString();
        $hariIni = Carbon::parse($tanggalTerakhir);

        // 1. Ambil Statistik Dasar
        $totalSiswa = Siswa::count();
        $totalDevice = Device::count();
        $inboxPending = FingerprintInbox::where('status', 'pending')->count();

        // 2. Ambil Statistik Kehadiran Hari Ini
        $hadirHariIni = Presensi::whereDate('tanggal', $hariIni)
                                ->where('status', 'Hadir')
                                ->count();
                                
        $terlambatHariIni = Presensi::whereDate('tanggal', $hariIni)
                                    ->where('status', 'Terlambat')
                                    ->count();

        // 3. Ambil List Kelas untuk dropdown filter
        $kelasList = \App\Models\Kelas::orderBy('nama', 'asc')->get();
        $filterKelasId = $request->kelas_id;

        // 4. Ambil 5 Data Presensi Terakhir (Realtime Feed) dengan filter kelas
        $presensiTerbaruQuery = Presensi::with([
            'siswa.rombelKelas.kelas', 
            'rombelJadwalPelajaran.rombelMataPelajaran.mataPelajaran',
            'kegiatanSekolah'
        ])
        ->orderBy('tanggal', 'desc')
        ->orderBy('jam_scan', 'desc');

        if ($filterKelasId) {
            $presensiTerbaruQuery->whereHas('siswa.rombelKelas', function($q) use ($filterKelasId) {
                $q->where('id_kelas', $filterKelasId);
            });
        }

        $presensiTerbaru = $presensiTerbaruQuery->take(5)->get();

        return view('admin.dashboard', compact(
            'totalSiswa', 
            'totalDevice', 
            'inboxPending', 
            'hadirHariIni', 
            'terlambatHariIni', 
            'presensiTerbaru',
            'tanggalTerakhir',
            'kelasList',
            'filterKelasId'
        ));
    }
}