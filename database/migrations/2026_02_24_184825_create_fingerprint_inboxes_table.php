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
        Schema::create('fingerprint_inboxes', function (Blueprint $table) {
            $table->id();
            $table->string('id_device'); // Alat mana yang merekam pertama kali
            $table->integer('fingerprint_id'); // ID jari yang baru terdaftar
            $table->enum('status', ['pending', 'assigned'])->default('pending'); // Menunggu diisi nama atau sudah
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fingerprint_inboxes');
    }
};
