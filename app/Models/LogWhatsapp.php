<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogWhatsapp extends Model
{
    use HasFactory;

    protected $table = 'log_whatsapp';

    protected $fillable = [
        'id_siswa',
        'no_wa',
        'pesan',
        'status',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'id_siswa');
    }
}
