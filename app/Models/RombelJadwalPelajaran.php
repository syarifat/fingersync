<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RombelJadwalPelajaran extends Model
{
    use HasFactory;

    protected $table = 'rombel_jadwal_pelajaran';

    protected $fillable = [
        'id_rombel_mata_pelajaran',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'id_ruangan'
    ];

    // Relasi ke Parent (Plotting Guru)
    public function rombelMapel() 
    { 
        return $this->belongsTo(RombelMataPelajaran::class, 'id_rombel_mata_pelajaran'); 
    }

    // Relasi ke Ruangan
    public function ruangan() 
    { 
        return $this->belongsTo(Ruangan::class, 'id_ruangan'); 
    }
}