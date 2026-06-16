<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\RombelKelas;
use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class WaliKelasController extends Controller
{
    public function index(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        // 1. Cari kelas di mana guru ini menjadi Wali Kelas
        $infoKelas = RombelKelas::with('kelas')
            ->where('id_guru_wali_kelas', $guru->id)
            ->first();

        if (!$infoKelas) {
            abort(403, 'Akses Ditolak. Anda tidak terdaftar sebagai Wali Kelas.');
        }

        // 2. Ambil semua siswa di kelas tersebut
        $siswaKelas = RombelKelas::with('siswa')
            ->where('id_kelas', $infoKelas->id_kelas)
            ->get();

        // 3. Filter Bulan (Default: Bulan Ini)
        $bulanFilter = $request->bulan ?? Carbon::now()->format('Y-m');

        // 4. Hitung Rekap Absensi per Siswa untuk bulan tersebut
        foreach ($siswaKelas as $rs) {
            $rs->total_hadir = Presensi::where('id_siswa', $rs->id_siswa)
                                ->where('tanggal', 'like', $bulanFilter . '%')
                                ->where('status', 'Hadir')->count();
            
            $rs->total_sakit = Presensi::where('id_siswa', $rs->id_siswa)
                                ->where('tanggal', 'like', $bulanFilter . '%')
                                ->where('status', 'Sakit')->count();

            $rs->total_izin = Presensi::where('id_siswa', $rs->id_siswa)
                                ->where('tanggal', 'like', $bulanFilter . '%')
                                ->where('status', 'Izin')->count();

            $rs->total_alpha = Presensi::where('id_siswa', $rs->id_siswa)
                                ->where('tanggal', 'like', $bulanFilter . '%')
                                ->where('status', 'Alpha')->count();
        }

        return view('guru.walikelas.index', compact('infoKelas', 'siswaKelas', 'bulanFilter'));
    }

    public function exportPdf(Request $request)
    {
        $guru = Guru::where('user_id', Auth::id())->firstOrFail();

        // 1. Cari kelas di mana guru ini menjadi Wali Kelas
        $infoKelas = RombelKelas::with('kelas')
            ->where('id_guru_wali_kelas', $guru->id)
            ->first();

        if (!$infoKelas) {
            abort(403, 'Akses Ditolak. Anda tidak terdaftar sebagai Wali Kelas.');
        }

        $kelas = $infoKelas->kelas;
        $bulanFilter = $request->bulan ?? Carbon::now()->format('Y-m');
        
        \Carbon\Carbon::setLocale('id');
        $tahun    = (int) date('Y', strtotime($bulanFilter . '-01'));
        $bulan    = (int) date('m', strtotime($bulanFilter . '-01'));
        $bulanLabel = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->isoFormat('MMMM YYYY');
        $daysInMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        $tahunAjar = \App\Models\TahunAjar::where('status_aktif', 1)->first();

        $siswaList = \App\Models\RombelKelas::with('siswa')
            ->where('id_kelas', $infoKelas->id_kelas)
            ->when($tahunAjar, fn($q) => $q->where('id_tahun_ajar', $tahunAjar->id))
            ->get()->pluck('siswa')->filter()->sortBy('nama')->values();

        // Cari mata pelajaran unik yang diajarkan di kelas ini
        $mapelList = \App\Models\MataPelajaran::whereHas('rombelMataPelajaran', function($q) use ($infoKelas, $tahunAjar) {
            $q->where('id_kelas', $infoKelas->id_kelas);
            if ($tahunAjar) {
                $q->where('id_tahun_ajar', $tahunAjar->id);
            }
        })->get();

        // Build array of dates properties
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
                ->whereHas('rombelJadwalPelajaran.rombelMataPelajaran', function($q) use ($mapel, $infoKelas) {
                    $q->where('id_mata_pelajaran', $mapel->id)
                      ->where('id_kelas', $infoKelas->id_kelas);
                })
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->get();

            // Jika benar-benar kosong presensi mapel ini, boleh dilewati agar pdf tidak memunculkan tabel kosong melompong
            if ($presensiList->isEmpty()) continue; 

            // Hitung jadwal khusus hari mapel ini
            $hariJadwal = \App\Models\RombelJadwalPelajaran::whereHas('rombelMataPelajaran', function($q) use ($mapel, $infoKelas, $tahunAjar) {
                $q->where('id_mata_pelajaran', $mapel->id)
                  ->where('id_kelas', $infoKelas->id_kelas);
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
                        if (in_array('Alpha', $statuses) || in_array('Alpa', $statuses)) $row[$d] = 'A';
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

        // Tambahkan virtual mapel untuk "Kegiatan Sekolah (Serentak)" jika ada kegiatan
        $kegiatanSekolahList = \App\Models\KegiatanSekolah::where('tipe', 'serentak')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        if ($kegiatanSekolahList->isNotEmpty()) {
            $presensiKegiatan = Presensi::whereNotNull('id_siswa')
                ->whereNotNull('id_kegiatan_sekolah')
                ->whereHas('siswa.rombelKelas', function($q) use ($infoKelas) {
                    $q->where('id_kelas', $infoKelas->id_kelas);
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

        $kelasSafe = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $kelas->nama);
        $fileName = "Data Rekap Presensi_{$bulanLabel}_{$kelasSafe}.pdf";

        $pdf = Pdf::loadView('admin.presensi.pdf', compact('kelas', 'siswaList', 'dataPerMapel', 'bulanLabel'))
                  ->setPaper('a4', 'landscape');
                  
        return $pdf->download($fileName);
    }
}