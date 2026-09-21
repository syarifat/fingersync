<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;

// 1. Rekap Pagi (Laporan Kehadiran KBM Pagi)
// Berjalan otomatis setiap hari jam 08:00 pagi
Schedule::command('absensi:rekap-pagi')
    ->dailyAt('08:00');

// 2. Rekap Sore (Laporan Harian & Kepulangan)
// Berjalan otomatis setiap hari jam 16:00 sore
Schedule::command('absensi:rekap-sore')
    ->dailyAt('16:00');