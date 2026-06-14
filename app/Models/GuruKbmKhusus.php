<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuruKbmKhusus extends Model
{
    use HasFactory;

    protected $table = 'guru_kbm_khusus';

    protected $fillable = [
        'id_rombel_jadwal_pelajaran',
        'tanggal',
        'status',
        'id_guru_pengganti',
        'keterangan'
    ];

    public function rombelJadwalPelajaran()
    {
        return $this->belongsTo(RombelJadwalPelajaran::class, 'id_rombel_jadwal_pelajaran');
    }

    public function guruPengganti()
    {
        return $this->belongsTo(Guru::class, 'id_guru_pengganti');
    }
}
