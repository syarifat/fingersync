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

        $query = Presensi::with(['siswa', 'rombelJadwalPelajaran.rombelMataPelajaran.kelas', 'rombelJadwalPelajaran.rombelMataPelajaran.mataPelajaran', 'device', 'tahunAjar']);

        // 1. Filter Kelas (Wajib)
        if ($kelas_id) {
            $query->whereHas('rombelJadwalPelajaran.rombelMataPelajaran', function ($q) use ($kelas_id) {
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

        $dataPresensi = $query->latest()->paginate(10);

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

        $rmQuery = \App\Models\RombelMataPelajaran::with('mataPelajaran')
            ->where('id_kelas', $request->kelas_id)
            ->when($tahunAjar, fn($q) => $q->where('id_tahun_ajar', $tahunAjar->id));
        if ($request->mapel_id) {
            $rmQuery->where('id_mata_pelajaran', $request->mapel_id);
        }
        $rombelMapelList = $rmQuery->get();

        // Build array of dates properties (for header and weekend formatting)
        $datesInfo = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $datesInfo[$d] = [
                'day' => $d,
                'isWeekend' => Carbon::createFromDate($tahun, $bulan, $d)->isWeekend()
            ];
        }

        $dataPerMapel = [];
        foreach ($rombelMapelList as $rm) {
            $presensiList = Presensi::whereNotNull('id_siswa')
                ->whereHas('rombelJadwalPelajaran', fn($q) => $q->where('id_rombel_mata_pelajaran', $rm->id))
                ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

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
                'nama_mapel'    => $rm->mataPelajaran->nama ?? 'Mapel',
                'matrix'        => $matrix,
            ];
        }

        $pdf = Pdf::loadView('admin.presensi.pdf', compact('kelas', 'siswaList', 'dataPerMapel', 'datesInfo', 'bulanLabel', 'mapelInfo'))
                  ->setPaper('a4', 'landscape');
        return $pdf->download("Absensi_{$kelas->nama}_{$bulanStr}.pdf");
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