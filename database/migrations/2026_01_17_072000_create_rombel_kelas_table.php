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
        Schema::create('rombel_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kelas')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('id_siswa')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('id_guru_wali_kelas')->constrained('guru');
            $table->foreignId('id_guru_bk')->constrained('guru');
            $table->foreignId('id_tahun_ajar')->constrained('tahun_ajar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rombel_kelas');
    }
};
