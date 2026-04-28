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
    public function index()
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

        // 3. Ambil 5 Data Presensi Terakhir (Realtime Feed)
        $presensiTerbaru = Presensi::with(['siswa', 'rombelJadwalPelajaran.mataPelajaran'])
                                   ->orderBy('tanggal', 'desc')
                                   ->orderBy('jam_scan', 'desc')
                                   ->take(5)
                                   ->get();

        return view('admin.dashboard', compact(
            'totalSiswa', 
            'totalDevice', 
            'inboxPending', 
            'hadirHariIni', 
            'terlambatHariIni', 
            'presensiTerbaru'
        ));
    }
}