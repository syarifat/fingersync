<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiswaTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'NIS', 
            'NAMA_LENGKAP', 
            'FINGERPRINT_ID', 
            'ID_JURUSAN', 
            'GENDER', 
            'AGAMA', 
            'ALAMAT', 
            'NOHP_SISWA', 
            'NOHP_ORTU', 
            'EMAIL', 
            'NAMA_AYAH', 
            'NAMA_IBU', 
            'STATUS'
        ];
    }

    public function array(): array
    {
        // Memberikan 1 baris contoh (dummy) agar admin paham formatnya
        return [
            [
                '12345678', 
                'Budi Santoso', 
                '15', 
                '1', // Angka ID Jurusan
                'Laki-laki', 
                'Islam', 
                'Jl. Mawar No 1', 
                '08123456789', 
                '08987654321', 
                'budi@email.com', 
                'Anton', 
                'Siti', 
                'Aktif'
            ]
        ];
    }
}