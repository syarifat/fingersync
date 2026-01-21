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
        Schema::create('rombel_jadwal_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_rombel_mata_pelajaran')->constrained('rombel_mata_pelajaran')->onDelete('cascade');
            $table->string('hari'); // Senin, Selasa, dst
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            // KUNCI: Jadwal ini berlangsung di ruangan mana?
            $table->foreignId('id_ruangan')->constrained('ruangan'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rombel_jadwal_pelajarans');
    }
};
