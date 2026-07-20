<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$date = '2026-07-20';
echo "Checking presensi records for date: {$date}\n";

$kbmCount = \App\Models\Presensi::where('tanggal', $date)
    ->where(function($q) {
        $q->whereNull('tipe_scan')->orWhere('tipe_scan', '!=', 'pulang');
    })
    ->count();

$pulangCount = \App\Models\Presensi::where('tanggal', $date)
    ->where('tipe_scan', 'pulang')
    ->count();

$totalCount = \App\Models\Presensi::where('tanggal', $date)->count();

echo "KBM count: {$kbmCount}\n";
echo "Pulang count: {$pulangCount}\n";
echo "Total count: {$totalCount}\n";

if ($totalCount > 0) {
    echo "\nSample records:\n";
    $samples = \App\Models\Presensi::where('tanggal', $date)->with('siswa')->limit(5)->get();
    foreach ($samples as $s) {
        echo "Student: {$s->siswa->nama} | Tipe: {$s->tipe_scan} | Status: {$s->status}\n";
    }
}
