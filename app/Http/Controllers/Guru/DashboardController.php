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
    public function index()
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

        // 5. Cek apakah guru ini adalah Wali Kelas (opsional jika ada session tahun_ajar)
        $isWaliKelas = \App\Models\RombelKelas::with('kelas')
            ->where('id_guru_wali_kelas', $guru->id)
            // ->where('id_tahun_ajar', session('tahun_ajar_id')) // Buka komentar ini jika Anda pakai session tahun ajar
            ->first();

        // 6. Ambil Siswa Terlambat dikelompokkan berdasarkan kelas yang diajar / diwalikan
        $tanggalTerakhir = \App\Models\Presensi::max('tanggal') ?? Carbon::today('Asia/Jakarta')->toDateString();
        $hariTerakhir = Carbon::parse($tanggalTerakhir)->isoFormat('dddd');

        // Kelas di mana guru adalah wali kelas
        $kelasWaliIds = \App\Models\RombelKelas::where('id_guru_wali_kelas', $guru->id)
            ->pluck('id_kelas')
            ->toArray();

        // Kelas di mana guru mengajar pada hari terakhir absensi atau hari kalender ini
        $kelasAjarIds = RombelJadwalPelajaran::whereIn('hari', [$hariTerakhir, $hariIni])
            ->whereHas('rombelMataPelajaran', function ($query) use ($guru) {
                $query->where('id_guru', $guru->id);
            })
            ->get()
            ->map(function ($jadwal) {
                return $jadwal->rombelMataPelajaran->id_kelas ?? null;
            })
            ->filter()
            ->unique()
            ->toArray();

        $classIds = array_unique(array_merge($kelasWaliIds, $kelasAjarIds));

        $siswaTerlambat = \App\Models\Presensi::with([
            'siswa',
            'rombelJadwalPelajaran.rombelMataPelajaran.kelas'
        ])
        ->whereDate('tanggal', $tanggalTerakhir)
        ->where('status', 'Terlambat')
        ->whereHas('rombelJadwalPelajaran.rombelMataPelajaran', function($query) use ($classIds) {
            $query->whereIn('id_kelas', $classIds);
        })
        ->get();

        $terlambatByKelas = $siswaTerlambat->groupBy(function($p) {
            return $p->rombelJadwalPelajaran->rombelMataPelajaran->kelas->nama ?? 'Lainnya';
        });

        return view('guru.dashboard', compact(
            'guru', 
            'hariIni', 
            'jadwalHariIni', 
            'totalJadwalSeminggu', 
            'isWaliKelas',
            'terlambatByKelas',
            'tanggalTerakhir',
            'kbmKhususUtama',
            'jadwalHariIniPengganti'
        ));
    }
}