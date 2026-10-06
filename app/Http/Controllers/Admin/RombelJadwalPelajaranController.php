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

    // 3. PROSES SIMPAN (Validasi Bentrok & Bulk Insert)
    public function storeManage(Request $request, $id_kelas)
    {
        $activeYear = session('tahun_ajar_id');

        // Jika form kosong (Admin sengaja menghapus semua baris)
        if (!$request->has('hari')) {
            $plottingIds = RombelMataPelajaran::where('id_kelas', $id_kelas)->where('id_tahun_ajar', $activeYear)->pluck('id');
            $jadwalLamaIds = RombelJadwalPelajaran::whereIn('id_rombel_mata_pelajaran', $plottingIds)->pluck('id');
            
            // Hapus child data (presensi) terlebih dahulu agar tidak kena foreign key constraint error
            \App\Models\Presensi::whereIn('id_rombel_jadwal_pelajaran', $jadwalLamaIds)->delete();
            
            RombelJadwalPelajaran::whereIn('id', $jadwalLamaIds)->delete();
            return redirect()->route('admin.rombel-jadwal.index')->with('success', 'Jadwal pelajaran berhasil dikosongkan!');
        }

        // =========================================================================
        // MULAI VALIDASI BENTROK
        // =========================================================================

        // 1. Ambil data plotting mapel untuk mengecek Guru di form yang disubmit
        $plottingMapelList = RombelMataPelajaran::where('id_tahun_ajar', $activeYear)->get()->keyBy('id');

        // 2. Ambil semua jadwal dari KELAS LAIN (Untuk cek bentrok ruangan & guru)
        $jadwalKelasLain = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran', 'rombelMapel.guru', 'ruangan'])
            ->whereHas('rombelMapel', function($q) use ($activeYear, $id_kelas) {
                $q->where('id_tahun_ajar', $activeYear)
                  ->where('id_kelas', '!=', $id_kelas); // Kecualikan kelas ini sendiri
            })->get();

        $dataInsert = [];
        $count = count($request->hari);

        for ($i = 0; $i < $count; $i++) {
            $hari_i = $request->hari[$i];
            $id_plot_i = $request->id_rombel_mata_pelajaran[$i];
            $mulai_i = $request->jam_mulai[$i];
            $selesai_i = $request->jam_selesai[$i];
            $ruangan_i = $request->id_ruangan[$i];

            // Abaikan jika ada baris yang kosong atau tidak lengkap
            if (empty($hari_i) || empty($id_plot_i) || empty($mulai_i) || empty($selesai_i) || empty($ruangan_i)) {
                continue; 
            }

            // Validasi Logika Waktu
            if ($mulai_i >= $selesai_i) {
                return back()->with('error', "Format waktu salah! Pada hari $hari_i, Jam Selesai harus lebih besar dari Jam Mulai.");
            }

            $guru_id_i = $plottingMapelList[$id_plot_i]->id_guru ?? null;

            // A. CEK BENTROK INTERNAL (Dalam satu kelas/form yang sama)
            for ($j = 0; $j < $i; $j++) {
                if ($request->hari[$j] == $hari_i) {
                    $mulai_j = $request->jam_mulai[$j];
                    $selesai_j = $request->jam_selesai[$j];
                    // Rumus Overlap: (Mulai A < Selesai B) DAN (Selesai A > Mulai B)
                    if ($mulai_i < $selesai_j && $selesai_i > $mulai_j) {
                        return back()->with('error', "Bentrok Jadwal Kelas! Ada jadwal yang tumpang tindih pada hari $hari_i jam $mulai_i di form yang Anda isi.");
                    }
                }
            }

            // B. CEK BENTROK EKSTERNAL (Dengan kelas lain)
            foreach ($jadwalKelasLain as $db) {
                if ($db->hari == $hari_i) {
                    // Cek jika waktunya tumpang tindih
                    if ($mulai_i < $db->jam_selesai && $selesai_i > $db->jam_mulai) {
                        
                        // Aturan 2: Bentrok Ruangan
                        if ($ruangan_i == $db->id_ruangan) {
                            $nama_ruangan = $db->ruangan->nama_ruangan ?? 'Ruangan';
                            $nama_kelas = $db->rombelMapel->kelas->nama ?? 'Kelas Lain';
                            return back()->with('error', "BENTROK RUANGAN! $nama_ruangan pada hari $hari_i ($mulai_i - $selesai_i) sedang digunakan oleh $nama_kelas.");
                        }

                        // Aturan 3: Bentrok Guru
                        if ($guru_id_i == $db->rombelMapel->id_guru) {
                            $nama_guru = $db->rombelMapel->guru->nama ?? 'Guru';
                            $nama_kelas = $db->rombelMapel->kelas->nama ?? 'Kelas Lain';
                            return back()->with('error', "BENTROK GURU! $nama_guru pada hari $hari_i ($mulai_i - $selesai_i) sudah memiliki jadwal mengajar di $nama_kelas.");
                        }
                    }
                }
            }

            // Jika lulus semua validasi, masukkan ke antrean insert
            $dataInsert[] = [
                'id_rombel_mata_pelajaran' => $id_plot_i,
                'hari' => $hari_i,
                'jam_mulai' => $mulai_i,
                'jam_selesai' => $selesai_i,
                'id_ruangan' => $ruangan_i,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        // =========================================================================
        // AKHIR VALIDASI BENTROK
        // =========================================================================

        // Jika script berhasil sampai di titik ini, berarti SELURUH jadwal AMAN 100%.
        // Lakukan Sapu Bersih jadwal lama, dan Insert jadwal baru.
        $plottingIds = RombelMataPelajaran::where('id_kelas', $id_kelas)->where('id_tahun_ajar', $activeYear)->pluck('id');
        $jadwalLamaIds = RombelJadwalPelajaran::whereIn('id_rombel_mata_pelajaran', $plottingIds)->pluck('id');
        
        // Hapus child data (presensi) terlebih dahulu agar tidak kena foreign key constraint error
        \App\Models\Presensi::whereIn('id_rombel_jadwal_pelajaran', $jadwalLamaIds)->delete();
        
        // Baru hapus jadwalnya
        RombelJadwalPelajaran::whereIn('id', $jadwalLamaIds)->delete();

        if (count($dataInsert) > 0) {
            RombelJadwalPelajaran::insert($dataInsert);
        }

        return redirect()->route('admin.rombel-jadwal.index')->with('success', 'Jadwal pelajaran berhasil disimpan dan bebas dari bentrok!');
    }

    public function checkClash(Request $request, $id_kelas)
    {
        $activeYear = session('tahun_ajar_id');

        if (!$request->has('hari')) {
            return response()->json(['has_conflict' => false, 'conflicts' => []]);
        }

        $plottingMapelList = RombelMataPelajaran::where('id_tahun_ajar', $activeYear)->get()->keyBy('id');

        // Ambil semua jadwal dari KELAS LAIN
        $jadwalKelasLain = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran', 'rombelMapel.guru', 'ruangan'])
            ->whereHas('rombelMapel', function($q) use ($activeYear, $id_kelas) {
                $q->where('id_tahun_ajar', $activeYear)
                  ->where('id_kelas', '!=', $id_kelas);
            })->get();

        $conflicts = [];
        $count = count($request->hari);
        $validRows = [];

        for ($i = 0; $i < $count; $i++) {
            $hari_i = $request->hari[$i];
            $id_plot_i = $request->id_rombel_mata_pelajaran[$i];
            $mulai_i = $request->jam_mulai[$i] ? $request->jam_mulai[$i] . ':00' : null;
            $selesai_i = $request->jam_selesai[$i] ? $request->jam_selesai[$i] . ':00' : null;
            $ruangan_i = $request->id_ruangan[$i];

            // Abaikan jika baris belum lengkap
            if (empty($hari_i) || empty($id_plot_i) || empty($mulai_i) || empty($selesai_i) || empty($ruangan_i)) {
                continue; 
            }

            // Validasi Logika Waktu
            if ($mulai_i >= $selesai_i) {
                $conflicts[$i] = "Waktu Mulai harus lebih kecil dari Waktu Selesai.";
                continue;
            }

            $guru_id_i = $plottingMapelList[$id_plot_i]->id_guru ?? null;

            // A. CEK BENTROK INTERNAL (dengan baris lain di form ini)
            $bentrokInternal = false;
            foreach ($validRows as $j => $rowJ) {
                if ($rowJ['hari'] == $hari_i) {
                    if ($mulai_i < $rowJ['jam_selesai'] && $selesai_i > $rowJ['jam_mulai']) {
                        // Cek bentrok ruangan
                        if ($ruangan_i == $rowJ['id_ruangan']) {
                            $conflicts[$i] = "Bentrok Ruangan! Tumpang tindih dengan baris ke-" . ($j + 1) . ".";
                            $bentrokInternal = true;
                            break;
                        }

                        // Cek bentrok guru
                        if ($guru_id_i == $rowJ['id_guru']) {
                            $conflicts[$i] = "Bentrok Guru! Tumpang tindih mengajar dengan baris ke-" . ($j + 1) . ".";
                            $bentrokInternal = true;
                            break;
                        }
                    }
                }
            }

            if ($bentrokInternal) {
                continue;
            }

            $validRows[$i] = [
                'hari' => $hari_i,
                'jam_mulai' => $mulai_i,
                'jam_selesai' => $selesai_i,
                'id_ruangan' => $ruangan_i,
                'id_guru' => $guru_id_i
            ];

            // B. CEK BENTROK EKSTERNAL (dengan kelas lain di DB)
            foreach ($jadwalKelasLain as $db) {
                if ($db->hari == $hari_i) {
                    if ($mulai_i < $db->jam_selesai && $selesai_i > $db->jam_mulai) {
                        // Bentrok Ruangan
                        if ($ruangan_i == $db->id_ruangan) {
                            $nama_ruangan = $db->ruangan->nama_ruangan ?? 'Ruangan';
                            $nama_kelas = $db->rombelMapel->kelas->nama ?? 'Kelas Lain';
                            $conflicts[$i] = "BENTROK RUANGAN! $nama_ruangan sedang digunakan oleh $nama_kelas pada jam " . substr($db->jam_mulai, 0, 5) . " - " . substr($db->jam_selesai, 0, 5) . ".";
                            break;
                        }

                        // Bentrok Guru
                        if ($guru_id_i == $db->rombelMapel->id_guru) {
                            $nama_guru = $db->rombelMapel->guru->nama ?? 'Guru';
                            $nama_kelas = $db->rombelMapel->kelas->nama ?? 'Kelas Lain';
                            $conflicts[$i] = "BENTROK GURU! $nama_guru sudah memiliki jadwal mengajar di $nama_kelas pada jam " . substr($db->jam_mulai, 0, 5) . " - " . substr($db->jam_selesai, 0, 5) . ".";
                            break;
                        }
                    }
                }
            }
        }

        return response()->json([
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts
        ]);
    }
}