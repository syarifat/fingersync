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
        
        $activeYear = \App\Models\TahunAjar::where('status_aktif', true)->first();
        $activeYearId = $activeYear ? $activeYear->id : null;

        // Generate daftar 12 bulan untuk dropdown filter (dari 6 bulan lalu s/d 5 bulan depan)
        $monthsList = [];
        for ($i = -6; $i <= 5; $i++) {
            $m = \Carbon\Carbon::now()->addMonths($i);
            $monthsList[$m->format('Y-m')] = $m->locale('id')->isoFormat('MMMM YYYY');
        }

        // Ambil semua status KBM unik dari database untuk filter
        $statusList = Presensi::whereNotNull('status')
            ->where(function($q) {
                $q->whereNull('tipe_scan')->orWhere('tipe_scan', '!=', 'pulang');
            })
            ->distinct()
            ->pluck('status');

        // Kelas wajib dipilih, set default jika kosong
        $kelas_id = $request->input('kelas_id');
        if (empty($kelas_id) && $kelasList->count() > 0) {
            $kelas_id = $kelasList->first()->id;
        }
        $request->merge(['kelas_id' => $kelas_id]);

        // Pengecekan tipe_presensi === 'pulang'
        if ($request->has('tipe_presensi') && $request->tipe_presensi === 'pulang') {
            // Paksakan tanggal tunggal (default ke hari ini jika kosong)
            $tanggal = $request->input('tanggal');
            if (empty($tanggal)) {
                $tanggal = date('Y-m-d');
            }
            $request->merge(['tanggal' => $tanggal]);

            // Ambil seluruh siswa aktif di kelas
            $siswaList = \App\Models\Siswa::whereHas('rombelKelas', function ($q) use ($kelas_id, $activeYearId) {
                $q->where('id_kelas', $kelas_id);
                if ($activeYearId) {
                    $q->where('id_tahun_ajar', $activeYearId);
                }
            })
            ->where('status', 'Aktif')
            ->withCount(['presensi as total_ais' => function ($pq) use ($activeYearId) {
                $pq->whereIn('status', ['Alpa', 'Alpha', 'Izin', 'Sakit']);
                if ($activeYearId) {
                    $pq->where('id_tahun_ajar', $activeYearId);
                }
            }])
            ->get();

            $collection = collect();
            foreach ($siswaList as $siswa) {
                // Cek data checkout
                $checkoutRecord = Presensi::where('id_siswa', $siswa->id)
                    ->where('tanggal', $tanggal)
                    ->where('tipe_scan', 'pulang')
                    ->first();

                if ($checkoutRecord) {
                    // Masukkan record nyata dengan status virtual
                    $checkoutRecord->status_pulang = 'Sudah Absen Pulang';
                    $checkoutRecord->load(['device.ruangan']);
                    $collection->push($checkoutRecord);
                } else {
                    // Cek check-in KBM hari ini
                    $kbmRecords = Presensi::where('id_siswa', $siswa->id)
                        ->where('tanggal', $tanggal)
                        ->where(function($q) {
                            $q->whereNull('tipe_scan')->orWhere('tipe_scan', '!=', 'pulang');
                        })
                        ->get();

                    // Periksa apakah ada status Hadir / Terlambat
                    $hasAttended = $kbmRecords->contains(fn($r) => in_array($r->status, ['Hadir', 'Terlambat']));

                    $virtual = new Presensi();
                    $virtual->id = null; // Penanda baris virtual
                    $virtual->id_siswa = $siswa->id;
                    $virtual->tanggal = $tanggal;
                    $virtual->jam_scan = '-';
                    $virtual->tipe_scan = 'pulang';
                    $virtual->siswa = $siswa;
                    
                    if ($hasAttended) {
                        $virtual->status = 'Belum Absen Pulang';
                        $virtual->status_pulang = 'Belum Absen Pulang';
                    } else {
                        // Tentukan status spesifik: Sakit, Izin, atau Tidak Masuk (Alpha)
                        $kbmStatuses = $kbmRecords->pluck('status')->unique();
                        if ($kbmStatuses->count() === 1) {
                            $statusUtama = $kbmStatuses->first();
                            if (in_array($statusUtama, ['Sakit', 'Izin'])) {
                                $virtual->status = $statusUtama;
                            } else {
                                $virtual->status = 'Tidak Masuk';
                            }
                        } else {
                            $virtual->status = 'Tidak Masuk';
                        }
                        $virtual->status_pulang = 'Tidak Masuk';
                    }
                    $collection->push($virtual);
                }
            }

            // Saring berdasarkan Status Pulang
            if ($request->has('status_pulang') && $request->status_pulang != '') {
                $collection = $collection->where('status_pulang', $request->status_pulang);
            }

            // Saring berdasarkan Pencarian
            if ($request->has('search') && $request->search != '') {
                $search = strtolower($request->search);
                $collection = $collection->filter(function($row) use ($search) {
                    return str_contains(strtolower($row->siswa->nama ?? ''), $search) 
                        || str_contains(strtolower($row->siswa->nis ?? ''), $search);
                });
            }

            // Urutkan berdasarkan nama siswa ASC
            $collection = $collection->sortBy(function($row) {
                return strtolower($row->siswa->nama ?? '');
            })->values();

            // Paginasi manual untuk virtual collection
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $perPage = 10;
            $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage)->values();
            
            $dataPresensi = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageItems,
                $collection->count(),
                $perPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->all()]
            );

        } else {
            // Logika default untuk Masuk/KBM (Query riil database)
            $query = Presensi::with([
                'siswa' => function ($q) use ($activeYearId) {
                    $q->withCount(['presensi as total_ais' => function ($pq) use ($activeYearId) {
                        $pq->whereIn('status', ['Alpa', 'Alpha', 'Izin', 'Sakit']);
                        if ($activeYearId) {
                            $pq->where('id_tahun_ajar', $activeYearId);
                        }
                    }]);
                },
                'siswa.rombelKelas.kelas', 
                'rombelJadwalPelajaran.rombelMataPelajaran.kelas', 
                'rombelJadwalPelajaran.rombelMataPelajaran.mataPelajaran', 
                'device.ruangan', 
                'tahunAjar',
                'kegiatanSekolah'
            ]);

            // 1. Filter Kelas
            if ($kelas_id) {
                $query->whereHas('siswa.rombelKelas', function ($q) use ($kelas_id) {
                    $q->where('id_kelas', $kelas_id);
                });
            }

            // 2. Filter Mapel
            if ($request->has('mapel_id') && $request->mapel_id != '') {
                $query->whereHas('rombelJadwalPelajaran.rombelMataPelajaran', function ($q) use ($request) {
                    $q->where('id_mata_pelajaran', $request->mapel_id);
                });
            }

            // 2.b. Filter Status KBM
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            // 2.c. Filter Tipe Presensi (Masuk)
            $query->where(function($q) {
                $q->whereNull('tipe_scan')->orWhere('tipe_scan', '!=', 'pulang');
            });

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
        }

        return view('admin.presensi.index', compact('dataPresensi', 'kelasList', 'mapelList', 'statusList', 'monthsList'));
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

        // Pre-load Kegiatan Sekolah Serentak dates and presensi records for optimization
        $datesWithKegiatanSerentak = \App\Models\KegiatanSekolah::where('tipe', 'serentak')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->pluck('tanggal')
            ->map(fn($t) => \Carbon\Carbon::parse($t)->toDateString())
            ->toArray();

        $presensiKegiatanBulanIni = Presensi::whereNotNull('id_siswa')
            ->whereNotNull('id_kegiatan_sekolah')
            ->whereHas('siswa.rombelKelas', function($q) use ($request) {
                $q->where('id_kelas', $request->kelas_id);
            })
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

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
                $tglStr = $dateObj->format('Y-m-d');
                if (in_array($dayNameEng, $scheduledEngDays)) {
                    $datesInfoForThisMapel[$d] = [
                        'day' => $d,
                        'isWeekend' => $dateObj->isWeekend(),
                        'isHoliday' => \App\Models\HariLibur::isHoliday($tglStr)
                    ];
                }
            }

            if (empty($datesInfoForThisMapel)) {
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $dateObj = Carbon::createFromDate($tahun, $bulan, $d);
                    $tglStr = $dateObj->format('Y-m-d');
                    $datesInfoForThisMapel[$d] = [
                        'day' => $d,
                        'isWeekend' => $dateObj->isWeekend(),
                        'isHoliday' => \App\Models\HariLibur::isHoliday($tglStr)
                    ];
                }
            }

            $engToIndoDays = array_flip($indoToEngDays);

            $matrix = [];
            foreach ($siswaList as $siswa) {
                $row = [];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $tglStr = Carbon::createFromDate($tahun, $bulan, $d)->format('Y-m-d');
                    
                    if (\App\Models\HariLibur::isHoliday($tglStr)) {
                        $row[$d] = 'L';
                    } elseif (in_array($tglStr, $datesWithKegiatanSerentak)) {
                        $hasPresensiKegiatan = $presensiKegiatanBulanIni
                            ->where('id_siswa', $siswa->id)
                            ->where('tanggal', $tglStr)
                            ->isNotEmpty();
                        $row[$d] = $hasPresensiKegiatan ? 'H' : '';
                    } else {
                        $dateObj = Carbon::createFromDate($tahun, $bulan, $d);
                        $dayNameEng = $dateObj->format('l');
                        $hariIndo = $engToIndoDays[$dayNameEng] ?? 'Senin';
                        
                        // Cari jadwal KBM kelas ini untuk mapel ini
                        $jdwl = RombelJadwalPelajaran::where('hari', $hariIndo)
                            ->whereHas('rombelMataPelajaran', function($q) use ($kelas, $mapel) {
                                $q->where('id_kelas', $kelas->id)
                                  ->where('id_mata_pelajaran', $mapel->id);
                            })
                            ->first();
                            
                        $isGuruLibur = false;
                        if ($jdwl) {
                            $isGuruLibur = \App\Models\GuruKbmKhusus::where('id_rombel_jadwal_pelajaran', $jdwl->id)
                                ->whereDate('tanggal', $tglStr)
                                ->where('status', 'izin_libur')
                                ->exists();
                        }
                        
                        if ($isGuruLibur) {
                            $row[$d] = 'GL';
                        } else {
                            $pList = $presensiList->where('id_siswa', $siswa->id)->where('tanggal', $tglStr);
                            
                            if ($pList->isEmpty()) {
                                $row[$d] = '';
                            } else {
                                // Aggregate daily status: Worst-case logic for this specific mapel
                                $statuses = $pList->pluck('status')->toArray();
                                if (in_array('Alpa', $statuses) || in_array('Alpha', $statuses)) $row[$d] = 'A';
                                elseif (in_array('Sakit', $statuses)) $row[$d] = 'S';
                                elseif (in_array('Izin', $statuses)) $row[$d] = 'I';
                                elseif (in_array('Terlambat', $statuses)) $row[$d] = 'T';
                                elseif (in_array('Hadir', $statuses)) $row[$d] = 'H';
                                else $row[$d] = '';
                            }
                        }
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
            'status'    => 'required|in:Hadir,Izin,Sakit,Terlambat,Alpa,Alpha',
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
            'status' => 'required|in:Hadir,Izin,Sakit,Terlambat,Alpa,Alpha',
        ]);

        $presensi->update([
            'status' => $request->status,
        ]);

        return redirect()->route('admin.presensi.index')->with('success', 'Status absensi berhasil diperbarui.');
    }

    public function detailAis($siswa_id)
    {
        $siswa = \App\Models\Siswa::with('rombelKelas.kelas')->findOrFail($siswa_id);
        
        $activeYear = \App\Models\TahunAjar::where('status_aktif', true)->first();
        $activeYearId = $activeYear ? $activeYear->id : null;

        $query = Presensi::with([
            'rombelJadwalPelajaran.rombelMataPelajaran.mataPelajaran',
            'kegiatanSekolah',
            'device.ruangan'
        ])
        ->where('id_siswa', $siswa_id)
        ->whereIn('status', ['Alpa', 'Alpha', 'Izin', 'Sakit']);

        if ($activeYearId) {
            $query->where('id_tahun_ajar', $activeYearId);
        }

        $records = $query->orderBy('tanggal', 'desc')->orderBy('jam_scan', 'desc')->get();

        // Calculate counts
        $counts = [
            'Alpha' => $records->filter(fn($r) => in_array($r->status, ['Alpa', 'Alpha']))->count(),
            'Izin' => $records->where('status', 'Izin')->count(),
            'Sakit' => $records->where('status', 'Sakit')->count(),
        ];

        return view('admin.presensi.detail_ais', compact('siswa', 'records', 'counts'));
    }

    public function exportSemesterPdf(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $kelas = \App\Models\Kelas::findOrFail($request->kelas_id);
        
        $tahunAjar = \App\Models\TahunAjar::where('status_aktif', 1)->first();
        if (!$tahunAjar) {
            return back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        $semesterLabel = "Semester " . ($tahunAjar->semester ?? 'Ganjil');
        $tahunAjarLabel = "Tahun Ajaran " . ($tahunAjar->tahun ?? '-');

        // Ambil daftar siswa aktif di kelas
        $siswaList = \App\Models\RombelKelas::with('siswa')
            ->where('id_kelas', $request->kelas_id)
            ->where('id_tahun_ajar', $tahunAjar->id)
            ->get()->pluck('siswa')->filter()->sortBy('nama')->values();

        // Ambil daftar mata pelajaran di kelas ini
        $mapelList = \App\Models\MataPelajaran::whereHas('rombelMataPelajaran', function($q) use ($request, $tahunAjar) {
            $q->where('id_kelas', $request->kelas_id)
              ->where('id_tahun_ajar', $tahunAjar->id);
        })->orderBy('nama', 'asc')->get();

        // Ambil seluruh data presensi ketidakhadiran (AIS) untuk kelas dan tahun ajar ini
        $presensiRecords = Presensi::whereIn('status', ['Alpa', 'Alpha', 'Izin', 'Sakit'])
            ->where('id_tahun_ajar', $tahunAjar->id)
            ->whereHas('siswa.rombelKelas', function($q) use ($request, $tahunAjar) {
                $q->where('id_kelas', $request->kelas_id)
                  ->where('id_tahun_ajar', $tahunAjar->id);
            })
            ->with(['rombelJadwalPelajaran.rombelMataPelajaran'])
            ->get();

        $matrix = [];
        foreach ($siswaList as $siswa) {
            $siswaMatrix = [
                'nama' => $siswa->nama,
                'mapel_ais' => [],
                'total_ais' => 0
            ];
            
            // Presensi siswa ini saja
            $siswaPresensi = $presensiRecords->where('id_siswa', $siswa->id);
            
            foreach ($mapelList as $mapel) {
                $mapelPresensi = $siswaPresensi->filter(function($p) use ($mapel) {
                    return $p->rombelJadwalPelajaran 
                        && $p->rombelJadwalPelajaran->rombelMataPelajaran 
                        && $p->rombelJadwalPelajaran->rombelMataPelajaran->id_mata_pelajaran == $mapel->id;
                });

                $aCount = $mapelPresensi->filter(fn($p) => in_array($p->status, ['Alpa', 'Alpha']))->count();
                $iCount = $mapelPresensi->where('status', 'Izin')->count();
                $sCount = $mapelPresensi->where('status', 'Sakit')->count();
                $totalAis = $aCount + $iCount + $sCount;

                $siswaMatrix['mapel_ais'][$mapel->id] = [
                    'A' => $aCount,
                    'I' => $iCount,
                    'S' => $sCount,
                    'Total' => $totalAis
                ];
                $siswaMatrix['total_ais'] += $totalAis;
            }
            $matrix[] = $siswaMatrix;
        }

        $fileName = "Rekap_AIS_Semester_{$kelas->nama}_{$tahunAjar->tahun}.pdf";
        $fileName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $fileName);

        $pdf = Pdf::loadView('admin.presensi.pdf_semester', compact('kelas', 'tahunAjar', 'semesterLabel', 'tahunAjarLabel', 'mapelList', 'matrix'))
                  ->setPaper('a4', 'landscape');

        return $pdf->download($fileName);
    }
}