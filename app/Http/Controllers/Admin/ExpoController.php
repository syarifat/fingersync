<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use App\Models\Siswa;
use App\Models\Presensi;
use App\Models\Device;
use App\Models\RombelJadwalPelajaran;
use App\Models\RombelMataPelajaran;
use App\Http\Controllers\Api\DeviceController;

class ExpoController extends Controller
{
    /**
     * Daftar seluruh skenario respon yang bisa dipilih untuk ESP32
     */
    public static function getModes(): array
    {
        return [
            'normal' => [
                'code' => 'normal',
                'title' => 'Mode Normal (Logika Real Sistem)',
                'desc' => 'Menjalankan alur validasi sekolah sebenarnya (cek database, jadwal, jam KBM, dan ruangan asli).',
                'category' => 'system',
                'badge' => 'bg-gray-100 text-gray-800 border-gray-300 dark:bg-gray-800 dark:text-gray-200',
                'lcd_title' => 'SESUAI SISTEM',
                'lcd_line1' => 'Validasi Normal',
                'beep' => 'Sesuai Hasil',
                'icon' => 'cog',
            ],
            'random' => [
                'code' => 'random',
                'title' => '🎲 Mode Acak (Random Scenario)',
                'desc' => 'Setiap ada yang tap jari, sistem mengacak respon (Sukses, Terlambat, Sudah Absen, Salah Ruang, dll).',
                'category' => 'special',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-900/40 dark:text-purple-300',
                'lcd_title' => 'ACAK DINAMIS',
                'lcd_line1' => 'Berganti tiap tap',
                'beep' => 'Bervariasi',
                'icon' => 'sparkles',
            ],
            'success_hadir' => [
                'code' => 'success_hadir',
                'title' => '🟢 Sukses: Hadir Tepat Waktu',
                'desc' => 'Status SUCCESS (Hadir), LCD menampilkan nama siswa, mapel KBM, dan buzzer berbunyi 1x.',
                'category' => 'success',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300',
                'lcd_title' => 'ABSENSI BERHASIL!',
                'lcd_line1' => '[Nama Siswa]',
                'lcd_line2' => 'Status: Hadir',
                'lcd_line3' => 'Expo Showcase',
                'beep' => '1x Beep',
                'icon' => 'check-circle',
            ],
            'success_terlambat' => [
                'code' => 'success_terlambat',
                'title' => '🟡 Sukses: Terlambat',
                'desc' => 'Status SUCCESS (Terlambat), toleransi waktu terlampaui. Buzzer 1x dan tersimpan sebagai Terlambat.',
                'category' => 'success',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300',
                'lcd_title' => 'ABSENSI BERHASIL!',
                'lcd_line1' => '[Nama Siswa]',
                'lcd_line2' => 'Status: Terlambat',
                'lcd_line3' => 'Expo Showcase',
                'beep' => '1x Beep',
                'icon' => 'clock',
            ],
            'success_pulang' => [
                'code' => 'success_pulang',
                'title' => '🔵 Sukses: Absen Pulang',
                'desc' => 'Status SUCCESS (Pulang), simulasi scan setelah KBM usai. Tersimpan sebagai tipe scan pulang.',
                'category' => 'success',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900/40 dark:text-blue-300',
                'lcd_title' => 'ABSENSI BERHASIL!',
                'lcd_line1' => '[Nama Siswa]',
                'lcd_line2' => 'Status: Pulang',
                'lcd_line3' => 'Pulang Sekolah',
                'beep' => '1x Beep',
                'icon' => 'arrow-right-circle',
            ],
            'warn_sudah_absen' => [
                'code' => 'warn_sudah_absen',
                'title' => '⚠️ Peringatan: Sudah Pernah Absen',
                'desc' => 'Status WARN, menolak duplikasi scan jika siswa sudah absen pada mapel/sesi yang sama. Buzzer 2x.',
                'category' => 'warning',
                'badge' => 'bg-orange-100 text-orange-800 border-orange-300 dark:bg-orange-900/40 dark:text-orange-300',
                'lcd_title' => 'SUDAH ABSEN!',
                'lcd_line1' => '[Nama Siswa]',
                'beep' => '2x Beep',
                'icon' => 'exclamation',
            ],
            'info_tidak_ada_jadwal' => [
                'code' => 'info_tidak_ada_jadwal',
                'title' => 'ℹ️ Info: Tidak Ada Jadwal KBM',
                'desc' => 'Status INFO, jam scan berada di luar jam KBM atau jam istirahat. Buzzer 3x penolakan.',
                'category' => 'info',
                'badge' => 'bg-sky-100 text-sky-800 border-sky-300 dark:bg-sky-900/40 dark:text-sky-300',
                'lcd_title' => 'AKSES DITOLAK!',
                'lcd_line1' => 'Tidak ada KBM aktif',
                'lcd_line2' => 'untuk kelas Anda',
                'beep' => '3x Beep',
                'icon' => 'information-circle',
            ],
            'error_salah_ruangan' => [
                'code' => 'error_salah_ruangan',
                'title' => '❌ Error: Salah Ruangan Kelas',
                'desc' => 'Status ERROR, siswa melakukan scan di device ruang lain yang tidak sesuai jadwal. Buzzer 3x.',
                'category' => 'error',
                'badge' => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/40 dark:text-red-300',
                'lcd_title' => 'AKSES DITOLAK!',
                'lcd_line1' => 'Salah Ruangan!',
                'lcd_line2' => 'Kelas Anda di Lab RPL',
                'beep' => '3x Beep',
                'icon' => 'x-circle',
            ],
            'error_tidak_terdaftar' => [
                'code' => 'error_tidak_terdaftar',
                'title' => '❌ Error: Sidik Jari Tidak Terdaftar',
                'desc' => 'Status ERROR, data sidik jari / ID tidak ditemukan di database server. Buzzer 3x.',
                'category' => 'error',
                'badge' => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-900/40 dark:text-rose-300',
                'lcd_title' => 'AKSES DITOLAK!',
                'lcd_line1' => 'Data sidik jari',
                'lcd_line2' => 'siswa tidak ada!',
                'beep' => '3x Beep',
                'icon' => 'finger-print',
            ],
            'error_belum_masuk_kelas' => [
                'code' => 'error_belum_masuk_kelas',
                'title' => '❌ Error: Siswa Belum Terdaftar di Kelas',
                'desc' => 'Status ERROR, siswa ada di database tetapi belum di-plotting ke rombel kelas aktif.',
                'category' => 'error',
                'badge' => 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/40 dark:text-red-300',
                'lcd_title' => 'AKSES DITOLAK!',
                'lcd_line1' => 'Siswa belum masuk',
                'lcd_line2' => 'kelas manapun!',
                'beep' => '3x Beep',
                'icon' => 'user-remove',
            ],
            'error_hari_libur' => [
                'code' => 'error_hari_libur',
                'title' => '❌ Error: Hari Libur Nasional / Sekolah',
                'desc' => 'Status ERROR, kalender hari ini terdeteksi sebagai hari libur. Absensi KBM dinonaktifkan.',
                'category' => 'error',
                'badge' => 'bg-pink-100 text-pink-800 border-pink-300 dark:bg-pink-900/40 dark:text-pink-300',
                'lcd_title' => 'AKSES DITOLAK!',
                'lcd_line1' => 'Hari Libur:',
                'lcd_line2' => 'Libur Nasional',
                'beep' => '3x Beep',
                'icon' => 'calendar',
            ],
            'error_guru_izin' => [
                'code' => 'error_guru_izin',
                'title' => '❌ Error: Guru Sedang Izin / KBM Libur',
                'desc' => 'Status ERROR, guru pengampu pada jadwal ini tercatat sedang izin libur di sistem KBM khusus.',
                'category' => 'error',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300',
                'lcd_title' => 'AKSES DITOLAK!',
                'lcd_line1' => 'Jadwal Libur,',
                'lcd_line2' => 'Guru Sedang Izin',
                'beep' => '3x Beep',
                'icon' => 'badge-check',
            ],
        ];
    }

