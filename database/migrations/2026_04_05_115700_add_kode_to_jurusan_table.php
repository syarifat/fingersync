<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jurusan', function (Blueprint $table) {
            // Menambah kolom kode setelah id
            $table->string('kode', 20)->unique()->after('id'); 
        });
    }

    public function down()
    {
        Schema::table('jurusan', function (Blueprint $table) {
            $table->dropColumn('kode');
        });
    }
};