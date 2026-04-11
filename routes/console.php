<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// 1. Cek Anomali (Siswa bolos/telat > 1 jam)
// Berjalan setiap 30 menit, hanya di jam sekolah (07:00 - 15:00)
Schedule::command('absensi:cek-anomali')
    ->everyThirtyMinutes()
    ->between('07:00', '15:00');

// 2. Rekap Sore (Laporan harian ke Orang Tua)
// Berjalan otomatis setiap hari tepat jam 16:00 (4 sore)
Schedule::command('absensi:rekap-sore')
    ->dailyAt('16:00');