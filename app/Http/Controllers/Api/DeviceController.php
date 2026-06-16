<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Device;
use App\Models\Siswa;
use App\Models\RombelJadwalPelajaran;
use App\Models\Presensi;
use App\Models\FingerprintInbox;
use App\Models\DeviceTask;
use App\Services\WhatsAppService; // <-- PASTIKAN CLASS INI SUDAH ANDA BUAT (Seperti petunjuk sebelumnya)

class DeviceController extends Controller
{
    public function scan(Request $request)
    {
        // ---------------------------------------------------------
        // MODE TESTING (Ubah jadi false jika sudah mau dipasang di sekolah)
        // ---------------------------------------------------------
        $isTestMode = false; 

        try {
            // 1. Validasi Input
            $request->validate([
                'id_device' => 'required|string',
                'fingerprint_id' => 'required|integer',
            ]);

            // 2. Setup Waktu (Normal vs Test Mode)
            if ($isTestMode) {
                // Paksa waktu jadi SENIN jam 07:15 (Biar jadwal ketemu saat testing)
                $now = Carbon::create(2025, 10, 27, 7, 15, 0); 
                $hariIni = 'Senin';
            } else {
                // Waktu Realtime
                $now = Carbon::now('Asia/Jakarta');
                $hariIni = $this->getHariIndo($now->format('l'));
            }

            $jamSekarang = $now->format('H:i:s');

            // 2.a. Cek Hari Libur
            $tanggalScan = $now->format('Y-m-d');
            if (\App\Models\HariLibur::isHoliday($tanggalScan)) {
                $namaLibur = \App\Models\HariLibur::getHolidayName($tanggalScan);
                return response()->json([
                    'status' => 'ERROR',
                    'message' => 'Hari Libur: ' . $namaLibur
                ], 200);
            }

            // 3. Cek Device (Apakah terdaftar?)
            $device = Device::where('id_device', $request->id_device)->first();
            if (!$device) {
                return response()->json([
                    'status' => 'ERROR', 
                    'message' => 'ID Device tidak ditemukan di database!'
                ], 200);
            }

            // 4. Cek Siswa (Apakah ada?)
            $siswa = Siswa::where('fingerprint_id', $request->fingerprint_id)->first();
            if (!$siswa) {
                return response()->json([
                    'status' => 'ERROR', 
                    'message' => 'Data sidik jari siswa tidak ditemukan!'
                ], 200);
            }

            // Cari rombel aktif siswa untuk tahun ajaran berjalan
            $activeYear = \App\Models\TahunAjar::where('status_aktif', true)->value('id');
            $rombel = \App\Models\RombelKelas::where('id_siswa', $siswa->id)
                ->where('id_tahun_ajar', $activeYear)
                ->first();

            if (!$rombel) {
                return response()->json([
                    'status' => 'ERROR', 
                    'message' => 'Siswa belum terdaftar di kelas manapun!'
                ], 200);
            }

            // 4.a. Cek Kegiatan Sekolah Serentak hari ini
            $kegiatanSerentak = \App\Models\KegiatanSekolah::whereDate('tanggal', $tanggalScan)
                ->where('tipe', 'serentak')
                ->first();

            if ($kegiatanSerentak) {
                // Tentukan tipe scan (datang / pulang) berdasarkan jam saat ini
                $tipeScan = null;
                $jamSkrgStr = $jamSekarang;

                if ($jamSkrgStr >= $kegiatanSerentak->jam_mulai_datang && $jamSkrgStr <= $kegiatanSerentak->jam_selesai_datang) {
                    $tipeScan = 'datang';
                } elseif ($jamSkrgStr >= $kegiatanSerentak->jam_mulai_pulang && $jamSkrgStr <= $kegiatanSerentak->jam_selesai_pulang) {
                    $tipeScan = 'pulang';
                }

                if (!$tipeScan) {
                    return response()->json([
                        'status' => 'INFO',
                        'message' => 'Bukan waktu absen untuk Kegiatan: ' . $kegiatanSerentak->nama_kegiatan
                    ], 200);
                }

                // Cek apakah sudah absen untuk tipe scan ini hari ini
                $sudahAbsenKegiatan = Presensi::where('id_siswa', $siswa->id)
                    ->where('id_kegiatan_sekolah', $kegiatanSerentak->id)
                    ->where('tipe_scan_kegiatan', $tipeScan)
                    ->whereDate('tanggal', $tanggalScan)
                    ->exists();

                if ($sudahAbsenKegiatan) {
                    return response()->json([
                        'status' => 'WARN',
                        'message' => 'Sudah Absen ' . ucfirst($tipeScan) . '!',
                        'nama' => $siswa->nama
                    ]);
                }

                // Kirim notifikasi WA
                if (!empty($siswa->nohp_ortu)) {
                    $waktuWA = $now->format('H:i');
                    $pesanOrtu = "Halo Ayah/Ibu dari *{$siswa->nama}*,\n\n";
                    if ($tipeScan === 'datang') {
                        $pesanOrtu .= "Kami menginformasikan bahwa ananda telah *Tiba di Sekolah* untuk mengikuti kegiatan *{$kegiatanSerentak->nama_kegiatan}* pada jam *{$waktuWA} WIB*.\n\n";
                    } else {
                        $pesanOrtu .= "Kami menginformasikan bahwa ananda telah melakukan presensi *Pulang Kegiatan* *{$kegiatanSerentak->nama_kegiatan}* pada jam *{$waktuWA} WIB*.\n\n";
                    }
                    $pesanOrtu .= "Terima kasih.";
                    WhatsAppService::send($siswa->nohp_ortu, $pesanOrtu, $siswa->id);
                }

                // Simpan presensi kegiatan
                Presensi::create([
                    'id_siswa' => $siswa->id,
                    'id_rombel_jadwal_pelajaran' => null,
                    'id_kegiatan_sekolah' => $kegiatanSerentak->id,
                    'tipe_scan_kegiatan' => $tipeScan,
                    'tanggal' => $tanggalScan,
                    'jam_scan' => $jamSekarang,
                    'id_device' => $device->id,
                    'status' => 'Hadir',
                    'id_tahun_ajar' => $activeYear,
                ]);

                $rombelKelas = \App\Models\Kelas::where('id', $rombel->id_kelas)->value('nama');

                return response()->json([
                    'status' => 'SUCCESS',
                    'message' => 'Berhasil Absen',
                    'nama' => $siswa->nama,
                    'kelas' => $rombelKelas ?? '-',
                    'mapel' => 'Kegiatan: ' . $kegiatanSerentak->nama_kegiatan,
                    'stat' => 'Hadir (' . ucfirst($tipeScan) . ')'
                ]);
            }

            // 4.b. Tentukan apakah ini scan pulang dinamis (setelah KBM terakhir selesai s/d jam 16:00)
            $lastJadwal = RombelJadwalPelajaran::where('hari', $hariIni)
                ->whereHas('rombelMataPelajaran', function($q) use ($rombel, $activeYear) {
                    $q->where('id_kelas', $rombel->id_kelas)
                      ->where('id_tahun_ajar', $activeYear);
                })
                ->orderBy('jam_selesai', 'desc')
                ->first();

            $jamMulaiPulang = $lastJadwal ? $lastJadwal->jam_selesai : '12:00:00';
            $jamSelesaiPulang = '16:00:00';

            $isScanPulang = ($jamSekarang >= $jamMulaiPulang && $jamSekarang <= $jamSelesaiPulang);

            if ($isScanPulang) {
                // Cek apakah sudah absen pulang hari ini
                $sudahAbsenPulang = Presensi::where('id_siswa', $siswa->id)
                    ->whereDate('tanggal', $tanggalScan)
                    ->where('tipe_scan', 'pulang')
                    ->exists();

                if ($sudahAbsenPulang) {
                    return response()->json([
                        'status' => 'WARN',
                        'message' => 'Sudah Absen Pulang!',
                        'nama' => $siswa->nama
                    ]);
                }

                // Kirim notifikasi WA Pulang
                if (!empty($siswa->nohp_ortu)) {
                    $waktuWA = $now->format('H:i');
                    $pesanOrtu = "Halo Ayah/Ibu dari *{$siswa->nama}*,\n\n";
                    $pesanOrtu .= "Kami menginformasikan bahwa ananda telah melakukan presensi *Pulang Sekolah* pada jam *{$waktuWA} WIB*.\n\n";
                    $pesanOrtu .= "Terima kasih.";
                    WhatsAppService::send($siswa->nohp_ortu, $pesanOrtu, $siswa->id);
                }

                // Simpan presensi pulang
                Presensi::create([
                    'id_siswa' => $siswa->id,
                    'id_rombel_jadwal_pelajaran' => null,
                    'tanggal' => $tanggalScan,
                    'jam_scan' => $jamSekarang,
                    'id_device' => $device->id,
                    'status' => 'Hadir',
                    'tipe_scan' => 'pulang',
                    'id_tahun_ajar' => $activeYear,
                ]);

                $rombelKelas = \App\Models\Kelas::where('id', $rombel->id_kelas)->value('nama');

                return response()->json([
                    'status' => 'SUCCESS',
                    'message' => 'Berhasil Absen Pulang',
                    'nama' => $siswa->nama,
                    'kelas' => $rombelKelas ?? '-',
                    'mapel' => 'Pulang Sekolah',
                    'stat' => 'Pulang'
                ]);
            }

            // 5. Cari Jadwal Pelajaran (Sesuai Kelas Siswa & Jam Sekarang)
            $jadwal = RombelJadwalPelajaran::with(['rombelMapel.mataPelajaran', 'rombelMapel.kelas', 'ruangan'])
                ->where('hari', $hariIni)
                ->whereHas('rombelMapel', function($q) use ($rombel) {
                    $q->where('id_kelas', $rombel->id_kelas);
                })
                ->where('jam_mulai', '<=', $jamSekarang)
                ->where('jam_selesai', '>=', $jamSekarang)
                ->first();

            // Jika tidak ada jadwal
            if (!$jadwal) {
                return response()->json([
                    'status' => 'INFO', 
                    'message' => 'Tidak ada KBM aktif untuk kelas Anda saat ini!'
                ], 200);
            }

            // Cek apakah ruangan tempat scan (device) sama dengan ruangan terjadwal
            if ($jadwal->id_ruangan != $device->id_ruangan) {
                return response()->json([
                    'status' => 'ERROR', 
                    'message' => 'Salah Ruangan! Kelas Anda di ' . $jadwal->ruangan->nama_ruangan
                ], 200);
            }

            // Cek kondisi khusus Guru pada jadwal ini
            $kbmKhusus = \App\Models\GuruKbmKhusus::with('guruPengganti')
                ->where('id_rombel_jadwal_pelajaran', $jadwal->id)
                ->whereDate('tanggal', $tanggalScan)
                ->first();

            // 6. Cek Duplikasi (Jangan sampai absen 2x di mapel yang sama)
            $sudahAbsen = Presensi::where('id_siswa', $siswa->id)
                ->where('id_rombel_jadwal_pelajaran', $jadwal->id)
                ->whereDate('tanggal', $tanggalScan)
                ->exists();

            if ($sudahAbsen) {
                return response()->json([
                    'status' => 'WARN',
                    'message' => 'Sudah Absen!',
                    'nama' => $siswa->nama
                ]);
            }

            // =====================================================================
            // LOGIKA BARU: CEK ABSENSI PERTAMA HARI INI & KIRIM WA KE ORTU
            // =====================================================================
            $absenPertamaHariIni = Presensi::where('id_siswa', $siswa->id)
                ->whereDate('tanggal', $tanggalScan)
                ->doesntExist();

            if ($absenPertamaHariIni && !empty($siswa->nohp_ortu)) {
                $waktuWA = $now->format('H:i');
                $pesanOrtu = "Halo Ayah/Ibu dari *{$siswa->nama}*,\n\n";

                if ($kbmKhusus && in_array($kbmKhusus->status, ['izin', 'absen'])) {
                    $keteranganTugas = $kbmKhusus->keterangan ? "Tugas: {$kbmKhusus->keterangan}" : "Belajar Mandiri";
                    $pesanOrtu .= "Kami menginformasikan bahwa ananda telah *Tiba di Sekolah* pada jam *{$waktuWA} WIB*.\n";
                    $pesanOrtu .= "Pada KBM jam ini, Guru mata pelajaran *{$jadwal->rombelMapel->mataPelajaran->nama}* sedang berhalangan hadir ({$kbmKhusus->status}). Ananda belajar mandiri di kelas. ({$keteranganTugas})\n\n";
                } elseif ($kbmKhusus && $kbmKhusus->status === 'diganti' && $kbmKhusus->guruPengganti) {
                    $pesanOrtu .= "Kami menginformasikan bahwa ananda telah *Tiba di Sekolah* pada jam *{$waktuWA} WIB*.\n";
                    $pesanOrtu .= "KBM *{$jadwal->rombelMapel->mataPelajaran->nama}* hari ini didampingi oleh Guru Pengganti *{$kbmKhusus->guruPengganti->nama}*.\n\n";
                } else {
                    $pesanOrtu .= "Kami menginformasikan bahwa ananda telah *Tiba di Sekolah* dan melakukan presensi pertama pada jam *{$waktuWA} WIB*.\n\n";
                }

                $pesanOrtu .= "Semoga ananda belajar dengan baik hari ini. Terima kasih.";
                WhatsAppService::send($siswa->nohp_ortu, $pesanOrtu, $siswa->id);
            }
            // =====================================================================

            // 7. Simpan Presensi
            // Logic Terlambat: Toleransi 15 menit dari jam mulai mapel
            $jamMulai = Carbon::parse($jadwal->jam_mulai);
            $selisihMenit = $jamMulai->diffInMinutes($now, false);
            $statusKehadiran = ($selisihMenit > 15) ? 'Terlambat' : 'Hadir';

            Presensi::create([
                'id_siswa' => $siswa->id,
                'id_rombel_jadwal_pelajaran' => $jadwal->id,
                'tanggal' => $tanggalScan,
                'jam_scan' => $jamSekarang,
                'id_device' => $device->id,
                'status' => $statusKehadiran,
                'id_tahun_ajar' => $jadwal->rombelMapel->id_tahun_ajar,
            ]);

            // Tentukan pesan respons sukses berdasarkan kondisi guru
            $customMessage = 'Berhasil Absen';
            $customMapel = $jadwal->rombelMapel->mataPelajaran->nama;

            if ($kbmKhusus) {
                if (in_array($kbmKhusus->status, ['izin', 'absen'])) {
                    $customMessage = 'KBM Mandiri (' . ucfirst($kbmKhusus->status) . ')';
                    $customMapel = $jadwal->rombelMapel->mataPelajaran->nama . ' (Mandiri)';
                } elseif ($kbmKhusus->status === 'diganti' && $kbmKhusus->guruPengganti) {
                    $customMessage = 'Guru Pengganti: ' . $kbmKhusus->guruPengganti->nama;
                    $customMapel = $jadwal->rombelMapel->mataPelajaran->nama . ' (Pengganti)';
                }
            }

            return response()->json([
                'status' => 'SUCCESS',
                'message' => $customMessage,
                'nama' => $siswa->nama,
                'kelas' => $jadwal->rombelMapel->kelas->nama,
                'mapel' => $customMapel,
                'stat' => $statusKehadiran
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'FATAL_ERROR',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    // ==========================================================
    // 1. ALAT 1 MENGIRIM ID BARU KE INBOX (MODE REGISTRASI)
    // ==========================================================
    public function registerNewId(Request $request)
    {
        $request->validate([
            'id_device' => 'required|string',
            'fingerprint_id' => 'required|integer'
        ]);

        // Cek apakah ID ini sudah ada di inbox dan masih pending, biar gak dobel
        $exists = FingerprintInbox::where('id_device', $request->id_device)
                    ->where('fingerprint_id', $request->fingerprint_id)
                    ->where('status', 'pending')
                    ->first();

        if (!$exists) {
            FingerprintInbox::create([
                'id_device' => $request->id_device,
                'fingerprint_id' => $request->fingerprint_id,
                'status' => 'pending'
            ]);
        }

        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'ID berhasil masuk ke Inbox Admin'
        ]);
    }

    // ==========================================================
    // 2. ALAT 2 & 3 RUTIN BERTANYA "ADA TUGAS?" (POLLING)
    // ==========================================================
    public function checkTask(Request $request)
    {
        $id_device = $request->query('id_device'); // Alat mengirim ID-nya via parameter URL

        if (!$id_device) {
            return response()->json(['status' => 'ERROR', 'message' => 'ID Device kosong']);
        }

        // Cari 1 tugas yang masih 'pending' untuk alat ini
        $task = DeviceTask::where('id_device', $id_device)
                  ->where('status', 'pending')
                  ->oldest() // Ambil tugas yang paling lama mengantri
                  ->first();

        if ($task) {
            return response()->json([
                'status' => 'TASK_AVAILABLE',
                'task_id' => $task->id,
                'action' => $task->action, // 'enroll'
                'target_id' => $task->fingerprint_id // ID 15
            ]);
        }

        // Jika tidak ada tugas, suruh alat standby (balik ke mode absen biasa)
        return response()->json([
            'status' => 'STANDBY'
        ]);
    }

    // ==========================================================
    // 3. ALAT 2 & 3 LAPOR TUGAS SELESAI
    // ==========================================================
    public function completeTask(Request $request)
    {
        $request->validate([
            'task_id' => 'required|integer',
            'status' => 'required|in:done,failed'
        ]);

        $task = DeviceTask::find($request->task_id);
        
        if ($task) {
            $task->update(['status' => $request->status]);
            return response()->json(['status' => 'SUCCESS', 'message' => 'Tugas diperbarui']);
        }

        return response()->json(['status' => 'ERROR', 'message' => 'Tugas tidak ditemukan'], 404);
    }

    // Helper: Translate Hari
    private function getHariIndo($day) {
        $days = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        return $days[$day] ?? 'Senin';
    }
}