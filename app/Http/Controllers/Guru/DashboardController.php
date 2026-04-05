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

        // 3. Ambil jadwal mengajar HARI INI (Gunakan whereHas untuk menyeberang ke tabel rombel_mata_pelajaran)
        $jadwalHariIni = RombelJadwalPelajaran::with(['rombelMataPelajaran.kelas', 'rombelMataPelajaran.mataPelajaran', 'ruangan'])
            ->whereHas('rombelMataPelajaran', function ($query) use ($guru) {
                $query->where('id_guru', $guru->id);
            })
            ->where('hari', $hariIni)
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

        return view('guru.dashboard', compact('guru', 'hariIni', 'jadwalHariIni', 'totalJadwalSeminggu', 'isWaliKelas'));
    }
}