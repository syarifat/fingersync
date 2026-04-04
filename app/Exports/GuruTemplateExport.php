<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GuruTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'NIDN', 
            'NAMA_LENGKAP', 
            'GENDER', 
            'ALAMAT', 
            'USERNAME', 
            'NOHP', 
            'IS_BK', // Diisi 1 jika Guru BK, 0 jika bukan
            'STATUS'
        ];
    }

    public function array(): array
    {
        // 1 Baris data Dummy agar Admin tahu cara mengisinya
        return [
            [
                '1234567890', 
                'Budi Santoso, S.Pd', 
                'Laki-laki', 
                'Jl. Pendidikan No. 1', 
                'budisantoso', 
                '081234567890', 
                '0', // 0 = Bukan BK, 1 = Guru BK
                'Aktif'
            ]
        ];
    }
}