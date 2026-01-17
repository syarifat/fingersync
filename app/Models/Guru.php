<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    protected $table = 'guru'; // Pastikan nama tabel tunggal sesuai migrasi

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
