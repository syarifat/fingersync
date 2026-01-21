<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            // Jadwal yang sedang berlangsung saat tap
            $table->foreignId('id_rombel_jadwal_pelajaran')->constrained('rombel_jadwal_pelajaran');
            $table->date('tanggal');
            $table->time('jam_scan');
            // Alat mana yang menangkap data
            $table->foreignId('id_device')->constrained('device'); 
            $table->string('status'); // Hadir, Terlambat, Alpa, Izin, Sakit
            $table->foreignId('id_tahun_ajar')->constrained('tahun_ajar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
