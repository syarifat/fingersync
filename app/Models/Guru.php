<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';
    
    // Tambahkan semua kolom yang ingin bisa diisi lewat create()
    protected $fillable = [
        'user_id', 
        'nidn', 
        'nama', 
        'gender', 
        'alamat', 
        'username', // <--- Pastikan ini ada!
        'password', 
        'nohp', 
        'is_bk', 
        'image', 
        'status'
    ];

    // Relasi ke User (Akun Login)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}