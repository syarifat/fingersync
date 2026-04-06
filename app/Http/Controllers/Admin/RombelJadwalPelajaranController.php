<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RombelJadwalPelajaran;
use App\Models\RombelMataPelajaran;
use App\Models\Kelas;
use App\Models\Ruangan;
use Illuminate\Http\Request;

class RombelJadwalPelajaranController extends Controller
{
    // 1. HALAMAN INDEX: Menampilkan Daftar Kelas
    public function index()
    {
        $activeYear = session('tahun_ajar_id');
        $kelas = Kelas::orderBy('nama', 'asc')->get();

        // Hitung berapa jadwal yang sudah dibuat untuk tiap kelas
        foreach ($kelas as $k) {
            $k->jumlah_jadwal = RombelJadwalPelajaran::whereHas('rombelMapel', function($q) use ($k, $activeYear) {
                $q->where('id_kelas', $k->id)->where('id_tahun_ajar', $activeYear);
            })->count();
        }

        return view('admin.rombel-jadwal.index', compact('kelas'));
    }

    // 2. HALAMAN KELOLA: Form Dinamis Jadwal
    public function manage($id_kelas)
    {
        $activeYear = session('tahun_ajar_id');
        if (!$activeYear) return back()->with('error', 'Pilih Tahun Ajar terlebih dahulu di menu atas!');

        $kelas = Kelas::findOrFail($id_kelas);

        // Ambil Mapel yang sudah diplot KHUSUS untuk kelas ini
        $plottings = RombelMataPelajaran::with(['mataPelajaran', 'guru'])
            ->where('id_kelas', $id_kelas)
            ->where('id_tahun_ajar', $activeYear)
            ->get();

        // Jika belum ada plotting mapel, cegah masuk ke halaman ini
        if($plottings->isEmpty()) {
            return back()->with('error', 'Kelas ini belum memiliki plotting mata pelajaran. Silakan atur di menu Plotting Guru terlebih dahulu.');
        }

        $ruangan = Ruangan::orderBy('nama_ruangan')->get();

        // Ambil jadwal existing untuk di-load ke JavaScript
        $jadwalSaatIni = RombelJadwalPelajaran::whereHas('rombelMapel', function($q) use ($id_kelas, $activeYear) {
            $q->where('id_kelas', $id_kelas)->where('id_tahun_ajar', $activeYear);
        })->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
          ->orderBy('jam_mulai', 'asc')
          ->get();

        return view('admin.rombel-jadwal.manage', compact('kelas', 'plottings', 'ruangan', 'jadwalSaatIni'));
    }

    // 3. PROSES SIMPAN (Bulk Delete & Insert)
    public function storeManage(Request $request, $id_kelas)
    {
        $activeYear = session('tahun_ajar_id');

        // 1. Cari semua ID Plotting milik kelas ini
        $plottingIds = RombelMataPelajaran::where('id_kelas', $id_kelas)
            ->where('id_tahun_ajar', $activeYear)
            ->pluck('id');

        // 2. Hapus semua jadwal lama berdasarkan ID Plotting tersebut
        RombelJadwalPelajaran::whereIn('id_rombel_mata_pelajaran', $plottingIds)->delete();

        // 3. Insert jadwal baru jika form tidak kosong
        if ($request->has('hari')) {
            $dataInsert = [];
            foreach ($request->hari as $index => $hari) {
                // Pastikan baris tersebut valid (tidak ada dropdown yang belum dipilih)
                if (!empty($hari) && !empty($request->id_rombel_mata_pelajaran[$index]) && !empty($request->jam_mulai[$index])) {
                    $dataInsert[] = [
                        'id_rombel_mata_pelajaran' => $request->id_rombel_mata_pelajaran[$index],
                        'hari' => $hari,
                        'jam_mulai' => $request->jam_mulai[$index],
                        'jam_selesai' => $request->jam_selesai[$index],
                        'id_ruangan' => $request->id_ruangan[$index],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if(count($dataInsert) > 0){
                RombelJadwalPelajaran::insert($dataInsert);
            }
        }

        return redirect()->route('admin.rombel-jadwal.index')->with('success', 'Jadwal pelajaran kelas berhasil diperbarui!');
    }
}