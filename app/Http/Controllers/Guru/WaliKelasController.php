<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\RombelKelas;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WaliKelasController extends Controller
{
    public function index(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        // 1. Cari kelas di mana guru ini menjadi Wali Kelas
        $infoKelas = RombelKelas::with('kelas')
            ->where('id_guru_wali_kelas', $guru->id)
            ->first();

        if (!$infoKelas) {
            abort(403, 'Akses Ditolak. Anda tidak terdaftar sebagai Wali Kelas.');
        }

        // 2. Ambil semua siswa di kelas tersebut
        $siswaKelas = RombelKelas::with('siswa')
            ->where('id_kelas', $infoKelas->id_kelas)
            ->get();

        // 3. Filter Bulan (Default: Bulan Ini)
        $bulanFilter = $request->bulan ?? Carbon::now()->format('Y-m');

        // 4. Hitung Rekap Absensi per Siswa untuk bulan tersebut
        foreach ($siswaKelas as $rs) {
            $rs->total_hadir = Presensi::where('id_siswa', $rs->id_siswa)
                                ->where('tanggal', 'like', $bulanFilter . '%')
                                ->where('status', 'Hadir')->count();
            
            $rs->total_sakit = Presensi::where('id_siswa', $rs->id_siswa)
                                ->where('tanggal', 'like', $bulanFilter . '%')
                                ->where('status', 'Sakit')->count();

            $rs->total_izin = Presensi::where('id_siswa', $rs->id_siswa)
                                ->where('tanggal', 'like', $bulanFilter . '%')
                                ->where('status', 'Izin')->count();

            $rs->total_alpha = Presensi::where('id_siswa', $rs->id_siswa)
                                ->where('tanggal', 'like', $bulanFilter . '%')
                                ->where('status', 'Alpha')->count();
        }

        return view('guru.walikelas.index', compact('infoKelas', 'siswaKelas', 'bulanFilter'));
    }
}