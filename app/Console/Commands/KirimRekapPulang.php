<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Presensi;
use App\Models\RombelJadwalPelajaran;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class KirimRekapPulang extends Command
{
    protected $signature = 'absensi:rekap-pulang';
    protected $description = 'Kirim rekap kepulangan sore (sudah pulang / belum scan pulang) ke Grup WhatsApp Kelas pada sore hari jam 17:30';

    public function handle()
    {
        Carbon::setLocale('id');
        
        // --- MODE TESTING (Ubah jadi false jika ingin kirim real-time) ---
        $isTestMode = false;

        if ($isTestMode) {
            $now = Carbon::create(2026, 5, 4, 17, 30, 0); 
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
            $this->info("Hari ini libur ({$namaLibur}). Pengiriman rekap pulang dibatalkan.");
            return 0;
        }

        $tanggalFormat = Carbon::parse($tanggalIni)->isoFormat('DD MMMM YYYY');
        $this->info("Memulai pengiriman rekap pulang untuk tanggal $tanggalFormat...");

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

            $sudahPulang = [];
            $belumPulang = [];

            foreach ($rombelSiswaList as $rs) {
                $siswa = $rs->siswa;
                if (!$siswa || $siswa->status !== 'Aktif') continue;

                if ($kegiatanHariIni) {
                    // Jika hari kegiatan serentak, cek scan pulang kegiatan
                    $absen = Presensi::where('id_siswa', $siswa->id)
                        ->where('id_kegiatan_sekolah', $kegiatanHariIni->id)
                        ->where('tipe_scan_kegiatan', 'pulang')
                        ->where('tanggal', $tanggalIni)
                        ->first();

                    if ($absen) {
                        $jamScan = substr($absen->jam_scan, 0, 5);
                        $sudahPulang[] = [
                            'nama' => $siswa->nama,
                            'info' => "pulang pukul {$jamScan} WIB"
                        ];
                    } else {
                        $belumPulang[] = [
                            'nama' => $siswa->nama
                        ];
                    }
                } else {
                    // Hari reguler: cek scan pulang
                    $absenPulang = Presensi::where('id_siswa', $siswa->id)
                        ->where('tanggal', $tanggalIni)
                        ->where('tipe_scan', 'pulang')
                        ->first();

                    if ($absenPulang) {
                        $jamScan = substr($absenPulang->jam_scan, 0, 5);
                        $sudahPulang[] = [
                            'nama' => $siswa->nama,
                            'info' => "pulang pukul {$jamScan} WIB"
                        ];
                    } else {
                        $belumPulang[] = [
                            'nama' => $siswa->nama
                        ];
                    }
                }
            }

            // Kirim ke grup WA kelas jika ada target
            if (!empty($kelas->id_grup_wa)) {
                $pesanGrup = "🔊 *LAPORAN PULANG SEKOLAH KELAS {$kelas->nama}*\n";
                $pesanGrup .= "----------------------------------\n";
                $pesanGrup .= "Kelas: *{$kelas->nama}*\n";
                $pesanGrup .= "Tanggal: {$tanggalFormat}\n";
                $pesanGrup .= "Waktu Laporan: 17:30 WIB\n";
                $pesanGrup .= "----------------------------------\n\n";

                $pesanGrup .= "✅ *SUDAH SCAN PULANG:*\n";
                if (count($sudahPulang) > 0) {
                    $no = 1;
                    foreach ($sudahPulang as $sp) {
                        $pesanGrup .= "{$no}. *{$sp['nama']}* ({$sp['info']})\n";
                        $no++;
                    }
                } else {
                    $pesanGrup .= "_Belum ada siswa yang scan pulang._\n";
                }

                $pesanGrup .= "\n❌ *BELUM SCAN PULANG:*\n";
                if (count($belumPulang) > 0) {
                    $no = 1;
                    foreach ($belumPulang as $bp) {
                        $pesanGrup .= "{$no}. *{$bp['nama']}*\n";
                        $no++;
                    }
                } else {
                    $pesanGrup .= "_Semua siswa sudah scan pulang._\n";
                }

                $pesanGrup .= "\n----------------------------------\n";
                $pesanGrup .= "Laporan pulang sekolah disampaikan otomatis. Terima kasih.";

                WhatsAppService::send($kelas->id_grup_wa, $pesanGrup);
                $totalTerkirim++;

                // Jeda anti-spam
                sleep(2);
            }
        }

        $this->info("Selesai! Berhasil mengirim $totalTerkirim rekap pulang via WhatsApp.");
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
