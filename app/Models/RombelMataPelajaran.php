<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RombelMataPelajaran extends Model
{
    use HasFactory;

    // Sesuai nama tabel di database Anda
    protected $table = 'rombel_mata_pelajaran';

    protected $fillable = [
        'id_tahun_ajar',
        'id_kelas',
        'id_mata_pelajaran', // <--- Sesuai struktur tabel
        'id_guru'
    ];

    public function tahunAjar() { return $this->belongsTo(TahunAjar::class, 'id_tahun_ajar'); }
    public function kelas() { return $this->belongsTo(Kelas::class, 'id_kelas'); }
    
    // Relasi ke Model MataPelajaran
    public function mataPelajaran() { return $this->belongsTo(MataPelajaran::class, 'id_mata_pelajaran'); }
    
    public function guru() { return $this->belongsTo(Guru::class, 'id_guru'); }
}