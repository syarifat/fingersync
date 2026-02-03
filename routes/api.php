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

// 1. Endpoint Absensi (POST)
Route::post('/scan', [DeviceController::class, 'scan']);

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