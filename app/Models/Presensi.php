<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    use HasFactory;

    // Penting: Definisi nama tabel karena tidak mengikuti standar plural bahasa Inggris
    protected $table = 'presensi';
    
    // Izinkan semua kolom diisi (kecuali id dan timestamps)
    protected $guarded = ['id'];

    // --- RELASI (JOIN) ---

    // Relasi ke tabel Siswa
    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }

    // Relasi ke Rombel Jadwal
    public function jadwal()
    {
        return $this->belongsTo(RombelJadwalPelajaran::class, 'id_rombel_jadwal_pelajaran');
    }

    // Relasi ke Device (Alat IoT)
    public function device()
    {
        return $this->belongsTo(Device::class, 'id_device');
    }

    // Relasi ke Tahun Ajar
    public function tahunAjar()
    {
        return $this->belongsTo(TahunAjar::class, 'id_tahun_ajar');
    }
}