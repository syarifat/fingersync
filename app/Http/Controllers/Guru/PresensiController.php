<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\RombelJadwalPelajaran;
use App\Models\RombelKelas;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PresensiController extends Controller
{
    public function show($id)
    {
        // 1. Ambil data jadwal beserta relasinya
        $jadwal = RombelJadwalPelajaran::with(['rombelMataPelajaran.kelas', 'rombelMataPelajaran.mataPelajaran'])
            ->findOrFail($id);

        // Keamanan: Pastikan jadwal ini benar-benar milik guru yang sedang login
        $guru_id = \App\Models\Guru::where('user_id', Auth::id())->value('id');
        if ($jadwal->rombelMataPelajaran->id_guru != $guru_id) {
            abort(403, 'Akses Ditolak. Ini bukan jadwal Anda.');
        }

        // 2. Ambil daftar siswa di kelas tersebut (dari tabel rombel_kelas)
        $rombelSiswa = RombelKelas::with('siswa')
            ->where('id_kelas', $jadwal->rombelMataPelajaran->id_kelas)
            ->where('id_tahun_ajar', $jadwal->rombelMataPelajaran->id_tahun_ajar)
            ->get();

        // 3. Ambil data presensi HARI INI untuk jadwal ini
        $tanggalHariIni = Carbon::now()->format('Y-m-d');
        
        $presensiHariIni = Presensi::where('id_rombel_jadwal_pelajaran', $id)
            ->where('tanggal', $tanggalHariIni)
            ->get()
            ->keyBy('id_siswa'); // Kunci array dengan id_siswa agar sangat mudah dicari di tampilan (View)

        return view('guru.presensi.show', compact('jadwal', 'rombelSiswa', 'presensiHariIni', 'tanggalHariIni'));
    }
}