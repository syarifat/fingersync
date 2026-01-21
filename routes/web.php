<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SiswaController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Guru\GuruController as GuruGuruController;
use App\Http\Controllers\Admin\RuanganController;
use App\Http\Controllers\Admin\GuruController as AdminGuruController;
use App\Http\Controllers\Auth\AktivasiGuruController;


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
    // Dashboard Admin (URL: /admin/dashboard)
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Master Data (Otomatis URL: /admin/siswa, /admin/guru, dll)
    Route::resource('siswa', SiswaController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('ruangan', RuanganController::class);
    
    // Menggunakan Alias Controller yang sudah didefinisikan di atas
    Route::resource('guru', AdminGuruController::class);
});

// Role Guru
Route::middleware(['auth', 'role:guru'])->prefix('guru')->as('guru.')->group(function () {
    Route::get('/dashboard', [GuruGuruController::class, 'index'])->name('dashboard');
});
require __DIR__.'/auth.php';
