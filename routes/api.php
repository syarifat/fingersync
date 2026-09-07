<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\DeviceController; 

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// =============================================================
//  JALUR IOT (ESP32)
//  URL: /api/scan
// =============================================================

// 1. Endpoint Absensi (POST & GET, toleran trailing slash/newline/spasi)
Route::match(['get', 'post'], '/scan{any?}', [DeviceController::class, 'scan'])->where('any', '.*');

// Route Sinkronisasi & Pendaftaran Alat (YANG BARU)
Route::post('/register/new', [DeviceController::class, 'registerNewId']);
Route::get('/register/task', [DeviceController::class, 'checkTask']);
Route::post('/register/complete', [DeviceController::class, 'completeTask']);

// 2. Cek Koneksi (GET)
Route::get('/ping', function () {
    return response()->json([
        'status' => 'ONLINE', 
        'message' => 'API Ready', 
        'timestamp' => now()
    ]);
});

// 3. Clear Cache (Utility)
Route::get('/clear-cache', function () {
    Artisan::call('route:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return response()->json(['message' => 'System Cache Cleared']);
});