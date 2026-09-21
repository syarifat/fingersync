<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\RombelKelas;
use App\Models\RombelMataPelajaran;
use App\Models\Presensi;
use App\Models\Siswa;
use App\Models\Device;
use App\Models\TahunAjar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BkPresensiController extends Controller
{
    /**
     * Helper untuk memvalidasi dan mengambil data Guru BK yang sedang login.
     */
    protected function getGuruBk()
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();
        if (!$guru->is_bk) {
            abort(403, 'Akses Ditolak. Halaman ini khusus untuk Guru Bimbingan Konseling (BK).');
        }
        return $guru;
    }

    /**
     * Halaman Rekap & Manajemen Presensi Siswa untuk Guru BK
     * Mendukung multi-kelas binaan (bisa 5+ kelas).
     */
    public function index(Request $request)
    {
        $guru = $this->getGuruBk();

        // 1. Ambil seluruh ID kelas binaan Guru BK ini
        // Menggabungkan siswa yang ditugaskan sebagai Guru BK di rombel_kelas & kelas yang diajar di rombel_mata_pelajaran
        $kelasBkIds = RombelKelas::where('id_guru_bk', $guru->id)->pluck('id_kelas')->toArray();
        $kelasMapelIds = RombelMataPelajaran::where('id_guru', $guru->id)->pluck('id_kelas')->toArray();
        $allKelasIds = array_values(array_unique(array_merge($kelasBkIds, $kelasMapelIds)));

        $kelasList = Kelas::whereIn('id', $allKelasIds)->orderBy('nama', 'asc')->get();

        if ($kelasList->isEmpty()) {
            return view('guru.bk.index', [
                'guru' => $guru,
                'kelasList' => collect(),
                'selectedKelasId' => null,
                'selectedKelas' => null,
                'selectedTanggal' => Carbon::now()->format('Y-m-d'),
                'items' => collect(),
                'stats' => ['total' => 0, 'hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpha' => 0, 'belum_absen' => 0],
                'statusList' => ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha', 'Belum Absen'],
                'emptyState' => true,
            ]);
        }

        // 2. Filter Kelas (default ke kelas pertama jika tidak dipilih, atau 'all' untuk semua kelas binaan)
        $selectedKelasId = $request->input('kelas_id');
        if (empty($selectedKelasId)) {
            $selectedKelasId = (string)$kelasList->first()->id;
        }

        // Tentukan kelas yang aktif difilter
        if ($selectedKelasId === 'all') {
            $targetKelasIds = $allKelasIds;
            $selectedKelas = null;
        } else {
            $targetKelasIds = [(int)$selectedKelasId];
            $selectedKelas = $kelasList->firstWhere('id', (int)$selectedKelasId);
        }

        // 3. Filter Tanggal (default ke hari ini)
        $selectedTanggal = $request->input('tanggal', Carbon::now()->format('Y-m-d'));
        $selectedStatus = $request->input('status');
        $search = $request->input('search');

        // 4. Ambil Tahun Ajar Aktif
        $activeYear = TahunAjar::where('status_aktif', true)->first();
        $activeYearId = $activeYear ? $activeYear->id : null;

        // 5. Query Seluruh Siswa di Kelas Binaan Terpilih
        $siswaQuery = Siswa::whereHas('rombelKelas', function ($q) use ($targetKelasIds, $activeYearId) {
            $q->whereIn('id_kelas', $targetKelasIds);
            if ($activeYearId) {
                $q->where('id_tahun_ajar', $activeYearId);
            }
        })
        ->where('status', 'Aktif')
        ->with(['rombelKelas.kelas'])
        ->withCount(['presensi as total_ais' => function ($pq) use ($activeYearId) {
            $pq->whereIn('status', ['Alpa', 'Alpha', 'Izin', 'Sakit']);
            if ($activeYearId) {
                $pq->where('id_tahun_ajar', $activeYearId);
            }
        }]);

        if (!empty($search)) {
            $siswaQuery->where(function ($sq) use ($search) {
                $sq->where('nama', 'like', "%{$search}%")
                   ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $siswaList = $siswaQuery->orderBy('nama', 'asc')->get();

        // 6. Ambil Data Presensi Siswa pada Tanggal Terpilih
        $presensiRecords = Presensi::whereIn('id_siswa', $siswaList->pluck('id'))
            ->where('tanggal', $selectedTanggal)
            ->where(function ($q) {
                $q->whereNull('tipe_scan')->orWhere('tipe_scan', '!=', 'pulang');
            })
            ->with(['device.ruangan', 'rombelJadwalPelajaran.rombelMataPelajaran.mataPelajaran'])
            ->orderBy('jam_scan', 'asc')
            ->get()
            ->groupBy('id_siswa');

        // 7. Format data gabungan Siswa + Status Presensi
        $stats = [
            'total' => $siswaList->count(),
            'hadir' => 0,
            'terlambat' => 0,
            'sakit' => 0,
            'izin' => 0,
            'alpha' => 0,
            'belum_absen' => 0,
        ];

        $items = $siswaList->map(function ($siswa) use ($presensiRecords, &$stats) {
            $records = $presensiRecords->get($siswa->id, collect());
            $primaryPresensi = $records->first();

            $rawStatus = $primaryPresensi ? $primaryPresensi->status : 'Belum Absen';
            if ($rawStatus === 'Alpa') {
                $rawStatus = 'Alpha';
            }

            // Hitung statistik
            match ($rawStatus) {
                'Hadir' => $stats['hadir']++,
                'Terlambat' => $stats['terlambat']++,
                'Sakit' => $stats['sakit']++,
                'Izin' => $stats['izin']++,
                'Alpha' => $stats['alpha']++,
                default => $stats['belum_absen']++,
            };

            $kelasNama = $siswa->rombelKelas && $siswa->rombelKelas->kelas ? $siswa->rombelKelas->kelas->nama : '-';
            $ruanganNama = $primaryPresensi && $primaryPresensi->device && $primaryPresensi->device->ruangan ? $primaryPresensi->device->ruangan->nama_ruangan : 'Manual/Sistem';
            $mapelNama = $primaryPresensi && $primaryPresensi->rombelJadwalPelajaran && $primaryPresensi->rombelJadwalPelajaran->rombelMataPelajaran && $primaryPresensi->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran
                ? $primaryPresensi->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran->nama
                : ($primaryPresensi ? 'Presensi Harian / BK' : '-');

            return (object) [
                'siswa' => $siswa,
                'presensi' => $primaryPresensi,
                'all_records' => $records,
                'status' => $rawStatus,
                'jam_scan' => $primaryPresensi ? $primaryPresensi->jam_scan : null,
                'kelas_nama' => $kelasNama,
                'ruangan' => $ruanganNama,
                'mapel' => $mapelNama,
            ];
        });

        // 8. Terapkan filter status jika dipilih
        if (!empty($selectedStatus)) {
            $items = $items->filter(function ($item) use ($selectedStatus) {
                if ($selectedStatus === 'Alpha') {
                    return in_array($item->status, ['Alpha', 'Alpa']);
                }
                return strcasecmp($item->status, $selectedStatus) === 0;
            })->values();
        }

        $statusList = ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha', 'Belum Absen'];

        return view('guru.bk.index', compact(
            'guru',
            'kelasList',
            'selectedKelasId',
            'selectedKelas',
            'selectedTanggal',
            'selectedStatus',
            'search',
            'items',
            'stats',
            'statusList'
        ));
    }

    /**
     * Memperbarui status presensi siswa (single record)
     */
    public function update(Request $request, $id)
    {
        $this->getGuruBk();

        $request->validate([
            'status' => 'required|in:Hadir,Terlambat,Sakit,Izin,Alpha,Alpa',
            'jam_scan' => 'nullable|date_format:H:i:s,H:i',
        ]);

        $presensi = Presensi::findOrFail($id);

        $updateData = [
            'status' => $request->status === 'Alpa' ? 'Alpha' : $request->status,
        ];

        if ($request->filled('jam_scan')) {
            $updateData['jam_scan'] = strlen($request->jam_scan) === 5 ? $request->jam_scan . ':00' : $request->jam_scan;
        }

        $presensi->update($updateData);

        return back()->with('success', "Status presensi {$presensi->siswa->nama} berhasil diubah menjadi {$presensi->status}.");
    }

    /**
     * Menyimpan data presensi baru atau memperbarui jika belum ada presensi di tanggal tersebut
     * (Misal siswa tidak hadir/belum tap, lalu Guru BK menerima surat sakit/izin dari orang tua)
     */
    public function storeOrUpdate(Request $request)
    {
        $this->getGuruBk();

        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Terlambat,Sakit,Izin,Alpha,Alpa',
            'jam_scan' => 'nullable',
        ]);

        $status = $request->status === 'Alpa' ? 'Alpha' : $request->status;
        $jamScan = $request->filled('jam_scan')
            ? (strlen($request->jam_scan) === 5 ? $request->jam_scan . ':00' : $request->jam_scan)
            : Carbon::now()->format('H:i:s');

        $activeYear = TahunAjar::where('status_aktif', true)->first();
        $device = Device::first();

        // Cari apakah sudah ada record presensi KBM pada tanggal tersebut
        $presensi = Presensi::where('id_siswa', $request->siswa_id)
            ->where('tanggal', $request->tanggal)
            ->where(function ($q) {
                $q->whereNull('tipe_scan')->orWhere('tipe_scan', '!=', 'pulang');
            })
            ->first();

        if ($presensi) {
            $presensi->update([
                'status' => $status,
                'jam_scan' => $jamScan,
            ]);
        } else {
            $presensi = Presensi::create([
                'id_siswa' => $request->siswa_id,
                'id_rombel_jadwal_pelajaran' => null,
                'tanggal' => $request->tanggal,
                'jam_scan' => $jamScan,
                'id_device' => $device ? $device->id : 1,
                'status' => $status,
                'id_tahun_ajar' => $activeYear ? $activeYear->id : 1,
            ]);
        }

        $siswa = Siswa::find($request->siswa_id);
        return back()->with('success', "Presensi {$siswa->nama} pada tanggal {$request->tanggal} berhasil disimpan ({$status}).");
    }

    /**
     * Memperbarui status presensi banyak siswa sekaligus (Batch Update)
     */
    public function batchUpdate(Request $request)
    {
        $this->getGuruBk();

        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswa,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Terlambat,Sakit,Izin,Alpha,Alpa',
        ]);

        $status = $request->status === 'Alpa' ? 'Alpha' : $request->status;
        $activeYear = TahunAjar::where('status_aktif', true)->first();
        $device = Device::first();
        $jamScan = Carbon::now()->format('H:i:s');

        $updatedCount = 0;
        foreach ($request->siswa_ids as $siswaId) {
            $presensi = Presensi::where('id_siswa', $siswaId)
                ->where('tanggal', $request->tanggal)
                ->where(function ($q) {
                    $q->whereNull('tipe_scan')->orWhere('tipe_scan', '!=', 'pulang');
                })
                ->first();

            if ($presensi) {
                $presensi->update(['status' => $status]);
            } else {
                Presensi::create([
                    'id_siswa' => $siswaId,
                    'id_rombel_jadwal_pelajaran' => null,
                    'tanggal' => $request->tanggal,
                    'jam_scan' => $jamScan,
                    'id_device' => $device ? $device->id : 1,
                    'status' => $status,
                    'id_tahun_ajar' => $activeYear ? $activeYear->id : 1,
                ]);
            }
            $updatedCount++;
        }

        return back()->with('success', "Berhasil memperbarui status presensi {$updatedCount} siswa menjadi {$status} pada tanggal {$request->tanggal}.");
    }

    /**
     * Menampilkan riwayat ketidakhadiran (AIS - Alpha, Izin, Sakit) siswa binaan
     */
    public function detailAis($siswa_id)
    {
        $this->getGuruBk();

        $siswa = Siswa::with('rombelKelas.kelas')->findOrFail($siswa_id);
        $activeYear = TahunAjar::where('status_aktif', true)->first();
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

        $counts = [
            'Alpha' => $records->filter(fn($r) => in_array($r->status, ['Alpa', 'Alpha']))->count(),
            'Izin' => $records->where('status', 'Izin')->count(),
            'Sakit' => $records->where('status', 'Sakit')->count(),
        ];

        return view('guru.bk.detail_ais', compact('siswa', 'records', 'counts'));
    }
}
