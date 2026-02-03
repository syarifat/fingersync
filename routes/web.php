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
use App\Http\Controllers\Api\DeviceController as ApiDeviceController;


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
    Route::resource('siswa', SiswaController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('ruangan', RuanganController::class);
    Route::resource('guru', AdminGuruController::class);
    Route::resource('jurusan', JurusanController::class);
    Route::resource('mata-pelajaran', MataPelajaranController::class);
    Route::resource('tahun-ajar', TahunAjarController::class);
    Route::resource('device', DeviceController::class);
    Route::post('tahun-ajar/switch', [TahunAjarController::class, 'switch'])->name('tahun-ajar.switch');

    // Rombel
    Route::resource('rombel-kelas', RombelKelasController::class);
    Route::resource('rombel-mata-pelajaran', RombelMataPelajaranController::class);
    Route::resource('rombel-jadwal', RombelJadwalPelajaranController::class);

    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::post('/presensi', [PresensiController::class, 'store'])->name('presensi.store');
});

// Role Guru
Route::middleware(['auth', 'role:guru'])->prefix('guru')->as('guru.')->group(function () {
    Route::get('/dashboard', [GuruGuruController::class, 'index'])->name('dashboard');
});


// =============================================================
//  🔥 FIRE GROUP (API Workaround untuk Vercel)
//  Nanti tinggal pindahkan blok ini ke routes/api.php kalau sudah di hosting
// =============================================================
Route::prefix('fire')->group(function () {
    Route::post('/scan', [ApiDeviceController::class, 'scan']);
    Route::get('/clear-cache', function () {
        Artisan::call('route:clear');
        Artisan::call('config:clear');
        return "Cache Cleared!";
    });
});

require __DIR__ . '/auth.php';