    /**
     * Tampilan utama panel Expo Control Center
     */
    public function index()
    {
        $currentMode = Cache::store('file')->get('expo_response_mode', 'normal');
        $currentTargetStudentId = Cache::store('file')->get('expo_target_student_id', 'auto');
        $autoGuest = Cache::store('file')->get('expo_auto_guest', true);

        $modes = self::getModes();
        $selectedModeDetails = $modes[$currentMode] ?? $modes['normal'];

        // Data Siswa untuk simulasi
        $siswas = Siswa::with(['kelas', 'rombelKelas.kelas'])->orderBy('nama')->get();

        // Hari dan Waktu Sekarang
        $now = Carbon::now('Asia/Jakarta');
        $tanggalHariIni = $now->format('Y-m-d');
        $hariIndo = DeviceController::getHariIndo($now->format('l'));

        // Statistik Presensi Hari Ini
        $presensiHariIni = Presensi::with(['siswa.kelas', 'siswa.rombelKelas.kelas', 'device'])
            ->whereDate('tanggal', $tanggalHariIni)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $presensiHariIni->count(),
            'hadir' => $presensiHariIni->where('status', 'Hadir')->where('tipe_scan', '!=', 'pulang')->count(),
            'terlambat' => $presensiHariIni->where('status', 'Terlambat')->count(),
            'pulang' => $presensiHariIni->where('tipe_scan', 'pulang')->count(),
        ];

