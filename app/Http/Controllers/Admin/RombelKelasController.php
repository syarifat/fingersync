<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RombelKelas;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Guru;
use Illuminate\Http\Request;

class RombelKelasController extends Controller
{
    // 1. HALAMAN INDEX (Menampilkan Daftar Kelas, bukan Siswa)
    public function index(Request $request)
    {
        $activeYear = session('tahun_ajar_id');
        $kelas = Kelas::orderBy('nama', 'asc')->get();

        // Cari tahu info rombel untuk tiap kelas di tahun ajar ini
        foreach ($kelas as $k) {
            $perwakilan = RombelKelas::with(['waliKelas', 'guruBk'])
                ->where('id_kelas', $k->id)
                ->where('id_tahun_ajar', $activeYear)
                ->first();

            $k->wali_kelas_nama = $perwakilan ? $perwakilan->waliKelas->nama : 'Belum Diatur';
            $k->guru_bk_nama = $perwakilan ? $perwakilan->guruBk->nama : 'Belum Diatur';
            
            $k->jumlah_siswa = RombelKelas::where('id_kelas', $k->id)
                ->where('id_tahun_ajar', $activeYear)
                ->count();
        }

        return view('admin.rombel-kelas.index', compact('kelas'));
    }

    // 2. HALAMAN KELOLA (Tampilan 2 Div Kanan-Kiri)
    public function manage($id_kelas)
    {
        $activeYear = session('tahun_ajar_id');
        if (!$activeYear) return back()->with('error', 'Pilih Tahun Ajar terlebih dahulu di menu atas!');

        $kelas = Kelas::findOrFail($id_kelas);
        $guru = Guru::where('status', 'Aktif')->orderBy('nama', 'asc')->get();

        // Cari Wali & BK saat ini (jika sudah diset sebelumnya)
        $rombelSaatIni = RombelKelas::where('id_kelas', $id_kelas)->where('id_tahun_ajar', $activeYear)->first();

        // Div Kanan: Siswa yang SUDAH di dalam kelas ini
        $siswaInClass = Siswa::whereHas('rombelKelas', function($q) use ($id_kelas, $activeYear) {
            $q->where('id_kelas', $id_kelas)->where('id_tahun_ajar', $activeYear);
        })->orderBy('nama')->get();

        // Div Kiri: Siswa yang BELUM punya kelas sama sekali di tahun ini
        $siswaNoClass = Siswa::whereDoesntHave('rombelKelas', function($q) use ($activeYear) {
            $q->where('id_tahun_ajar', $activeYear);
        })->orderBy('nama')->get();

        return view('admin.rombel-kelas.manage', compact('kelas', 'guru', 'rombelSaatIni', 'siswaInClass', 'siswaNoClass'));
    }

    // 3. PROSES SIMPAN KELOLA
    public function storeManage(Request $request, $id_kelas)
    {
        $request->validate([
            'id_guru_wali_kelas' => 'required',
            'id_guru_bk' => 'required',
            'id_siswa' => 'nullable|array', // Boleh kosong jika sengaja mengeluarkan semua siswa
        ]);

        $activeYear = session('tahun_ajar_id');

        // Strategi Ampuh: Hapus semua data rombel lama untuk kelas ini
        RombelKelas::where('id_kelas', $id_kelas)->where('id_tahun_ajar', $activeYear)->delete();

        // Jika ada siswa di kotak kanan, masukkan semuanya dengan Wali & BK yang baru
        if ($request->has('id_siswa')) {
            $dataInsert = [];
            foreach ($request->id_siswa as $siswaId) {
                $dataInsert[] = [
                    'id_tahun_ajar' => $activeYear,
                    'id_kelas' => $id_kelas,
                    'id_siswa' => $siswaId,
                    'id_guru_wali_kelas' => $request->id_guru_wali_kelas,
                    'id_guru_bk' => $request->id_guru_bk,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            RombelKelas::insert($dataInsert); // Insert massal biar database tidak capek
        }

        return redirect()->route('admin.rombel-kelas.index')->with('success', 'Konfigurasi Rombel Kelas berhasil diperbarui!');
    }
}