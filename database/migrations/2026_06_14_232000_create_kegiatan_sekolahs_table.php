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
        Schema::create('kegiatan_sekolah', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->date('tanggal');
            $table->string('tipe')->default('serentak'); // serentak, non-serentak
            $table->time('jam_mulai_datang')->default('06:30:00');
            $table->time('jam_selesai_datang')->default('09:00:00');
            $table->time('jam_mulai_pulang')->default('12:00:00');
            $table->time('jam_selesai_pulang')->default('16:00:00');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('tanggal');
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_sekolah');
    }
};