        // Device terdaftar
        $devices = Device::all();

        // Jadwal hari ini
        $jadwalsHariIni = RombelJadwalPelajaran::with(['rombelMapel.mataPelajaran', 'rombelMapel.kelas', 'ruangan'])
            ->where('hari', $hariIndo)
            ->orderBy('jam_mulai')
            ->get();

        return view('admin.expo.index', compact(
            'currentMode',
            'currentTargetStudentId',
            'autoGuest',
            'modes',
            'selectedModeDetails',
            'siswas',
            'stats',
            'presensiHariIni',
            'devices',
            'jadwalsHariIni',
            'now',
            'hariIndo'
        ));
    }

    /**
     * Set mode respon ESP32
     */
    public function setMode(Request $request)
    {
        $validModes = array_keys(self::getModes());
        $request->validate([
            'mode' => 'required|in:' . implode(',', $validModes),
            'target_student_id' => 'nullable|string',
            'auto_guest' => 'nullable',
        ]);

        Cache::store('file')->forever('expo_response_mode', $request->mode);
        Cache::store('file')->forever('expo_target_student_id', $request->target_student_id ?? 'auto');
        Cache::store('file')->forever('expo_auto_guest', $request->boolean('auto_guest'));

        $modeTitle = self::getModes()[$request->mode]['title'] ?? $request->mode;

        return redirect()->route('admin.expo.index')->with('success', "Mode respon ESP32 berhasil diubah menjadi: {$modeTitle}");
    }

    /**
     * Hapus semua presensi hari ini
     */
    public function resetPresensiHariIni()
    {
        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $deleted = Presensi::whereDate('tanggal', $today)->delete();

        return redirect()->route('admin.expo.index')->with('success', "Berhasil mereset data! Sebanyak {$deleted} data presensi hari ini ({$today}) telah dibersihkan.");
    }

    /**
     * Quick Migrate Fresh & Seed (1-Klik)
     */
    public function migrateFreshSeed()
    {
        try {
            Artisan::call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);

            return redirect()->route('admin.expo.index')->with('success', "Database berhasil di-migrate fresh & di-seed ulang secara sempurna!");
        } catch (\Throwable $e) {
            return redirect()->route('admin.expo.index')->with('error', "Gagal menjalankan migrate fresh: " . $e->getMessage());
        }
    }

    /**
     * Sinkronkan Jam Jadwal KBM Hari Ini ke Jam Sekarang
     * Agar status KBM langsung "Sedang Berlangsung" saat demo expo
     */
    public function syncJadwalSekarang()
    {
        try {
            $now = Carbon::now('Asia/Jakarta');
            $hariIndo = DeviceController::getHariIndo($now->format('l'));

            $jamMulai = $now->copy()->subMinutes(20)->format('H:i:s');
            $jamSelesai = $now->copy()->addHours(4)->format('H:i:s');

            // Cek apakah ada jadwal di hari ini
            $jadwalHariIni = RombelJadwalPelajaran::where('hari', $hariIndo)->get();

            if ($jadwalHariIni->isNotEmpty()) {
                // Update jadwal pertama hari ini agar mencakup jam sekarang
                $first = $jadwalHariIni->first();
                $first->update([
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                ]);
            } else {
                // Jika hari ini libur/tidak ada jadwal (misal Sabtu/Minggu),
                // ubah semua jadwal yang ada menjadi hari ini dan set jamnya
                $anyJadwal = RombelJadwalPelajaran::first();
                if ($anyJadwal) {
                    $anyJadwal->update([
                        'hari' => $hariIndo,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                    ]);
                }
            }

            return redirect()->route('admin.expo.index')->with('success', "Jadwal KBM hari {$hariIndo} berhasil disinkronkan ke jam sekarang ({$jamMulai} s/d {$jamSelesai})! Status KBM kini aktif.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.expo.index')->with('error', "Gagal menyinkronkan jadwal: " . $e->getMessage());
        }
    }

    /**
     * Simulasi Tap Sidik Jari via Browser (Emergency Scanner)
     */
    public function simulateScan(Request $request)
    {
        $request->validate([
            'id_device' => 'required|string',
            'fingerprint_id' => 'required|integer',
        ]);

        $deviceController = new DeviceController();
        $response = $deviceController->scan($request);

        return response()->json($response->getData(), $response->status());
    }
}
