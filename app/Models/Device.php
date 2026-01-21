<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $table = 'device';

    protected $fillable = [
        'id_device', // Serial Number Unik
        'id_ruangan',
        'status' // Online/Offline
    ];

    // Relasi ke Ruangan (Setiap alat pasti nempel di satu ruangan)
    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'id_ruangan');
    }
}