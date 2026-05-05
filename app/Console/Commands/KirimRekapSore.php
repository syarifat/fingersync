<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Presensi;
use App\Models\RombelJadwalPelajaran;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class KirimRekapSore extends Command
{
    // Nama perintah terminal
    protected $signature = 'absensi:rekap-sore';
    protected $description = 'Kirim rekap absensi harian ke Orang Tua pada sore hari jam 16:00';

    public function handle()
    {
        Carbon::setLocale('id');
        
        // --- MODE TESTING (Ubah jadi false jika ingin kirim real-time) ---
        $isTestMode = false;

        if ($isTestMode) {
            $now = Carbon::create(2026, 4, 11, 16, 0, 0); 
            $hariIni = 'Sabtu';
            $tanggalIni = '2026-04-11';
        } else {
            $now = Carbon::now('Asia/Jakarta');
            $hariIni = $this->getHariIndo($now->format('l'));
            $tanggalIni = $now->format('Y-m-d');
        }

        $tanggalFormat = Carbon::parse($tanggalIni)->isoFormat('DD MMMM YYYY');
        $this->info("Memulai pengiriman rekap sore untuk tanggal $tanggalFormat...");

        // 1. Ambil semua siswa yang punya nomor HP Ortu
        $siswaList = Siswa::whereNotNull('nohp_ortu')->where('status', 'Aktif')->get();

        $totalTerkirim = 0;

        foreach ($siswaList as $siswa) {
            // --- PERBAIKAN: Cari siswa ini ada di KELAS mana ---
            $rombelSiswa = \App\Models\RombelKelas::where('id_siswa', $siswa->id)
                            ->latest() // Ambil riwayat kelas yang paling baru
                            ->first();
                            
            if (!$rombelSiswa) continue; // Jika anak ini belum punya kelas, lewati

            // 2. Cari jadwal pelajaran KHUSUS untuk kelas tersebut
            $jadwalHariIni = RombelJadwalPelajaran::with(['rombelMataPelajaran.mataPelajaran'])
                ->whereHas('rombelMataPelajaran', function($q) use ($rombelSiswa) {
                    $q->where('id_kelas', $rombelSiswa->id_kelas);
                })
                ->where('hari', $hariIni)
                ->orderBy('jam_mulai', 'asc')
                ->get();

            // Jika tidak ada jadwal, lewati
            if ($jadwalHariIni->isEmpty()) continue;

            // 3. Susun Pesan WA
            $pesan = "📝 *LAPORAN PRESENSI HARIAN*\n";
            $pesan .= "----------------------------------\n";
            $pesan .= "Nama: *{$siswa->nama}*\n";
            $pesan .= "Tanggal: {$tanggalFormat}\n";
            $pesan .= "----------------------------------\n\n";
            $pesan .= "Berikut detail kehadiran ananda hari ini:\n\n";

            foreach ($jadwalHariIni as $jdwl) {
                $mapel = $jdwl->rombelMataPelajaran->mataPelajaran->nama;
                $jam = substr($jdwl->jam_mulai, 0, 5);

                // Cek apakah ada data presensinya
                $absen = Presensi::where('id_siswa', $siswa->id)
                    ->where('id_rombel_jadwal_pelajaran', $jdwl->id)
                    ->where('tanggal', $tanggalIni)
                    ->first();

                $status = $absen ? $absen->status : 'Alpha / Tidak Ada Keterangan';
                
                // Icon pemanis
                $icon = '❌';
                if ($status == 'Hadir' || $status == 'Terlambat') $icon = '✅';
                if ($status == 'Sakit') $icon = '🤒';
                if ($status == 'Izin') $icon = '✉️';

                $pesan .= "{$icon} *{$jam}* | {$mapel}\n";
                $pesan .= "Status: _{$status}_\n\n";
            }

            $pesan .= "----------------------------------\n";
            $pesan .= "Demikian laporan harian ini kami sampaikan. Terima kasih atas perhatian Ayah/Ibu.";

            // 4. KIRIM WA!
            \App\Services\WhatsAppService::send($siswa->nohp_ortu, $pesan);
            $totalTerkirim++;
        }

        $this->info("Selesai! Berhasil mengirim $totalTerkirim rekap sore ke Orang Tua.");
    }

    private function getHariIndo($day) {
        $days = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        return $days[$day] ?? 'Senin';
    }
}