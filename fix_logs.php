<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$logs = App\Models\LogWhatsapp::whereNull('id_siswa')->where('pesan', 'like', '%LAPORAN PRESENSI HARIAN%')->get();
foreach ($logs as $log) {
    $siswa = App\Models\Siswa::where('nohp_ortu', $log->no_wa)->first();
    if ($siswa) {
        $log->id_siswa = $siswa->id;
        $log->save();
        echo "Fixed Log ID: {$log->id}\n";
    }
}
echo "Done!\n";
