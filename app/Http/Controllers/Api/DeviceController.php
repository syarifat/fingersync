<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Device;
use App\Models\Siswa;
use App\Models\RombelJadwalPelajaran;
use App\Models\Presensi;

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
                ], 404);
            }

            // 4. Cek Siswa (Apakah ada?)
            $siswa = Siswa::where('fingerprint_id', $request->fingerprint_id)->first();
            if (!$siswa) {
                return response()->json([
                    'status' => 'ERROR', 
                    'message' => 'Data sidik jari siswa tidak ditemukan!'
                ], 404);
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
                ], 404);
            }

            // 6. Cek Duplikasi (Jangan sampai absen 2x)
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

            // 7. Simpan Presensi
            // Logic Terlambat: Toleransi 15 menit
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

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'FATAL_ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
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