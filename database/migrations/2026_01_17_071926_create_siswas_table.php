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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('nama');
            $table->integer('fingerprint_id')->unique(); // ID 1-127 di sensor
            $table->foreignId('id_jurusan')->constrained('jurusan');
            $table->string('gender');
            $table->string('agama')->nullable();
            $table->text('alamat')->nullable();
            $table->string('nohp_siswa')->nullable();
            $table->string('nohp_ortu')->nullable(); // WA Gateway target
            $table->string('email')->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
