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
        Schema::create('rombel_mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kelas')->constrained('kelas');
            $table->foreignId('id_mata_pelajaran')->constrained('mata_pelajaran');
            $table->foreignId('id_guru')->constrained('guru');
            $table->foreignId('id_tahun_ajar')->constrained('tahun_ajar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rombel_mata_pelajarans');
    }
};
