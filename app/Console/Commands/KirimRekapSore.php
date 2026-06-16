<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Presensi;
use App\Models\RombelJadwalPelajaran;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class KirimRekapSore extends Command
{
    // Nama perintah terminal
    protected $signature = 'absensi:rekap-sore';
    protected $description = 'Kirim rekap absensi harian ke Grup WhatsApp Kelas atau Orang Tua pada sore hari jam 16:00';

    public function handle()
    {
        Carbon::setLocale('id');
        
        // --- MODE TESTING (Ubah jadi false jika ingin kirim real-time) ---
        $isTestMode = false;

        if ($isTestMode) {
            $now = Carbon::create(2026, 5, 4, 16, 0, 0); 
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
            $this->info("Hari ini libur ({$namaLibur}). Pengiriman rekap sore dibatalkan.");
            return 0;
        }

        $tanggalFormat = Carbon::parse($tanggalIni)->isoFormat('DD MMMM YYYY');
        $this->info("Memulai pengisian Alpha otomatis dan pengiriman rekap sore untuk tanggal $tanggalFormat...");

        // CARI DEVICE DEFAULT UNTUK ABSENSI OTOMATIS
        $device = \App\Models\Device::first();
        $deviceId = $device ? $device->id : null; 

        $activeYear = \App\Models\TahunAjar::where('status_aktif', true)->value('id');
        if (!$activeYear) {
            $this->error("Tidak ada tahun ajar aktif. Proses dibatalkan.");
            return 1;
        }

        // Ambil semua kelas
        $kelasList = Kelas::all();

        $totalTerkirim = 0;
        $totalAlphaDitambahkan = 0;

        foreach ($kelasList as $kelas) {
            // 1. Cari jadwal pelajaran KHUSUS untuk kelas ini
            $jadwalHariIni = RombelJadwalPelajaran::with(['rombelMataPelajaran.mataPelajaran'])
                ->whereHas('rombelMataPelajaran', function($q) use ($kelas, $activeYear) {
                    $q->where('id_kelas', $kelas->id)
                      ->where('id_tahun_ajar', $activeYear);
                })
                ->where('hari', $hariIni)
                ->orderBy('jam_mulai', 'asc')
                ->get();

            // Jika tidak ada jadwal untuk kelas ini hari ini, lewati
            if ($jadwalHariIni->isEmpty()) {
                continue;
            }

            // 2. Ambil seluruh anggota siswa di kelas ini
            $rombelSiswaList = \App\Models\RombelKelas::with('siswa')
                ->where('id_kelas', $kelas->id)
                ->where('id_tahun_ajar', $activeYear)
                ->get();

            if ($rombelSiswaList->isEmpty()) {
                continue;
            }

            // 3. Proses absensi Alpha otomatis terlebih dahulu untuk database
            foreach ($rombelSiswaList as $rs) {
                $siswa = $rs->siswa;
                if (!$siswa || $siswa->status !== 'Aktif') continue;

                foreach ($jadwalHariIni as $jdwl) {
                    $absen = Presensi::where('id_siswa', $siswa->id)
                        ->where('id_rombel_jadwal_pelajaran', $jdwl->id)
                        ->where('tanggal', $tanggalIni)
                        ->first();

                    if (!$absen) {
                        Presensi::create([
                            'id_siswa' => $siswa->id,
                            'id_rombel_jadwal_pelajaran' => $jdwl->id,
                            'tanggal' => $tanggalIni,
                            'jam_scan' => '16:00:00', // Jam 4 sore
                            'id_device' => $deviceId,
                            'status' => 'Alpha',
                            'id_tahun_ajar' => $activeYear,
                        ]);
                        $totalAlphaDitambahkan++;
                    }
                }
            }

            // 4. KELOMPOK PENGIRIMAN WA
            if (!empty($kelas->id_grup_wa)) {
                // A. JIKA KELAS MEMILIKI GRUP WA (Kirim Terkelompok)
                
                // Bagi siswa ke dalam chunk (maksimal 10 siswa per pesan agar tidak kepanjangan di WA)
                $siswaArray = $rombelSiswaList->values()->toArray();
                $chunks = array_chunk($siswaArray, 10);
                
                foreach ($chunks as $chunkIndex => $studentChunk) {
                    // Cek limit pengiriman jika dikonfigurasi di .env
                    $limit = env('WA_LIMIT_PER_RUN', 0);
                    if ($limit > 0 && $totalTerkirim >= $limit) {
                        $this->info("Batas pengiriman WA harian tercapai ({$limit}). Sisa pesan grup dibatalkan.");
                        break;
                    }

                    $pesanGrup = "📝 *REKAP PRESENSI HARIAN KELAS {$kelas->nama}*\n";
                    $pesanGrup .= "*(Bagian " . ($chunkIndex + 1) . "/" . count($chunks) . ")*\n";
                    $pesanGrup .= "----------------------------------\n";
                    $pesanGrup .= "Tanggal: {$tanggalFormat}\n";
                    $pesanGrup .= "----------------------------------\n\n";

                    $hasContent = false;
                    foreach ($studentChunk as $studentIndexGlobal => $rsData) {
                        $siswaId = $rsData['id_siswa'];
                        $siswaNama = $rsData['siswa']['nama'];
                        $siswaStatus = $rsData['siswa']['status'];
                        if ($siswaStatus !== 'Aktif') continue;

                        $noUrut = ($chunkIndex * 10) + $studentIndexGlobal + 1;
                        $pesanGrup .= "{$noUrut}. *{$siswaNama}*\n";
                        
                        foreach ($jadwalHariIni as $jdwl) {
                            $mapel = $jdwl->rombelMataPelajaran->mataPelajaran->nama;
                            $jam = substr($jdwl->jam_mulai, 0, 5);

                            $absen = Presensi::where('id_siswa', $siswaId)
                                ->where('id_rombel_jadwal_pelajaran', $jdwl->id)
                                ->where('tanggal', $tanggalIni)
                                ->first();

                            $status = $absen ? $absen->status : 'Alpha';
                            
                            $icon = '❌';
                            if ($status == 'Hadir' || $status == 'Terlambat') $icon = '✅';
                            if ($status == 'Sakit') $icon = '🤒';
                            if ($status == 'Izin') $icon = '✉️';
                            
                            $displayStatus = ($status == 'Alpha') ? 'Alpha' : $status;
                            $pesanGrup .= "   {$icon} *{$jam}* | {$mapel} (_{$displayStatus}_)\n";
                        }

                        // Tampilkan status scan pulang
                        $scanPulang = Presensi::where('id_siswa', $siswaId)
                            ->where('tanggal', $tanggalIni)
                            ->where('tipe_scan', 'pulang')
                            ->first();

                        if ($scanPulang) {
                            $jamPulang = substr($scanPulang->jam_scan, 0, 5);
                            $pesanGrup .= "   🚪 *Scan Pulang:* {$jamPulang} WIB (Sudah Pulang)\n";
                        } else {
                            $pesanGrup .= "   🚪 *Scan Pulang:* - (Belum Scan Pulang / Bolos)\n";
                        }

                        $pesanGrup .= "\n";
                        $hasContent = true;
                    }

                    $pesanGrup .= "----------------------------------\n";
                    $pesanGrup .= "Demikian laporan harian kelas ini disampaikan. Terima kasih.";

                    if ($hasContent) {
                        WhatsAppService::send($kelas->id_grup_wa, $pesanGrup);
                        $totalTerkirim++;
                        
                        // Jeda anti-spam
                        sleep(2);
                    }
                }
            } else {
                // B. FALLBACK: JIKA TIDAK ADA GRUP WA (Kirim individu ke wali murid)
                foreach ($rombelSiswaList as $rs) {
                    $siswa = $rs->siswa;
                    if (!$siswa || $siswa->status !== 'Aktif' || empty($siswa->nohp_ortu)) {
                        continue;
                    }

                    // Cek limit pengiriman jika dikonfigurasi di .env
                    $limit = env('WA_LIMIT_PER_RUN', 0);
                    if ($limit > 0 && $totalTerkirim >= $limit) {
                        $this->info("Batas pengiriman WA harian tercapai ({$limit}). Sisa pesan untuk siswa berikutnya dibatalkan kirim WA.");
                        break; 
                    }

                    $pesan = "📝 *LAPORAN PRESENSI HARIAN*\n";
                    $pesan .= "----------------------------------\n";
                    $pesan .= "Nama: *{$siswa->nama}*\n";
                    $pesan .= "Tanggal: {$tanggalFormat}\n";
                    $pesan .= "----------------------------------\n\n";
                    $pesan .= "Berikut detail kehadiran ananda hari ini:\n\n";

                    foreach ($jadwalHariIni as $jdwl) {
                        $mapel = $jdwl->rombelMataPelajaran->mataPelajaran->nama;
                        $jam = substr($jdwl->jam_mulai, 0, 5);

                        $absen = Presensi::where('id_siswa', $siswa->id)
                            ->where('id_rombel_jadwal_pelajaran', $jdwl->id)
                            ->where('tanggal', $tanggalIni)
                            ->first();

                        $status = $absen ? $absen->status : 'Alpha';
                        
                        $icon = '❌';
                        if ($status == 'Hadir' || $status == 'Terlambat') $icon = '✅';
                        if ($status == 'Sakit') $icon = '🤒';
                        if ($status == 'Izin') $icon = '✉️';

                        $displayStatus = ($status == 'Alpha') ? 'Alpha / Tanpa Keterangan' : $status;

                        $pesan .= "{$icon} *{$jam}* | {$mapel}\n";
                        $pesan .= "Status: _{$displayStatus}_\n\n";
                    }

                    // Tampilkan status scan pulang
                    $scanPulang = Presensi::where('id_siswa', $siswa->id)
                        ->where('tanggal', $tanggalIni)
                        ->where('tipe_scan', 'pulang')
                        ->first();

                    if ($scanPulang) {
                        $jamPulang = substr($scanPulang->jam_scan, 0, 5);
                        $pesan .= "🚪 *Scan Pulang:* {$jamPulang} WIB (Sudah Pulang)\n\n";
                    } else {
                        $pesan .= "🚪 *Scan Pulang:* - (Belum Scan Pulang / Bolos)\n\n";
                    }

                    $pesan .= "----------------------------------\n";
                    $pesan .= "Demikian laporan harian ini kami sampaikan. Terima kasih atas perhatian Ayah/Ibu.";

                    WhatsAppService::send($siswa->nohp_ortu, $pesan, $siswa->id);
                    $totalTerkirim++;

                    // Beri jeda 2 detik per pengiriman agar nomor WA tidak terkena ban spam
                    sleep(2);
                }
            }
        }

        $this->info("Selesai! $totalAlphaDitambahkan data Alpha otomatis ditambahkan ke database.");
        $this->info("Berhasil mengirim $totalTerkirim rekap sore via WhatsApp.");
    }

    private function getHariIndo($day) {
        $days = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        return $days[$day] ?? 'Senin';
    }
}