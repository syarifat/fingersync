<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\RombelJadwalPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Cari data profil Guru berdasarkan user_id yang sedang login
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        // 2. Dapatkan nama hari ini dalam bahasa Indonesia
        Carbon::setLocale('id');
        $hariIni = Carbon::now()->isoFormat('dddd');

        $todayDate = Carbon::now('Asia/Jakarta')->toDateString();

        // 3. Ambil jadwal mengajar HARI INI (Gunakan whereHas untuk menyeberang ke tabel rombel_mata_pelajaran)
        $jadwalHariIni = RombelJadwalPelajaran::with(['rombelMataPelajaran.kelas', 'rombelMataPelajaran.mataPelajaran', 'ruangan'])
            ->whereHas('rombelMataPelajaran', function ($query) use ($guru) {
                $query->where('id_guru', $guru->id);
            })
            ->where('hari', $hariIni)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // Cek kondisi khusus Guru (Izin, Absen, Diganti) hari ini
        $kbmKhususUtama = \App\Models\GuruKbmKhusus::with('guruPengganti')
            ->whereIn('id_rombel_jadwal_pelajaran', $jadwalHariIni->pluck('id'))
            ->whereDate('tanggal', $todayDate)
            ->get()
            ->keyBy('id_rombel_jadwal_pelajaran');

        // Ambil jadwal di mana guru ini ditunjuk sebagai Guru Pengganti hari ini
        $idJadwalPengganti = \App\Models\GuruKbmKhusus::whereDate('tanggal', $todayDate)
            ->where('status', 'diganti')
            ->where('id_guru_pengganti', $guru->id)
            ->pluck('id_rombel_jadwal_pelajaran')
            ->toArray();

        $jadwalHariIniPengganti = RombelJadwalPelajaran::with(['rombelMataPelajaran.kelas', 'rombelMataPelajaran.mataPelajaran', 'ruangan'])
            ->whereIn('id', $idJadwalPengganti)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // 4. Hitung total jam mengajar dalam seminggu
        $totalJadwalSeminggu = RombelJadwalPelajaran::whereHas('rombelMataPelajaran', function ($query) use ($guru) {
            $query->where('id_guru', $guru->id);
        })->count();

        // 5. Cek apakah guru ini adalah Wali Kelas
        $isWaliKelas = \App\Models\RombelKelas::with('kelas')
            ->where('id_guru_wali_kelas', $guru->id)
            ->first();

        // 6. Ambil List Kelas & Log Presensi Terbaru (Khusus kelas yang diampu / diwalikan)
        $kelasWaliIds = \App\Models\RombelKelas::where('id_guru_wali_kelas', $guru->id)
            ->pluck('id_kelas')
            ->toArray();

        $kelasAjarIds = \App\Models\RombelMataPelajaran::where('id_guru', $guru->id)
            ->pluck('id_kelas')
            ->toArray();

        $classIds = array_unique(array_merge($kelasWaliIds, $kelasAjarIds));
        $kelasList = \App\Models\Kelas::whereIn('id', $classIds)->orderBy('nama', 'asc')->get();
        $filterKelasId = $request->kelas_id;

        $presensiTerbaruQuery = \App\Models\Presensi::with([
            'siswa.rombelKelas.kelas', 
            'rombelJadwalPelajaran.rombelMataPelajaran.mataPelajaran',
            'kegiatanSekolah'
        ])
        ->whereHas('siswa.rombelKelas', function($query) use ($classIds) {
            $query->whereIn('id_kelas', $classIds);
        })
        ->orderBy('tanggal', 'desc')
        ->orderBy('jam_scan', 'desc');

        if ($filterKelasId && in_array($filterKelasId, $classIds)) {
            $presensiTerbaruQuery->whereHas('siswa.rombelKelas', function($q) use ($filterKelasId) {
                $q->where('id_kelas', $filterKelasId);
            });
        }

        $presensiTerbaru = $presensiTerbaruQuery->take(5)->get();

        return view('guru.dashboard', compact(
            'guru', 
            'hariIni', 
            'jadwalHariIni', 
            'totalJadwalSeminggu', 
            'isWaliKelas',
            'presensiTerbaru',
            'kelasList',
            'filterKelasId',
            'kbmKhususUtama',
            'jadwalHariIniPengganti'
        ));
    }
}