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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke users
            $table->string('nidn')->unique();
            $table->string('nama');
            $table->string('gender');
            $table->text('alamat');
            $table->string('username')->unique();
            $table->string('password'); // Tetap ada sesuai permintaan Anda
            $table->string('nohp');
            $table->boolean('is_bk')->default(false);
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
        Schema::dropIfExists('gurus');
    }
};
