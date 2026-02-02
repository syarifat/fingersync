<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Siswa;
use App\Models\RombelJadwalPelajaran;
use App\Models\Presensi;
use Carbon\Carbon;

class DeviceController extends Controller
{
    public function scan(Request $request)
    {
        // 1. VALIDASI INPUT DARI ESP32
        // ESP32 harus kirim JSON: { "id_device": "ESP32-XXXX", "fingerprint_id": 123 }
        $request->validate([
            'id_device' => 'required|string',
            'fingerprint_id' => 'required|integer',
        ]);

        $now = Carbon::now('Asia/Jakarta'); // Wajib set timezone
        $hariIni = $this->getHariIndo($now->format('l'));
        $jamSekarang = $now->format('H:i:s');

        // 2. CEK DEVICE & RUANGAN
        $device = Device::where('id_device', $request->id_device)->first();
        if (!$device) {
            return response()->json(['status' => 'ERROR', 'message' => 'Device Tidak Dikenal!'], 404);
        }

        // 3. CEK SISWA
        $siswa = Siswa::where('fingerprint_id', $request->fingerprint_id)->first();
        if (!$siswa) {
            return response()->json(['status' => 'ERROR', 'message' => 'Siswa Tidak Dikenal!'], 404);
        }

        // 4. CEK JADWAL DI RUANGAN TERSEBUT SAAT INI
        // Cari jadwal yang:
        // - Harinya sama dengan hari ini
        // - Ruangannya sama dengan ruangan device
        // - Jam sekarang berada di antara jam mulai & jam selesai
        $jadwal = RombelJadwalPelajaran::with('rombelMapel.mataPelajaran', 'rombelMapel.kelas')
            ->where('hari', $hariIni)
            ->where('id_ruangan', $device->id_ruangan)
            ->where('jam_mulai', '<=', $jamSekarang)
            ->where('jam_selesai', '>=', $jamSekarang)
            ->first();

        if (!$jadwal) {
            return response()->json([
                'status' => 'INFO', 
                'message' => 'Tidak ada jadwal aktif saat ini di ruangan ' . $device->ruangan->nama_ruangan
            ], 404);
        }

        // 5. CEK DUPLIKASI (SPAM PREVENTION)
        // Apakah siswa sudah absen di jadwal ini pada tanggal hari ini?
        $sudahAbsen = Presensi::where('id_siswa', $siswa->id)
            ->where('id_rombel_jadwal_pelajaran', $jadwal->id)
            ->whereDate('tanggal', $now->format('Y-m-d'))
            ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'status' => 'WARN',
                'message' => 'Sudah Absen!',
                'nama' => $siswa->nama
            ]); // Tetap return 200 biar ESP32 gak error
        }

        // 6. TENTUKAN STATUS KEHADIRAN
        // Logic: 
        // - Telat toleransi 15 menit dari jam mulai.
        // - Lewat 15 menit = Terlambat.
        $jamMulai = Carbon::parse($jadwal->jam_mulai);
        $selisihMenit = $jamMulai->diffInMinutes($now, false); // false agar bisa negatif

        $statusKehadiran = 'Hadir';
        if ($selisihMenit > 15) {
            $statusKehadiran = 'Terlambat';
        }

        // 7. SIMPAN PRESENSI
        Presensi::create([
            'id_siswa' => $siswa->id,
            'id_rombel_jadwal_pelajaran' => $jadwal->id,
            'tanggal' => $now->format('Y-m-d'),
            'jam_scan' => $jamSekarang,
            'id_device' => $device->id,
            'status' => $statusKehadiran,
            'id_tahun_ajar' => $jadwal->rombelMapel->id_tahun_ajar,
        ]);

        // 8. RESPONSE SUKSES KE ESP32
        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'Berhasil!',
            'nama' => $siswa->nama, // Untuk ditampilkan di LCD
            'kelas' => $jadwal->rombelMapel->kelas->nama,
            'stat' => $statusKehadiran
        ]);
    }

    // Helper Convert Hari
    private function getHariIndo($day)
    {
        return match($day) {
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
            default => 'Senin'
        };
    }
}