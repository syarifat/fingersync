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

            // 5. Cari Jadwal Pelajaran (Sesuai Ruangan Device & Jam Sekarang)
            $jadwal = RombelJadwalPelajaran::with(['rombelMapel.mataPelajaran', 'rombelMapel.kelas'])
                ->where('hari', $hariIni)
                ->where('id_ruangan', $device->id_ruangan)
                ->where('jam_mulai', '<=', $jamSekarang)
                ->where('jam_selesai', '>=', $jamSekarang)
                ->first();

            // Jika tidak ada jadwal
            if (!$jadwal) {
                return response()->json([
                    'status' => 'INFO', 
                    'message' => 'Tidak ada KBM aktif di ruangan ' . $device->ruangan->nama_ruangan . ' pada jam ' . $jamSekarang
                ], 200);
            }

            // 6. Cek Duplikasi (Jangan sampai absen 2x di mapel yang sama)
            $sudahAbsen = Presensi::where('id_siswa', $siswa->id)
                ->where('id_rombel_jadwal_pelajaran', $jadwal->id)
                ->whereDate('tanggal', $now->format('Y-m-d'))
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
            // Kita cek sebelum data di-insert, apakah anak ini sudah absen HARI INI di jadwal manapun?
            $absenPertamaHariIni = Presensi::where('id_siswa', $siswa->id)
                ->whereDate('tanggal', $now->format('Y-m-d'))
                ->doesntExist();

            if ($absenPertamaHariIni && !empty($siswa->nohp_ortu)) {
                $waktuWA = $now->format('H:i');
                $pesanOrtu = "Halo Ayah/Ibu dari *{$siswa->nama}*,\n\n";
                $pesanOrtu .= "Kami menginformasikan bahwa ananda telah *Tiba di Sekolah* dan melakukan presensi pertama pada jam *{$waktuWA} WIB*.\n\n";
                $pesanOrtu .= "Semoga ananda belajar dengan baik hari ini. Terima kasih.";
                
                // Panggil layanan pengirim WA (akan jalan di background)
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
                'tanggal' => $now->format('Y-m-d'),
                'jam_scan' => $jamSekarang,
                'id_device' => $device->id,
                'status' => $statusKehadiran,
                'id_tahun_ajar' => $jadwal->rombelMapel->id_tahun_ajar,
            ]);

            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Berhasil Absen',
                'nama' => $siswa->nama,
                'kelas' => $jadwal->rombelMapel->kelas->nama,
                'mapel' => $jadwal->rombelMapel->mataPelajaran->nama,
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