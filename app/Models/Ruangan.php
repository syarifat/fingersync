<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangan';
    protected $fillable = ['nama_ruangan', 'keterangan'];

    // Relasi ke Device (Satu ruangan bisa punya banyak device)
    public function devices()
    {
        return $this->hasMany(Device::class, 'id_ruangan');
    }
}