<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RombelKelas extends Model
{
    use HasFactory;

    // Kita tetap tembak tabel 'rombel' di database (sesuai data yang Anda kirim)
    // Tapi nama Model di Laravel kita perjelas jadi RombelKelas
    protected $table = 'rombel_kelas'; 

    protected $fillable = [
        'id_kelas',
        'id_siswa',
        'id_guru_wali_kelas',
        'id_guru_bk',
        'id_tahun_ajar'
    ];

    public function kelas() { return $this->belongsTo(Kelas::class, 'id_kelas'); }
    public function siswa() { return $this->belongsTo(Siswa::class, 'id_siswa'); }
    public function waliKelas() { return $this->belongsTo(Guru::class, 'id_guru_wali_kelas'); }
    public function guruBk() { return $this->belongsTo(Guru::class, 'id_guru_bk'); }
    public function tahunAjar() { return $this->belongsTo(TahunAjar::class, 'id_tahun_ajar'); }
}