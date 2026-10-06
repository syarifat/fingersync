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
        Schema::create('device_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('id_device'); // Alat mana yang disuruh (Alat 2 / 3)
            $table->integer('fingerprint_id'); // ID yang harus dipaksa simpan
            $table->enum('action', ['enroll', 'delete'])->default('enroll'); // Perintahnya apa
            $table->enum('status', ['pending', 'done', 'failed'])->default('pending'); // Status tugas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tasks');
    }
};
