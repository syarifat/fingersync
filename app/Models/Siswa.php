<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa'; // Nama tabel tunggal

    protected $fillable = [
        'nis', 'nama', 'fingerprint_id', 'id_jurusan', 'gender', 
        'agama', 'alamat', 'nohp_siswa', 'nohp_ortu', 'email', 
        'nama_ayah', 'nama_ibu', 'image', 'status'
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan');
    }
}