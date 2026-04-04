<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User; // Wajib ditambahkan untuk membuat akun login!
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // 1. Buat User Login terlebih dahulu (Persis seperti di Controller)
        $user = User::create([
            'nama'     => $row['nama_lengkap'],
            'username' => $row['username'], // atau bisa pakai $row['nidn'] jika ingin disamakan
            'password' => null,             // Password Null agar bisa Aktivasi
            'role'     => 'guru',
        ]);

        // 2. Buat Profil Guru dan tautkan dengan ID User yang baru saja dibuat
        return new Guru([
            'user_id'  => $user->id, // Mengambil ID dari proses No 1
            'nidn'     => $row['nidn'],
            'nama'     => $row['nama_lengkap'],
            'gender'   => $row['gender'],
            'alamat'   => $row['alamat'],
            'username' => $row['username'],
            'nohp'     => $row['nohp'],
            'is_bk'    => $row['is_bk'] ?? 0,
            'status'   => $row['status'] ?? 'Aktif',
            'password' => null, 
        ]);
    }

    public function rules(): array
    {
        return [
            'nidn'         => 'required|numeric|unique:guru,nidn',
            'nama_lengkap' => 'required|string|max:255',
            // PENTING: Cek keunikan username di tabel users dan guru
            'username'     => 'required|unique:users,username|unique:guru,username',
            'gender'       => 'required|in:Laki-laki,Perempuan',
            'nohp'         => 'required|numeric',
            'is_bk'        => 'required|in:0,1',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nidn.unique'     => 'NIDN :input sudah terdaftar di database.',
            'username.unique' => 'Username :input sudah dipakai oleh pengguna lain.',
            'is_bk.in'        => 'Kolom IS_BK hanya boleh diisi angka 0 atau 1.',
            'gender.in'       => 'Gender harus diisi Laki-laki atau Perempuan.',
            'nohp.numeric'    => 'Nomor HP harus berupa angka.',
        ];
    }
}