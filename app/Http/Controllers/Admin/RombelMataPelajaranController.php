<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RombelMataPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Guru;
use Illuminate\Http\Request;

class RombelMataPelajaranController extends Controller
{
    // 1. HALAMAN INDEX: Menampilkan Daftar Kelas
    public function index()
    {
        $activeYear = session('tahun_ajar_id');
        $kelas = Kelas::orderBy('nama', 'asc')->get();

        // Hitung berapa mapel yang sudah diplot untuk tiap kelas
        foreach ($kelas as $k) {
            $k->jumlah_mapel = RombelMataPelajaran::where('id_kelas', $k->id)
                ->where('id_tahun_ajar', $activeYear)
                ->count();
        }

        return view('admin.rombel-mata-pelajaran.index', compact('kelas'));
    }

    // 2. HALAMAN KELOLA: Menampilkan Form Dinamis
    public function manage($id_kelas)
    {
        $activeYear = session('tahun_ajar_id');
        if (!$activeYear) return back()->with('error', 'Pilih Tahun Ajar terlebih dahulu di menu atas!');

        $kelas = Kelas::findOrFail($id_kelas);
        $mapelList = MataPelajaran::orderBy('nama', 'asc')->get();
        $guruList = Guru::where('status', 'Aktif')->orderBy('nama', 'asc')->get();

        // Ambil data plotting yang sudah ada untuk kelas ini (untuk pre-fill form)
        $plottingSaatIni = RombelMataPelajaran::where('id_kelas', $id_kelas)
            ->where('id_tahun_ajar', $activeYear)
            ->get();

        return view('admin.rombel-mata-pelajaran.manage', compact('kelas', 'mapelList', 'guruList', 'plottingSaatIni'));
    }

    // 3. PROSES SIMPAN (Bulk Delete & Insert)
    public function storeManage(Request $request, $id_kelas)
    {
        $activeYear = session('tahun_ajar_id');

        // 1. Hapus semua plotting lama di kelas ini untuk tahun ajar aktif
        RombelMataPelajaran::where('id_kelas', $id_kelas)
            ->where('id_tahun_ajar', $activeYear)
            ->delete();

        // 2. Jika ada input baris baru, masukkan semuanya
        if ($request->has('id_mata_pelajaran') && $request->has('id_guru')) {
            $dataInsert = [];
            
            // Looping form array
            foreach ($request->id_mata_pelajaran as $index => $id_mapel) {
                // Pastikan mapel dan gurunya tidak kosong
                if (!empty($id_mapel) && !empty($request->id_guru[$index])) {
                    $dataInsert[] = [
                        'id_tahun_ajar' => $activeYear,
                        'id_kelas' => $id_kelas,
                        'id_mata_pelajaran' => $id_mapel,
                        'id_guru' => $request->id_guru[$index],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            
            // Insert massal
            if (count($dataInsert) > 0) {
                RombelMataPelajaran::insert($dataInsert);
            }
        }

        return redirect()->route('admin.rombel-mata-pelajaran.index')->with('success', 'Plotting Guru Mata Pelajaran berhasil diperbarui!');
    }
}