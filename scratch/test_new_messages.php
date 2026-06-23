<?php

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Presensi;
use App\Models\RombelJadwalPelajaran;
use App\Models\KegiatanSekolah;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

// Load Laravel Bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🚀 Memulai Simulasi Pengujian Pesan WhatsApp untuk Konsep Baru...\n";

// Mengatur locale dan waktu ke Jakarta untuk kecocokan hari
Carbon::setLocale('id');

$siswa1 = Siswa::find(1);
$siswa2 = Siswa::find(2);

if (!$siswa1 || !$siswa2) {
    echo "❌ Error: Siswa 1 atau Siswa 2 tidak ditemukan di database.\n";
    exit(1);
}

// Gunakan tanggal hari ini (Selasa, 23 Juni 2026)
$tanggalHariIni = '2026-06-23';
$activeYear = \App\Models\TahunAjar::where('status_aktif', true)->value('id');

// Bersihkan data presensi hari ini
Presensi::where('tanggal', $tanggalHariIni)->delete();

// Cari jadwal TKJ 1 dan TKJ 2 hari Selasa
$jadwal1 = RombelJadwalPelajaran::where('hari', 'Selasa')
    ->whereHas('rombelMataPelajaran.kelas', fn($q) => $q->where('nama', 'X TKJ 1'))->first();
$jadwal2 = RombelJadwalPelajaran::where('hari', 'Selasa')
    ->whereHas('rombelMataPelajaran.kelas', fn($q) => $q->where('nama', 'X TKJ 2'))->first();

if (!$jadwal1 || !$jadwal2) {
    echo "❌ Error: Jadwal untuk hari Selasa tidak ditemukan.\n";
    exit(1);
}

// 1. Simulasikan Siswa 1 sudah Hadir, Siswa 2 belum
Presensi::create([
    'id_siswa' => $siswa1->id,
    'id_rombel_jadwal_pelajaran' => $jadwal1->id,
    'tanggal' => $tanggalHariIni,
    'jam_scan' => '07:05:00',
    'status' => 'Hadir',
    'id_device' => 1,
    'id_tahun_ajar' => $activeYear,
]);

echo "💬 Menguji Rekap Pagi (Hadir Pagi)...\n";
Artisan::call('absensi:rekap-pagi');
echo "Output: " . Artisan::output() . "\n";

// 2. Simulasikan Absen Pulang Siswa 1
Presensi::create([
    'id_siswa' => $siswa1->id,
    'tanggal' => $tanggalHariIni,
    'tipe_scan' => 'pulang',
    'jam_scan' => '12:15:00',
    'status' => 'Hadir',
    'id_device' => 1,
    'id_tahun_ajar' => $activeYear,
]);

echo "💬 Menguji Rekap Sore (16:00)...\n";
Artisan::call('absensi:rekap-sore');
echo "Output: " . Artisan::output() . "\n";

echo "💬 Menguji Rekap Pulang (17:30)...\n";
Artisan::call('absensi:rekap-pulang');
echo "Output: " . Artisan::output() . "\n";

echo "🎉 Simulasi selesai!\n";
