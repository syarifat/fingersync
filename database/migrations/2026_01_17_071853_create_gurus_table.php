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
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel users (untuk login)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nidn')->unique();
            $table->string('nama');
            $table->string('gender');
            $table->text('alamat')->nullable();
            $table->string('username')->unique();
            $table->string('password'); // Disimpan juga disini sesuai request
            $table->string('nohp')->nullable();
            $table->boolean('is_bk')->default(false);
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
        Schema::dropIfExists('gurus');
    }
};
