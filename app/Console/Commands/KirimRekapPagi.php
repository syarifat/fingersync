<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Presensi;
use App\Models\RombelJadwalPelajaran;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class KirimRekapPagi extends Command
{
    protected $signature = 'absensi:rekap-pagi';
    protected $description = 'Kirim rekap kehadiran pagi (sudah hadir & belum hadir) ke Grup WhatsApp Kelas pada pagi hari jam 08:00';

    public function handle()
    {
        Carbon::setLocale('id');
        
        // --- MODE TESTING (Ubah jadi false jika ingin kirim real-time) ---
        $isTestMode = false;

        if ($isTestMode) {
            $now = Carbon::create(2026, 5, 4, 8, 0, 0); 
            $hariIni = 'Senin';
            $tanggalIni = '2026-05-04';
        } else {
            $now = Carbon::now('Asia/Jakarta');
            $hariIni = $this->getHariIndo($now->format('l'));
            $tanggalIni = $now->format('Y-m-d');
        }

        // Cek Hari Libur
        if (\App\Models\HariLibur::isHoliday($tanggalIni)) {
            $namaLibur = \App\Models\HariLibur::getHolidayName($tanggalIni);
            $this->info("Hari ini libur ({$namaLibur}). Pengiriman rekap pagi dibatalkan.");
            return 0;
        }

        $tanggalFormat = Carbon::parse($tanggalIni)->isoFormat('DD MMMM YYYY');
        $this->info("Memulai pengiriman rekap pagi untuk tanggal $tanggalFormat...");

        $activeYear = \App\Models\TahunAjar::where('status_aktif', true)->value('id');
        if (!$activeYear) {
            $this->error("Tidak ada tahun ajar aktif. Proses dibatalkan.");
            return 1;
        }

        // Ambil semua kelas
        $kelasList = Kelas::all();
        $totalTerkirim = 0;

        foreach ($kelasList as $kelas) {
            // Cek apakah ada kegiatan serentak hari ini
            $kegiatanHariIni = \App\Models\KegiatanSekolah::whereDate('tanggal', $tanggalIni)
                ->where('tipe', 'serentak')
                ->first();

            // Cari jadwal pelajaran reguler untuk kelas ini hari ini
            $jadwalHariIni = RombelJadwalPelajaran::whereHas('rombelMataPelajaran', function($q) use ($kelas, $activeYear) {
                    $q->where('id_kelas', $kelas->id)
                      ->where('id_tahun_ajar', $activeYear);
                })
                ->where('hari', $hariIni)
                ->get();

            // Jika tidak ada jadwal untuk kelas ini hari ini dan bukan hari kegiatan serentak, lewati
            if ($jadwalHariIni->isEmpty() && !$kegiatanHariIni) {
                continue;
            }

            // Ambil seluruh anggota siswa di kelas ini (diurutkan berdasarkan nama siswa ASC)
            $rombelSiswaList = \App\Models\RombelKelas::with('siswa')
                ->where('id_kelas', $kelas->id)
                ->where('id_tahun_ajar', $activeYear)
                ->get()
                ->sortBy(function ($rk) {
                    return strtolower($rk->siswa->nama ?? '');
                });

            if ($rombelSiswaList->isEmpty()) {
                continue;
            }

            $sudahHadir = [];
            $belumHadir = [];

            foreach ($rombelSiswaList as $rs) {
                $siswa = $rs->siswa;
                if (!$siswa || $siswa->status !== 'Aktif') continue;

                if ($kegiatanHariIni) {
                    // Jika hari kegiatan serentak, cek scan datang kegiatan
                    $absen = Presensi::where('id_siswa', $siswa->id)
                        ->where('id_kegiatan_sekolah', $kegiatanHariIni->id)
                        ->where('tipe_scan_kegiatan', 'datang')
                        ->where('tanggal', $tanggalIni)
                        ->first();

                    if ($absen) {
                        $jamScan = substr($absen->jam_scan, 0, 5);
                        $sudahHadir[] = [
                            'nama' => $siswa->nama,
                            'info' => "hadir pukul {$jamScan} WIB"
                        ];
                    } else {
                        // Cek apakah ada izin/sakit
                        $manualAbsen = Presensi::where('id_siswa', $siswa->id)
                            ->where('tanggal', $tanggalIni)
                            ->whereIn('status', ['Izin', 'Sakit'])
                            ->first();

                        $statusText = $manualAbsen ? " (" . strtolower($manualAbsen->status) . ")" : "";
                        $belumHadir[] = [
                            'nama' => $siswa->nama,
                            'info' => $statusText
                        ];
                    }
                } else {
                    // Hari reguler: cek scan datang untuk mapel apapun hari ini (status Hadir/Terlambat)
                    $absenDatang = Presensi::where('id_siswa', $siswa->id)
                        ->where('tanggal', $tanggalIni)
                        ->whereNotNull('id_rombel_jadwal_pelajaran')
                        ->whereIn('status', ['Hadir', 'Terlambat'])
                        ->orderBy('jam_scan', 'asc')
                        ->first();

                    if ($absenDatang) {
                        $jamScan = substr($absenDatang->jam_scan, 0, 5);
                        $statusText = strtolower($absenDatang->status);
                        $sudahHadir[] = [
                            'nama' => $siswa->nama,
                            'info' => "{$statusText} pukul {$jamScan} WIB"
                        ];
                    } else {
                        // Cek apakah ada keterangan Sakit/Izin hari ini di tabel presensi
                        $absenManual = Presensi::where('id_siswa', $siswa->id)
                            ->where('tanggal', $tanggalIni)
                            ->whereIn('status', ['Izin', 'Sakit'])
                            ->first();

                        $statusText = $absenManual ? " (" . strtolower($absenManual->status) . ")" : "";
                        $belumHadir[] = [
                            'nama' => $siswa->nama,
                            'info' => $statusText
                        ];
                    }
                }
            }

            // Kirim ke grup WA kelas jika ada target
            if (!empty($kelas->id_grup_wa)) {
                $pesanGrup = "⏰ *LAPORAN KEHADIRAN PAGI KELAS {$kelas->nama}*\n";
                $pesanGrup .= "----------------------------------\n";
                $pesanGrup .= "Kelas: *{$kelas->nama}*\n";
                $pesanGrup .= "Tanggal: {$tanggalFormat}\n";
                $pesanGrup .= "Waktu Laporan: 08:00 WIB\n";
                $pesanGrup .= "----------------------------------\n\n";

                $pesanGrup .= "✅ *SUDAH HADIR:*\n";
                if (count($sudahHadir) > 0) {
                    $no = 1;
                    foreach ($sudahHadir as $sh) {
                        $pesanGrup .= "{$no}. *{$sh['nama']}* ({$sh['info']})\n";
                        $no++;
                    }
                } else {
                    $pesanGrup .= "_Belum ada siswa yang hadir._\n";
                }

                $pesanGrup .= "\n❌ *BELUM HADIR:*\n";
                if (count($belumHadir) > 0) {
                    $no = 1;
                    foreach ($belumHadir as $bh) {
                        $pesanGrup .= "{$no}. *{$bh['nama']}*{$bh['info']}\n";
                        $no++;
                    }
                } else {
                    $pesanGrup .= "_Semua siswa sudah hadir._\n";
                }

                $pesanGrup .= "\n----------------------------------\n";
                $pesanGrup .= "Laporan kehadiran pagi disampaikan otomatis. Terima kasih.";

                WhatsAppService::send($kelas->id_grup_wa, $pesanGrup);
                $totalTerkirim++;

                // Jeda anti-spam
                sleep(2);
            }
        }

        $this->info("Selesai! Berhasil mengirim $totalTerkirim rekap pagi via WhatsApp.");
        return 0;
    }

    private function getHariIndo($day) {
        $days = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        return $days[$day] ?? 'Senin';
    }
}
