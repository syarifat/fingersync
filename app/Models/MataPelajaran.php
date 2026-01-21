<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';
    protected $fillable = ['nama'];

    // Relasi untuk pengecekan sebelum hapus (nanti dipakai di Rombel)
    public function rombelMataPelajaran()
    {
        return $this->hasMany(RombelMataPelajaran::class, 'id_mata_pelajaran');
    }
}