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
            $table->integer('fingerprint_id')->unique(); // ID Biometrik
            $table->foreignId('id_jurusan')->constrained('jurusan');
            $table->string('gender');
            $table->string('agama');
            $table->text('alamat');
            $table->string('nohp_siswa');
            $table->string('nohp_ortu'); // Untuk WhatsApp Gateway
            $table->string('email')->nullable();
            $table->string('nama_ayah');
            $table->string('nama_ibu');
            $table->string('image')->nullable();
            $table->string('status');
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
