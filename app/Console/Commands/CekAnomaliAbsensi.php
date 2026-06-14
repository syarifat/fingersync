<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RombelJadwalPelajaran;
use App\Models\RombelKelas;
use App\Models\Presensi;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class CekAnomaliAbsensi extends Command
{
    // Nama perintah untuk dijalankan di terminal
    protected $signature = 'absensi:cek-anomali';
    protected $description = 'Mendeteksi siswa yang belum hadir 1 jam setelah mapel dimulai dan lapor ke Wali/BK';

    public function handle()
    {
        Carbon::setLocale('id');
        
        // --- MODE TESTING (Ubah jadi false jika di production) ---
        $isTestMode = false;

        if ($isTestMode) {
            // Kita setting seolah-olah mesin waktu berada di hari SABTU, jam 18:15 WIB.
            // Maka sistem akan mencari kelas yang mulainya antara jam 16:45 sampai 17:15.
            // Jadwal Anda yang jam 17:00 PASTI akan tertangkap!
            $now = Carbon::create(2026, 4, 11, 18, 15, 0); 
            $hariIni = 'Sabtu';
            $tanggalIni = '2026-04-11';
        } else {
            $now = Carbon::now('Asia/Jakarta');
            $hariIni = $this->getHariIndo($now->format('l'));
            $tanggalIni = $now->format('Y-m-d');
        }

        // Cek Hari Libur
        if (\App\Models\HariLibur::isHoliday($tanggalIni)) {
            $namaLibur = \App\Models\HariLibur::getHolidayName($tanggalIni);
            $this->info("Hari ini libur ({$namaLibur}). Razia anomali dibatalkan.");
            return 0;
        }

        // Trik Anti-Spam: Cari mapel yang mulainya antara 90 menit sampai 60 menit yang lalu.
        // Jika Command ini jalan tiap 30 menit, tidak akan ada kelas yang kena razia dua kali.
        $batasBawah = (clone $now)->subMinutes(90)->format('H:i:s');
        $batasAtas  = (clone $now)->subMinutes(60)->format('H:i:s');
        
        $this->info("Menjalankan razia anomali untuk jadwal antara {$batasBawah} - {$batasAtas} WIB...");

        $jadwalBerjalan = RombelJadwalPelajaran::with(['rombelMapel.kelas', 'rombelMapel.mataPelajaran'])
            ->where('hari', $hariIni)
            ->whereTime('jam_mulai', '>=', $batasBawah)
            ->whereTime('jam_mulai', '<=', $batasAtas)
            ->get();

        $jumlahLaporan = 0;

        foreach ($jadwalBerjalan as $jadwal) {
            $id_kelas = $jadwal->rombelMapel->id_kelas;
            $nama_kelas = $jadwal->rombelMapel->kelas->nama;
            $nama_mapel = $jadwal->rombelMapel->mataPelajaran->nama;

            // Ambil data siswa & guru pengampu di kelas tersebut
            $infoKelas = RombelKelas::with(['waliKelas', 'guruBk', 'siswa'])
                ->where('id_kelas', $id_kelas)
                ->where('id_tahun_ajar', $jadwal->rombelMapel->id_tahun_ajar)
                ->get();

            if ($infoKelas->isEmpty()) continue;

            $siswaAnomali = [];

            // Razia satu per satu siswa
            foreach ($infoKelas as $rk) {
                // Apakah anak ini absen di mapel ini? (Hadir/Sakit/Izin dianggap aman)
                $sudahAbsen = Presensi::where('id_siswa', $rk->id_siswa)
                    ->where('id_rombel_jadwal_pelajaran', $jadwal->id)
                    ->where('tanggal', $tanggalIni)
                    ->whereIn('status', ['Hadir', 'Sakit', 'Izin', 'Terlambat'])
                    ->exists();

                if (!$sudahAbsen) {
                    $siswaAnomali[] = "➖ " . $rk->siswa->nama; // Masukkan ke daftar bolos
                }
            }

            // JIKA ADA YANG BOLOS, RAPEL JADI 1 PESAN
            if (count($siswaAnomali) > 0) {
                $daftarSiswa = implode("\n", $siswaAnomali);
                
                $pesan = "⚠️ *Peringatan Anomali Kehadiran*\n\n";
                $pesan .= "Kelas: *{$nama_kelas}*\n";
                $pesan .= "Mapel: *{$nama_mapel}*\n";
                $pesan .= "Kondisi: Mapel sudah berjalan > 1 Jam, namun siswa berikut belum melakukan presensi sama sekali:\n\n";
                $pesan .= $daftarSiswa . "\n\n";
                $pesan .= "Mohon tindak lanjut dari Wali Kelas / Guru BK.";

                // Ambil No WA Wali Kelas dan BK (Pastikan kolom 'nohp' ada di tabel 'guru')
                $noWali = $infoKelas->first()->waliKelas->nohp ?? null;
                $noBk = $infoKelas->first()->guruBk->nohp ?? null;

                if ($noWali) {
                    WhatsAppService::send($noWali, $pesan);
                    sleep(2); // Jeda anti-spam
                }
                if ($noBk && $noBk != $noWali) {
                    WhatsAppService::send($noBk, $pesan);
                    sleep(2); // Jeda anti-spam
                }

                $jumlahLaporan++;
            }
        }

        $this->info("Razia selesai. {$jumlahLaporan} laporan kelas dikirim.");
    }

    // Helper Translate Hari
    private function getHariIndo($day) {
        $days = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        return $days[$day] ?? 'Senin';
    }
}