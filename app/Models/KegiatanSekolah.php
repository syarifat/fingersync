<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanSekolah extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_sekolah';

    protected $fillable = [
        'nama_kegiatan',
        'tanggal',
        'tipe',
        'jam_mulai_datang',
        'jam_selesai_datang',
        'jam_mulai_pulang',
        'jam_selesai_pulang',
        'keterangan'
    ];
}
