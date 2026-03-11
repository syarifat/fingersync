<?php

namespace App\Imports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Siswa([
            'nis'            => $row['nis'],
            'nama'           => $row['nama_lengkap'], // Sesuai nama header di template
            'fingerprint_id' => $row['fingerprint_id'],
            'id_jurusan'     => $row['id_jurusan'],
            'gender'         => $row['gender'],
            'agama'          => $row['agama'],
            'alamat'         => $row['alamat'],
            'nohp_siswa'     => $row['nohp_siswa'],
            'nohp_ortu'      => $row['nohp_ortu'],
            'email'          => $row['email'],
            'nama_ayah'      => $row['nama_ayah'],
            'nama_ibu'       => $row['nama_ibu'],
            'status'         => $row['status'] ?? 'Aktif',
        ]);
    }

    // STRATEGI A: ATURAN VALIDASI UNTUK EXCEL
    public function rules(): array
    {
        return [
            // Nama tabel DB, header di Excel huruf kecil karena aturan library
            'nis'            => 'required|unique:siswa,nis',
            'nama_lengkap'   => 'required|unique:siswa,nama', // Cek duplikat Nama
            'fingerprint_id' => 'required|numeric|unique:siswa,fingerprint_id', // Cek duplikat Fingerprint
            'id_jurusan'     => 'required|exists:jurusan,id', // Pastikan ID Jurusan ada di DB
            'gender'         => 'required|in:Laki-laki,Perempuan',
        ];
    }

    // Pesan error jika ada baris Excel yang melanggar aturan
    public function customValidationMessages()
    {
        return [
            'nis.unique'            => 'NIS :input sudah ada di database.',
            'nama_lengkap.unique'   => 'Siswa dengan nama :input sudah terdaftar.',
            'fingerprint_id.unique' => 'Fingerprint ID :input sudah dipakai siswa lain.',
            'id_jurusan.exists'     => 'ID Jurusan :input tidak ditemukan di database.',
        ];
    }
}