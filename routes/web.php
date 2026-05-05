<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Guru\GuruController as GuruGuruController;
use App\Http\Controllers\Admin\RuanganController;
use App\Http\Controllers\Admin\GuruController as AdminGuruController;
use App\Http\Controllers\Auth\AktivasiGuruController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\MataPelajaranController;
use App\Http\Controllers\Admin\TahunAjarController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\RombelKelasController;
use App\Http\Controllers\Admin\RombelMataPelajaranController;
use App\Http\Controllers\Admin\RombelJadwalPelajaranController;
use App\Http\Controllers\Admin\PresensiController;
use App\Http\Controllers\Admin\UserController;

// Route khusus untuk eksekusi Cron Job via Web (cron-job.org)
Route::get('/cron/cek-anomali/{token}', function ($token) {
    if ($token !== 'FINGERSYNC-SECURE-123') return abort(403, 'Unauthorized');
    Artisan::call('absensi:cek-anomali');
    return 'Cek Anomali dieksekusi: ' . Artisan::output();
});

Route::get('/cron/rekap-sore/{token}', function ($token) {
    if ($token !== 'FINGERSYNC-SECURE-123') return abort(403, 'Unauthorized');
    Artisan::call('absensi:rekap-sore');
    return 'Rekap Sore dieksekusi: ' . Artisan::output();
});


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('aktivasi-guru', [AktivasiGuruController::class, 'create'])->name('aktivasi.create');
    Route::post('aktivasi-guru', [AktivasiGuruController::class, 'store'])->name('aktivasi.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Role Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/siswa/inbox', [SiswaController::class, 'inbox'])->name('siswa.inbox');
    // Fitur Import Excel Siswa
    Route::get('/siswa/template', [SiswaController::class, 'downloadTemplate'])->name('siswa.template');
    Route::post('/siswa/import', [SiswaController::class, 'importExcel'])->name('siswa.import');
    Route::resource('siswa', SiswaController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('ruangan', RuanganController::class);
    Route::get('/guru/template', [AdminGuruController::class, 'downloadTemplate'])->name('guru.template');
    Route::post('/guru/import', [AdminGuruController::class, 'importExcel'])->name('guru.import');
    Route::resource('guru', AdminGuruController::class);
    Route::get('/jurusan/pdf', [JurusanController::class, 'downloadPdf'])->name('jurusan.pdf');
    Route::resource('jurusan', JurusanController::class);
    Route::resource('mata-pelajaran', MataPelajaranController::class);
    Route::resource('tahun-ajar', TahunAjarController::class);
    Route::resource('device', DeviceController::class);
    Route::post('tahun-ajar/switch', [TahunAjarController::class, 'switch'])->name('tahun-ajar.switch');

    // Rombel
    // ROMBEL KELAS (Konsep Baru)
    Route::get('/rombel-kelas', [RombelKelasController::class, 'index'])->name('rombel-kelas.index');
    Route::get('/rombel-kelas/{id_kelas}/manage', [RombelKelasController::class, 'manage'])->name('rombel-kelas.manage');
    Route::post('/rombel-kelas/{id_kelas}/manage', [RombelKelasController::class, 'storeManage'])->name('rombel-kelas.storeManage');
    
    // PLOTTING GURU MATA PELAJARAN (Konsep Baru)
    Route::get('/rombel-mata-pelajaran', [RombelMataPelajaranController::class, 'index'])->name('rombel-mata-pelajaran.index');
    Route::get('/rombel-mata-pelajaran/{id_kelas}/manage', [RombelMataPelajaranController::class, 'manage'])->name('rombel-mata-pelajaran.manage');
    Route::post('/rombel-mata-pelajaran/{id_kelas}/manage', [RombelMataPelajaranController::class, 'storeManage'])->name('rombel-mata-pelajaran.storeManage');
    
    // JADWAL PELAJARAN (Konsep Baru)
    Route::get('/rombel-jadwal', [RombelJadwalPelajaranController::class, 'index'])->name('rombel-jadwal.index');
    Route::get('/rombel-jadwal/{id_kelas}/manage', [RombelJadwalPelajaranController::class, 'manage'])->name('rombel-jadwal.manage');
    Route::post('/rombel-jadwal/{id_kelas}/manage', [RombelJadwalPelajaranController::class, 'storeManage'])->name('rombel-jadwal.storeManage');

    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/export-pdf', [PresensiController::class, 'exportPdf'])->name('presensi.export_pdf');
    Route::post('/presensi', [PresensiController::class, 'store'])->name('presensi.store');
    Route::get('/presensi/{presensi}/edit', [PresensiController::class, 'edit'])->name('presensi.edit');
    Route::put('/presensi/{presensi}', [PresensiController::class, 'update'])->name('presensi.update');

    Route::get('/whatsapp', [\App\Http\Controllers\Admin\WhatsappController::class, 'index'])->name('whatsapp.index');

    Route::get('/user', [UserController::class, 'index'])->name('user.index');

});

// ==========================================
// ROLE GURU
// ==========================================
Route::middleware(['auth', 'role:guru'])->prefix('guru')->as('guru.')->group(function () {
    
    // Dashboard Guru
    Route::get('/dashboard', [\App\Http\Controllers\Guru\DashboardController::class, 'index'])->name('dashboard');
    // Pantau Absensi Kelas
    Route::get('/presensi/{id}', [\App\Http\Controllers\Guru\PresensiController::class, 'show'])->name('presensi.show');
    Route::post('/presensi/{id}', [\App\Http\Controllers\Guru\PresensiController::class, 'update'])->name('presensi.update');
    // Jadwal Mengajar Full
    Route::get('/jadwal', [\App\Http\Controllers\Guru\JadwalController::class, 'index'])->name('jadwal.index');
    // Menu Wali Kelas
    Route::get('/wali-kelas', [\App\Http\Controllers\Guru\WaliKelasController::class, 'index'])->name('walikelas.index');
    // Riwayat Presensi (Berdasarkan Tanggal & Jadwal)
    Route::get('/riwayat-absensi', [\App\Http\Controllers\Guru\RiwayatAbsensiController::class, 'index'])->name('riwayat-absensi.index');
    Route::get('/riwayat-absensi/{id_jadwal}', [\App\Http\Controllers\Guru\RiwayatAbsensiController::class, 'show'])->name('riwayat-absensi.show');
    Route::post('/riwayat-absensi/{id_jadwal}', [\App\Http\Controllers\Guru\RiwayatAbsensiController::class, 'update'])->name('riwayat-absensi.update');


});

require __DIR__ . '/auth.php';