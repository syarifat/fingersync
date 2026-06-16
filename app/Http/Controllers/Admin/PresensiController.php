<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\TahunAjar;
use App\Models\RombelJadwalPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PresensiController extends Controller
{
    /**
     * Menampilkan daftar absensi
     */
    public function index(Request $request)
    {
        $kelasList = \App\Models\Kelas::orderBy('nama', 'asc')->get();
        $mapelList = \App\Models\MataPelajaran::orderBy('nama', 'asc')->get();

        // Kelas wajib dipilih, set default jika kosong
        $kelas_id = $request->kelas_id;
        if (!$kelas_id && $kelasList->count() > 0) {
            $kelas_id = $kelasList->first()->id;
            $request->merge(['kelas_id' => $kelas_id]);
        }

        $query = Presensi::with([
            'siswa.rombelKelas.kelas', 
            'rombelJadwalPelajaran.rombelMataPelajaran.kelas', 
            'rombelJadwalPelajaran.rombelMataPelajaran.mataPelajaran', 
            'device', 
            'tahunAjar',
            'kegiatanSekolah'
        ]);

        // 1. Filter Kelas berdasarkan kelas terdaftar siswa (agar absensi kegiatan serentak tidak hilang)
        if ($kelas_id) {
            $query->whereHas('siswa.rombelKelas', function ($q) use ($kelas_id) {
                $q->where('id_kelas', $kelas_id);
            });
        }

        // 2. Filter Mapel (Opsional)
        if ($request->has('mapel_id') && $request->mapel_id != '') {
            $query->whereHas('rombelJadwalPelajaran.rombelMataPelajaran', function ($q) use ($request) {
                $q->where('id_mata_pelajaran', $request->mapel_id);
            });
        }

        // 3. Filter Waktu (Harian atau Bulanan)
        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->where('tanggal', $request->tanggal);
        } elseif ($request->has('bulan') && $request->bulan != '') {
            $query->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                  ->whereYear('tanggal', date('Y', strtotime($request->bulan)));
        }

        // 4. Pencarian Siswa (Nama / NIS)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $dataPresensi = $query->latest()->paginate(10)->withQueryString();

        return view('admin.presensi.index', compact('dataPresensi', 'kelasList', 'mapelList'));
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'bulan'    => 'required',
        ]);

        \Carbon\Carbon::setLocale('id');
        $kelas    = \App\Models\Kelas::findOrFail($request->kelas_id);
        $bulanStr = $request->bulan;
        $tahun    = (int) date('Y', strtotime($bulanStr . '-01'));
        $bulan    = (int) date('m', strtotime($bulanStr . '-01'));
        $bulanLabel = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->isoFormat('MMMM YYYY');
        $daysInMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        $tahunAjar = \App\Models\TahunAjar::where('status_aktif', 1)->first();

        // Get filter mapel info if applied
        $mapelInfo = null;
        if ($request->mapel_id) {
            $mapelObj = \App\Models\MataPelajaran::find($request->mapel_id);
            if ($mapelObj) {
                $mapelInfo = $mapelObj->nama;
            }
        }

        $siswaList = \App\Models\RombelKelas::with('siswa')
            ->where('id_kelas', $request->kelas_id)
            ->when($tahunAjar, fn($q) => $q->where('id_tahun_ajar', $tahunAjar->id))
            ->get()->pluck('siswa')->filter()->sortBy('nama')->values();

        // Cari mata pelajaran unik yang diajarkan di kelas ini
        $mapelQuery = \App\Models\MataPelajaran::whereHas('rombelMataPelajaran', function($q) use ($request, $tahunAjar) {
            $q->where('id_kelas', $request->kelas_id);
            if ($tahunAjar) {
                $q->where('id_tahun_ajar', $tahunAjar->id);
            }
        });
        
        if ($request->mapel_id) {
            $mapelQuery->where('id', $request->mapel_id);
        }
        $mapelList = $mapelQuery->get();

        // Build array of dates properties (for header and weekend formatting)
        $datesInfo = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $datesInfo[$d] = [
                'day' => $d,
                'isWeekend' => Carbon::createFromDate($tahun, $bulan, $d)->isWeekend()
            ];
        }

        $dataPerMapel = [];
        foreach ($mapelList as $mapel) {
            $presensiList = Presensi::whereNotNull('id_siswa')
                ->whereHas('rombelJadwalPelajaran.rombelMataPelajaran', function($q) use ($mapel, $request) {
                    $q->where('id_mata_pelajaran', $mapel->id)
                      ->where('id_kelas', $request->kelas_id);
                })
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            // Jika benar-benar kosong presensi mapel ini, boleh dilewati agar pdf tidak memunculkan tabel kosong melompong
            if ($presensiList->isEmpty()) continue; 

            // Hitung jadwal khusus hari mapel ini
            $hariJadwal = \App\Models\RombelJadwalPelajaran::whereHas('rombelMataPelajaran', function($q) use ($mapel, $request, $tahunAjar) {
                $q->where('id_mata_pelajaran', $mapel->id)
                  ->where('id_kelas', $request->kelas_id);
                if ($tahunAjar) {
                    $q->where('id_tahun_ajar', $tahunAjar->id);
                }
            })->pluck('hari')->unique()->toArray();

            $indoToEngDays = [
                'Senin' => 'Monday',
                'Selasa' => 'Tuesday',
                'Rabu' => 'Wednesday',
                'Kamis' => 'Thursday',
                'Jumat' => 'Friday',
                'Sabtu' => 'Saturday',
                'Minggu' => 'Sunday'
            ];

            $scheduledEngDays = [];
            foreach ($hariJadwal as $h) {
                if (isset($indoToEngDays[$h])) {
                    $scheduledEngDays[] = $indoToEngDays[$h];
                }
            }

            $datesInfoForThisMapel = [];
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateObj = Carbon::createFromDate($tahun, $bulan, $d);
                $dayNameEng = $dateObj->format('l');
                if (in_array($dayNameEng, $scheduledEngDays)) {
                    $datesInfoForThisMapel[$d] = [
                        'day' => $d,
                        'isWeekend' => $dateObj->isWeekend()
                    ];
                }
            }

            if (empty($datesInfoForThisMapel)) {
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $datesInfoForThisMapel[$d] = [
                        'day' => $d,
                        'isWeekend' => Carbon::createFromDate($tahun, $bulan, $d)->isWeekend()
                    ];
                }
            }

            $matrix = [];
            foreach ($siswaList as $siswa) {
                $row = [];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $tglStr = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
                    $pList = $presensiList->where('id_siswa', $siswa->id)->where('tanggal', $tglStr);
                    
                    if ($pList->isEmpty()) {
                        $row[$d] = '';
                    } else {
                        // Aggregate daily status: Worst-case logic for this specific mapel
                        $statuses = $pList->pluck('status')->toArray();
                        if (in_array('Alpa', $statuses)) $row[$d] = 'A';
                        elseif (in_array('Sakit', $statuses)) $row[$d] = 'S';
                        elseif (in_array('Izin', $statuses)) $row[$d] = 'I';
                        elseif (in_array('Terlambat', $statuses)) $row[$d] = 'T';
                        elseif (in_array('Hadir', $statuses)) $row[$d] = 'H';
                        else $row[$d] = '';
                    }
                }
                $matrix[$siswa->id] = $row;
            }

            $dataPerMapel[] = [
                'nama_mapel'    => $mapel->nama,
                'matrix'        => $matrix,
                'datesInfo'     => $datesInfoForThisMapel
            ];
        }

        // Tambahkan virtual mapel untuk "Kegiatan Sekolah (Serentak)" jika ada kegiatan dan mapel_id tidak sedang difilter
        if (!$request->mapel_id) {
            $kegiatanSekolahList = \App\Models\KegiatanSekolah::where('tipe', 'serentak')
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            if ($kegiatanSekolahList->isNotEmpty()) {
                $presensiKegiatan = Presensi::whereNotNull('id_siswa')
                    ->whereNotNull('id_kegiatan_sekolah')
                    ->whereHas('siswa.rombelKelas', function($q) use ($request) {
                        $q->where('id_kelas', $request->kelas_id);
                    })
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->get();

                $datesInfoForKegiatan = [];
                foreach ($kegiatanSekolahList as $kegiatan) {
                    $dateObj = Carbon::parse($kegiatan->tanggal);
                    $d = (int) $dateObj->format('d');
                    $datesInfoForKegiatan[$d] = [
                        'day' => $d,
                        'isWeekend' => $dateObj->isWeekend()
                    ];
                }
                ksort($datesInfoForKegiatan); // Urutkan tanggal

                $matrixKegiatan = [];
                foreach ($siswaList as $siswa) {
                    $row = [];
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $row[$d] = '';
                    }
                    foreach ($datesInfoForKegiatan as $day => $info) {
                        $tglStr = Carbon::createFromDate($tahun, $bulan, $day)->format('Y-m-d');
                        $hasPresensi = $presensiKegiatan->where('id_siswa', $siswa->id)
                            ->where('tanggal', $tglStr)
                            ->isNotEmpty();
                        $row[$day] = $hasPresensi ? 'H' : '';
                    }
                    $matrixKegiatan[$siswa->id] = $row;
                }

                $dataPerMapel[] = [
                    'nama_mapel'    => 'Kegiatan Sekolah (Serentak)',
                    'matrix'        => $matrixKegiatan,
                    'datesInfo'     => $datesInfoForKegiatan
                ];
            }
        }

        $namaMapel = $mapelInfo ? $mapelInfo : 'Semua Mapel';
        // Membersihkan karakter yang dilarang pada nama file sistem operasi
        $namaMapelSafe = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $namaMapel);
        $kelasSafe = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $kelas->nama);
        
        $fileName = "Data Presensi_{$bulanLabel}_{$kelasSafe}_{$namaMapelSafe}.pdf";

        $pdf = Pdf::loadView('admin.presensi.pdf', compact('kelas', 'siswaList', 'dataPerMapel', 'bulanLabel', 'mapelInfo'))
                  ->setPaper('a4', 'landscape');
                  
        return $pdf->download($fileName);
    }

    /**
     * Menyimpan data (Manual Input oleh Admin atau Test API)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'id_siswa'  => 'required|exists:siswa,id',
            'id_device' => 'required|exists:device,id',
            'status'    => 'required|in:Hadir,Izin,Sakit,Terlambat,Alpa',
        ]);

        // 2. Tentukan Waktu Sekarang
        $tanggalSekarang = Carbon::now()->format('Y-m-d');
        $jamSekarang     = Carbon::now()->format('H:i:s');
        $hariIni         = Carbon::now()->locale('id')->isoFormat('dddd'); // Contoh: "Senin"

        // 3. Cari Tahun Ajar yang Aktif (Asumsi ada kolom status = 'aktif' atau '1')
        $tahunAjarAktif = TahunAjar::where('status', 'aktif')->first();

        if (!$tahunAjarAktif) {
            return back()->with('error', 'Tahun ajar aktif tidak ditemukan! Harap setting tahun ajar terlebih dahulu.');
        }

        // 4. Cari Jadwal Pelajaran yang sedang berlangsung
        // Logika: Cari jadwal di hari ini, dimana jam sekarang berada di antara jam mulai dan selesai
        $jadwalAktif = RombelJadwalPelajaran::where('hari', $hariIni)
            ->where('jam_mulai', '<=', $jamSekarang)
            ->where('jam_selesai', '>=', $jamSekarang)
            // Opsional: Filter berdasarkan kelas siswa jika perlu
            // ->where('id_kelas', $siswa->id_kelas) 
            ->first();

        // Fallback: Jika admin input manual di luar jam pelajaran, kita bisa set null (jika db nullable) 
        // atau ambil jadwal default/dummy. Di sini saya return error jika strict.
        if (!$jadwalAktif) {
            // OPSI A: Tolak jika tidak ada jadwal
            return back()->with('error', "Tidak ada jadwal pelajaran aktif pada hari $hariIni jam $jamSekarang.");

            // OPSI B (Alternatif): Jika ingin tetap simpan walau tidak ada jadwal (misal kegiatan ekskul)
            // $id_jadwal = 1; // ID jadwal dummy/umum
        }

        // 5. Simpan Data
        Presensi::create([
            'id_siswa'                   => $request->id_siswa,
            'id_rombel_jadwal_pelajaran' => $jadwalAktif->id, // Ambil ID dari hasil pencarian di atas
            'tanggal'                    => $tanggalSekarang,
            'jam_scan'                   => $jamSekarang,
            'id_device'                  => $request->id_device,
            'status'                     => $request->status,
            'id_tahun_ajar'              => $tahunAjarAktif->id,
        ]);

        return redirect()->route('admin.presensi.index')->with('success', 'Data absensi berhasil disimpan.');
    }

    /**
     * Menampilkan form edit presensi
     */
    public function edit(Presensi $presensi)
    {
        return view('admin.presensi.edit', compact('presensi'));
    }

    /**
     * Mengupdate data presensi
     */
    public function update(Request $request, Presensi $presensi)
    {
        $request->validate([
            'status' => 'required|in:Hadir,Izin,Sakit,Terlambat,Alpa',
        ]);

        $presensi->update([
            'status' => $request->status,
        ]);

        return redirect()->route('admin.presensi.index')->with('success', 'Status absensi berhasil diperbarui.');
    }
}