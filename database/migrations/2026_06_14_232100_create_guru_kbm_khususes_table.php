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
        Schema::create('guru_kbm_khusus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_rombel_jadwal_pelajaran')->constrained('rombel_jadwal_pelajaran')->onDelete('cascade');
            $table->date('tanggal');
            $table->string('status'); // izin, absen, diganti
            $table->foreignId('id_guru_pengganti')->nullable()->constrained('guru')->onDelete('set null');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tanggal', 'id_rombel_jadwal_pelajaran']);
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guru_kbm_khusus');
    }
};
