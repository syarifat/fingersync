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
    protected $description = 'Kirim rekap absensi harian terpadu (KBM & Kepulangan) ke Grup WhatsApp Kelas pada sore hari jam 17:30';

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
            $this->info("Hari ini libur ({$namaLibur}). Pengiriman rekap sore dibatalkan.");
            return 0;
        }

        $tanggalFormat = Carbon::parse($tanggalIni)->isoFormat('DD MMMM YYYY');
        $this->info("Memulai pengisian Alpha otomatis dan pengiriman rekap terpadu untuk tanggal $tanggalFormat...");

        // CARI DEVICE DEFAULT UNTUK ABSENSI OTOMATIS
        $device = \App\Models\Device::first();
        $deviceId = $device ? $device->id : null; 

        $activeYear = \App\Models\TahunAjar::where('status_aktif', true)->value('id');
        if (!$activeYear) {
            $this->error("Tidak ada tahun ajar aktif. Proses dibatalkan.");
            return 1;
        }

        // Cek apakah ada kegiatan serentak hari ini
        $kegiatanHariIni = \App\Models\KegiatanSekolah::whereDate('tanggal', $tanggalIni)
            ->where('tipe', 'serentak')
            ->first();

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

            // Jika tidak ada jadwal untuk kelas ini hari ini dan bukan hari kegiatan serentak, lewati
            if ($jadwalHariIni->isEmpty() && !$kegiatanHariIni) {
                continue;
            }

            // 2. Ambil seluruh anggota siswa di kelas ini (diurutkan berdasarkan nama siswa ASC)
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

            // 3. Proses absensi Alpha otomatis terlebih dahulu untuk database (Hanya jika KBM Reguler / Bukan Kegiatan Serentak)
            if (!$kegiatanHariIni) {
                foreach ($rombelSiswaList as $rs) {
                    $siswa = $rs->siswa;
                    if (!$siswa || $siswa->status !== 'Aktif') continue;

                    foreach ($jadwalHariIni as $jdwl) {
                        // Jika jadwal ini diliburkan karena guru izin, jangan buat Alpha
                        $kbmLibur = \App\Models\GuruKbmKhusus::where('id_rombel_jadwal_pelajaran', $jdwl->id)
                            ->whereDate('tanggal', $tanggalIni)
                            ->where('status', 'izin_libur')
                            ->exists();

                        if ($kbmLibur) {
                            continue;
                        }

                        $absen = Presensi::where('id_siswa', $siswa->id)
                            ->where('id_rombel_jadwal_pelajaran', $jdwl->id)
                            ->where('tanggal', $tanggalIni)
                            ->first();

                        if (!$absen) {
                            Presensi::create([
                                'id_siswa' => $siswa->id,
                                'id_rombel_jadwal_pelajaran' => $jdwl->id,
                                'tanggal' => $tanggalIni,
                                'jam_scan' => '17:30:00', // Jam 17:30 sore
                                'id_device' => $deviceId,
                                'status' => 'Alpha',
                                'id_tahun_ajar' => $activeYear,
                            ]);
                            $totalAlphaDitambahkan++;
                        }
                    }
                }
            }

            // 4. KELOMPOK PENGIRIMAN WA (Hanya ke grup kelas, tidak ke nomor pribadi)
            if (!empty($kelas->id_grup_wa)) {
                // Cek limit pengiriman jika dikonfigurasi di .env
                $limit = env('WA_LIMIT_PER_RUN', 0);
                if ($limit > 0 && $totalTerkirim >= $limit) {
                    $this->info("Batas pengiriman WA harian tercapai ({$limit}). Sisa pesan grup dibatalkan.");
                    continue;
                }

                $pesanGrup = "📝 *LAPORAN HARIAN & KEPULANGAN KELAS {$kelas->nama}*\n";
                $pesanGrup .= "----------------------------------\n";
                $pesanGrup .= "Kelas: *{$kelas->nama}*\n";
                $pesanGrup .= "Tanggal: {$tanggalFormat}\n";
                $pesanGrup .= "Waktu Laporan: 17:30 WIB\n";
                $pesanGrup .= "----------------------------------\n\n";

                $pesanGrup .= "👥 *RIWAYAT ABSENSI KBM:*\n";
                $pesanGrup .= "_(Jika nama anak tidak muncul di bawah ini, berarti anak hadir di semua mata pelajaran hari ini)_\n\n";

                $sudahPulang = [];
                $belumPulang = [];
                $hasContent = false;
                $noUrut = 1;

                foreach ($rombelSiswaList as $rsData) {
                    $siswa = $rsData->siswa;
                    if (!$siswa || $siswa->status !== 'Aktif') continue;

                    $siswaId = $siswa->id;
                    $siswaNama = $siswa->nama;

                    $isFullyPresent = true;
                    $studentKbmText = "";
                    $statusesToday = [];
                    
                    if ($kegiatanHariIni) {
                        $absenDatang = Presensi::where('id_siswa', $siswaId)
                            ->where('id_kegiatan_sekolah', $kegiatanHariIni->id)
                            ->where('tipe_scan_kegiatan', 'datang')
                            ->where('tanggal', $tanggalIni)
                            ->first();
                        if ($absenDatang) {
                            $jamDatang = substr($absenDatang->jam_scan, 0, 5);
                            $statusDatangText = "Hadir pukul {$jamDatang} WIB";
                            $statusesToday[] = $absenDatang->status;
                        } else {
                            $statusDatangText = "Tidak Hadir";
                            $isFullyPresent = false;
                            $statusesToday[] = 'Alpha';
                        }
                        
                        $studentKbmText .= "   - Hadir Kegiatan ({$statusDatangText})\n";

                        // Cek checkout kegiatan
                        $absenPulang = Presensi::where('id_siswa', $siswaId)
                            ->where('id_kegiatan_sekolah', $kegiatanHariIni->id)
                            ->where('tipe_scan_kegiatan', 'pulang')
                            ->where('tanggal', $tanggalIni)
                            ->first();

                        $uniqueStatuses = array_unique($statusesToday);
                        $infoPulangSuffix = "";
                        if (count($uniqueStatuses) === 1) {
                            $singleStatus = reset($uniqueStatuses);
                            if ($singleStatus === 'Sakit') {
                                $infoPulangSuffix = "Sakit";
                            } elseif ($singleStatus === 'Izin') {
                                $infoPulangSuffix = "Izin";
                            } elseif (in_array($singleStatus, ['Alpha', 'Alpa'])) {
                                $infoPulangSuffix = "Tidak Hadir";
                            }
                        }

                        if ($absenPulang) {
                            $jamPulang = substr($absenPulang->jam_scan, 0, 5);
                            $sudahPulang[] = [
                                'nama' => $siswaNama,
                                'info' => "Pulang pukul {$jamPulang} WIB"
                            ];
                        } else {
                            $belumPulang[] = [
                                'nama' => $siswaNama,
                                'suffix' => $infoPulangSuffix
                            ];
                        }
                    } else {
                        foreach ($jadwalHariIni as $jdwl) {
                            $mapel = $jdwl->rombelMataPelajaran->mataPelajaran->nama;

                            $kbmKhusus = \App\Models\GuruKbmKhusus::where('id_rombel_jadwal_pelajaran', $jdwl->id)
                                ->whereDate('tanggal', $tanggalIni)
                                ->first();

                            if ($kbmKhusus && $kbmKhusus->status === 'izin_libur') {
                                $statusText = "Libur (Guru Izin)";
                            } else {
                                $absen = Presensi::where('id_siswa', $siswaId)
                                    ->where('id_rombel_jadwal_pelajaran', $jdwl->id)
                                    ->where('tanggal', $tanggalIni)
                                    ->first();

                                $status = $absen ? $absen->status : 'Alpha';
                                $statusesToday[] = $status;
                                
                                if ($status === 'Hadir' || $status === 'Terlambat') {
                                    $jamScan = substr($absen->jam_scan, 0, 5);
                                    $statusText = "Hadir pukul {$jamScan} WIB";
                                } elseif ($status === 'Sakit') {
                                    $statusText = "Sakit";
                                    $isFullyPresent = false;
                                } elseif ($status === 'Izin') {
                                    $statusText = "Izin";
                                    $isFullyPresent = false;
                                } else {
                                    $statusText = "Tidak Hadir";
                                    $isFullyPresent = false;
                                }
                            }
                            
                            $studentKbmText .= "   - {$mapel} ({$statusText})\n";
                        }

                        // Cek checkout scan
                        $scanPulang = Presensi::where('id_siswa', $siswaId)
                            ->where('tanggal', $tanggalIni)
                            ->where('tipe_scan', 'pulang')
                            ->first();

                        $uniqueStatuses = array_unique($statusesToday);
                        $infoPulangSuffix = "";
                        if (count($uniqueStatuses) === 1) {
                            $singleStatus = reset($uniqueStatuses);
                            if ($singleStatus === 'Sakit') {
                                $infoPulangSuffix = "Sakit";
                            } elseif ($singleStatus === 'Izin') {
                                $infoPulangSuffix = "Izin";
                            } elseif (in_array($singleStatus, ['Alpha', 'Alpa'])) {
                                $infoPulangSuffix = "Tidak Hadir";
                            }
                        }

                        if ($scanPulang) {
                            $jamPulang = substr($scanPulang->jam_scan, 0, 5);
                            $sudahPulang[] = [
                                'nama' => $siswaNama,
                                'info' => "Pulang pukul {$jamPulang} WIB"
                            ];
                        } else {
                            $belumPulang[] = [
                                'nama' => $siswaNama,
                                'suffix' => $infoPulangSuffix
                            ];
                        }
                    }

                    if (!$isFullyPresent) {
                        $pesanGrup .= "{$noUrut}. *{$siswaNama}*\n" . $studentKbmText . "\n";
                        $noUrut++;
                        $hasContent = true;
                    }
                }

                if ($noUrut === 1) {
                    $pesanGrup .= "_Semua siswa hadir penuh hari ini._\n\n";
                    $hasContent = true;
                }

                // Append status kepulangan sekolah dengan info header tambahan
                $pesanGrup .= "----------------------------------\n";
                $pesanGrup .= "🚪 *STATUS KEPULANGAN SEKOLAH:*\n";
                $pesanGrup .= "Kelas: *{$kelas->nama}*\n";
                $pesanGrup .= "Tanggal: {$tanggalFormat}\n";
                $pesanGrup .= "Waktu Laporan: 17:30 WIB\n";
                $pesanGrup .= "----------------------------------\n\n";

                $pesanGrup .= "✅ *SUDAH SCAN PULANG:*\n";
                if (count($sudahPulang) > 0) {
                    $noSP = 1;
                    foreach ($sudahPulang as $sp) {
                        $pesanGrup .= "{$noSP}. *{$sp['nama']}* ({$sp['info']})\n";
                        $noSP++;
                    }
                } else {
                    $pesanGrup .= "_Belum ada siswa yang scan pulang._\n";
                }

                $pesanGrup .= "\n❌ *BELUM SCAN PULANG:*\n";
                if (count($belumPulang) > 0) {
                    $noBP = 1;
                    foreach ($belumPulang as $bp) {
                        if (!empty($bp['suffix'])) {
                            $pesanGrup .= "{$noBP}. *{$bp['nama']}* ({$bp['suffix']})\n";
                        } else {
                            $pesanGrup .= "{$noBP}. *{$bp['nama']}*\n";
                        }
                        $noBP++;
                    }
                } else {
                    $pesanGrup .= "_Semua siswa sudah scan pulang._\n";
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
        }

        $this->info("Selesai! $totalAlphaDitambahkan data Alpha otomatis ditambahkan ke database.");
        $this->info("Berhasil mengirim $totalTerkirim rekap sore via WhatsApp.");
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