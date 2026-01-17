<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    // Tambahkan baris ini
    protected $table = 'jurusan'; 

    protected $fillable = ['nama'];
}