<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjar extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajar';
    protected $fillable = ['tahun', 'semester', 'status_aktif'];

    // Casting agar status_aktif otomatis jadi boolean (true/false)
    protected $casts = [
        'status_aktif' => 'boolean',
    ];
}