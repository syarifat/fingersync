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
        Schema::table('presensi', function (Blueprint $table) {
            $table->foreignId('id_rombel_jadwal_pelajaran')->nullable()->change();
            $table->foreignId('id_kegiatan_sekolah')->nullable()->constrained('kegiatan_sekolah')->onDelete('set null');
            $table->string('tipe_scan_kegiatan')->nullable(); // datang, pulang
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presensi', function (Blueprint $table) {
            $table->foreignId('id_rombel_jadwal_pelajaran')->nullable(false)->change();
            $table->dropForeign(['id_kegiatan_sekolah']);
            $table->dropColumn(['id_kegiatan_sekolah', 'tipe_scan_kegiatan']);
        });
    }
};
