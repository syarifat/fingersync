<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AllTableSeeder extends Seeder
{
    public function run(): void
    {
        // --- 1. SEED DATA USER & PROFIL (ADMIN) ---
        $adminUserId = DB::table('users')->insertGetId([
            'nama' => 'Administrator Utama',
            'username' => 'admin_pusat',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'created_at' => now(),
        ]);

        DB::table('admin')->insert([
            'user_id' => $adminUserId,
            'nama' => 'Administrator Utama',
            'username' => 'admin_pusat',
            'created_at' => now(),
        ]);

        // --- 2. SEED DATA USER & PROFIL (GURU) ---
        $guruUserId = DB::table('users')->insertGetId([
            'nama' => 'Budi Santoso, S.Kom',
            'username' => 'budi_guru',
            'password' => Hash::make('password123'),
            'role' => 'guru',
            'created_at' => now(),
        ]);

        DB::table('guru')->insert([
            'id' => 1,
            'user_id' => $guruUserId,
            'nidn' => '12345678',
            'nama' => 'Budi Santoso, S.Kom',
            'gender' => 'Laki-laki',
            'alamat' => 'Jl. Teknologi No. 45, Surabaya',
            'username' => 'budi_guru',
            'password' => Hash::make('password123'),
            'nohp' => '08123456789',
            'is_bk' => false,
            'image' => 'default_guru.jpg',
            'status' => 'Aktif',
            'created_at' => now(),
        ]);

        // --- 3. DATA MASTER AKADEMIK & IOT ---
        DB::table('jurusan')->insert([
            ['id' => 1, 'nama' => 'Rekayasa Perangkat Lunak'],
            ['id' => 2, 'nama' => 'Teknik Komputer Jaringan'],
        ]);

        DB::table('mata_pelajaran')->insert([
            ['id' => 1, 'nama' => 'Pemrograman Berorientasi Objek'],
            ['id' => 2, 'nama' => 'Sistem Komputer'],
        ]);

        DB::table('tahun_ajar')->insert([
            ['id' => 1, 'tahun' => '2025/2026', 'semester' => 'Ganjil', 'status_aktif' => true],
        ]);

        DB::table('device')->insert([
            ['id' => 1, 'id_device' => 'ESP32-DEV-001', 'lokasi' => 'Gerbang Utama', 'status' => 'Online'],
        ]);

        // --- 4. DATA SISWA & KELAS ---
        DB::table('siswa')->insert([
            'id' => 1,
            'nis' => '20240001',
            'nama' => 'Rizky Pratama',
            'fingerprint_id' => 101, // Referensi ID di alat ESP32
            'id_jurusan' => 1,
            'gender' => 'Laki-laki',
            'agama' => 'Islam',
            'alamat' => 'Jl. Pelajar No. 1, Surabaya',
            'nohp_siswa' => '085711223344',
            'nohp_ortu' => '081199887766', // Untuk integrasi WhatsApp
            'email' => 'rizky@school.id',
            'nama_ayah' => 'Hendrawan',
            'nama_ibu' => 'Siti Aminah',
            'image' => 'student_01.jpg',
            'status' => 'Aktif',
            'created_at' => now(),
        ]);

        DB::table('kelas')->insert([
            ['id' => 1, 'nama' => 'XII RPL 1', 'id_jurusan' => 1],
        ]);

        // --- 5. RELASI PEMETAAN (ROMBEL) ---
        DB::table('rombel_kelas')->insert([
            'id_kelas' => 1,
            'id_siswa' => 1,
            'id_guru_wali_kelas' => 1,
            'id_guru_bk' => 1,
            'id_tahun_ajar' => 1,
        ]);

        DB::table('rombel_mata_pelajaran')->insert([
            'id' => 1,
            'id_kelas' => 1,
            'id_mata_pelajaran' => 1,
            'id_guru' => 1,
            'id_tahun_ajar' => 1,
        ]);

        DB::table('rombel_jadwal_pelajaran')->insert([
            'id' => 1,
            'id_rombel_mata_pelajaran' => 1,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '09:00:00',
            'id_device' => 1,
        ]);

        // --- 6. DATA OPERASIONAL (PRESENSI) ---
        DB::table('presensi')->insert([
            'id_siswa' => 1,
            'id_rombel_jadwal_pelajaran' => 1,
            'tanggal' => '2026-01-17',
            'jam_scan' => '06:45:10',
            'id_device' => 1,
            'status' => 'Hadir', // Sesuai skenario normal
            'id_tahun_ajar' => 1,
            'created_at' => now(),
        ]);
    }
}