<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class AllTableSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create('id_ID'); // Data Indonesia

        // ==========================================
        // 1. DATA MASTER (INDEPENDENT)
        // ==========================================

        // --- 1.A. Users (Login) ---
        echo "Seeding Users...\n";
        // Admin
        $adminUser = DB::table('users')->insertGetId([
            'nama' => 'Administrator Utama',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Guru 1 (Wali Kelas)
        $guruUser1 = DB::table('users')->insertGetId([
            'nama' => 'Budi Santoso, S.Kom',
            'username' => 'budi.guru',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Guru 2 (Guru BK)
        $guruUser2 = DB::table('users')->insertGetId([
            'nama' => 'Siti Aminah, M.Pd',
            'username' => 'siti.bk',
            'password' => Hash::make('password'),
            'role' => 'guru',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // --- 1.B. Admin Profile ---
        DB::table('admin')->insert([
            'user_id' => $adminUser,
            'nama' => 'Administrator Utama',
            'username' => 'admin',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // --- 1.C. Jurusan ---
        echo "Seeding Jurusan...\n";
        $jurusanRPL = DB::table('jurusan')->insertGetId(['nama' => 'Rekayasa Perangkat Lunak', 'created_at' => now()]);
        $jurusanTKJ = DB::table('jurusan')->insertGetId(['nama' => 'Teknik Komputer Jaringan', 'created_at' => now()]);

        // --- 1.D. Ruangan (Lokasi Fisik) ---
        echo "Seeding Ruangan...\n";
        $labRPL = DB::table('ruangan')->insertGetId([
            'nama_ruangan' => 'Lab Komputer 1 (RPL)',
            'keterangan' => 'Gedung A Lantai 2',
            'created_at' => now()
        ]);
        $kelasTeori = DB::table('ruangan')->insertGetId([
            'nama_ruangan' => 'Ruang Kelas X-A',
            'keterangan' => 'Gedung B Lantai 1',
            'created_at' => now()
        ]);
        $kantin = DB::table('ruangan')->insertGetId([
            'nama_ruangan' => 'Kantin Sekolah',
            'keterangan' => 'Area Belakang',
            'created_at' => now()
        ]);

        // --- 1.E. Mata Pelajaran ---
        echo "Seeding Mapel...\n";
        $mapelWeb = DB::table('mata_pelajaran')->insertGetId(['nama' => 'Pemrograman Web', 'created_at' => now()]);
        $mapelMTK = DB::table('mata_pelajaran')->insertGetId(['nama' => 'Matematika Wajib', 'created_at' => now()]);
        $mapelIndo = DB::table('mata_pelajaran')->insertGetId(['nama' => 'Bahasa Indonesia', 'created_at' => now()]);

        // --- 1.F. Tahun Ajar ---
        echo "Seeding Tahun Ajar...\n";
        $tahunAktif = DB::table('tahun_ajar')->insertGetId([
            'tahun' => '2025/2026',
            'semester' => 'Ganjil',
            'status_aktif' => true,
            'created_at' => now()
        ]);

        // ==========================================
        // 2. DATA PROFIL & RELASI TAHAP 1
        // ==========================================

        // --- 2.A. Guru Profile ---
        echo "Seeding Guru...\n";
        // Guru Mapel & Wali Kelas
        $guru1 = DB::table('guru')->insertGetId([
            'user_id' => $guruUser1,
            'nidn' => '198501012010011001',
            'nama' => 'Budi Santoso, S.Kom',
            'gender' => 'Laki-laki',
            'alamat' => 'Jl. Merdeka No. 45, Tulungagung',
            'username' => 'budi.guru',
            'password' => Hash::make('password'),
            'nohp' => '081234567890',
            'is_bk' => false,
            'status' => 'Aktif',
            'created_at' => now()
        ]);

        // Guru BK
        $guruBK = DB::table('guru')->insertGetId([
            'user_id' => $guruUser2,
            'nidn' => '199005052015012002',
            'nama' => 'Siti Aminah, M.Pd',
            'gender' => 'Perempuan',
            'alamat' => 'Jl. Pahlawan No. 10, Kediri',
            'username' => 'siti.bk',
            'password' => Hash::make('password'),
            'nohp' => '085678901234',
            'is_bk' => true,
            'status' => 'Aktif',
            'created_at' => now()
        ]);

        // --- 2.B. Device (ESP32) ---
        echo "Seeding Device...\n";
        $deviceLab = DB::table('device')->insertGetId([
            'id_device' => 'ESP32-LAB-001', // SN Alat
            'id_ruangan' => $labRPL, // Alat ini ada di Lab RPL
            'status' => 'Online',
            'created_at' => now()
        ]);

        $deviceKantin = DB::table('device')->insertGetId([
            'id_device' => 'ESP32-KANTIN-002',
            'id_ruangan' => $kantin,
            'status' => 'Online',
            'created_at' => now()
        ]);

        // --- 2.C. Siswa (Looping Faker) ---
        echo "Seeding Siswa...\n";
        $siswaIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $gender = $faker->randomElement(['Laki-laki', 'Perempuan']);
            $id = DB::table('siswa')->insertGetId([
                'nis' => '202500' . $i,
                'nama' => $faker->name($gender == 'Laki-laki' ? 'male' : 'female'),
                'fingerprint_id' => $i, // ID 1 sampai 10 di alat
                'id_jurusan' => $jurusanRPL,
                'gender' => $gender,
                'agama' => 'Islam',
                'alamat' => $faker->address,
                'nohp_siswa' => $faker->phoneNumber,
                'nohp_ortu' => $faker->phoneNumber,
                'email' => $faker->email,
                'nama_ayah' => $faker->name('male'),
                'nama_ibu' => $faker->name('female'),
                'image' => null,
                'status' => 'Aktif',
                'created_at' => now(), 'updated_at' => now()
            ]);
            $siswaIds[] = $id;
        }

        // --- 2.D. Kelas ---
        echo "Seeding Kelas...\n";
        $kelasXII = DB::table('kelas')->insertGetId([
            'nama' => 'XII RPL 1',
            'id_jurusan' => $jurusanRPL,
            'created_at' => now()
        ]);

        // ==========================================
        // 3. ROMBEL & JADWAL (KOMPLEKS)
        // ==========================================

        // --- 3.A. Rombel Kelas (Siswa masuk kelas mana) ---
        echo "Seeding Rombel Kelas...\n";
        foreach ($siswaIds as $siswaId) {
            DB::table('rombel_kelas')->insert([
                'id_kelas' => $kelasXII,
                'id_siswa' => $siswaId,
                'id_guru_wali_kelas' => $guru1,
                'id_guru_bk' => $guruBK,
                'id_tahun_ajar' => $tahunAktif,
                'created_at' => now()
            ]);
        }

        // --- 3.B. Rombel Mata Pelajaran (Siapa mengajar apa di kelas mana) ---
        echo "Seeding Rombel Mapel...\n";
        // Pak Budi mengajar Pemrograman Web di XII RPL 1
        $rombelMapelWeb = DB::table('rombel_mata_pelajaran')->insertGetId([
            'id_kelas' => $kelasXII,
            'id_mata_pelajaran' => $mapelWeb,
            'id_guru' => $guru1,
            'id_tahun_ajar' => $tahunAktif,
            'created_at' => now()
        ]);

        // --- 3.C. Rombel Jadwal Pelajaran (Kapan & Dimana) ---
        echo "Seeding Jadwal...\n";
        // Jadwal: Senin, 07:00 - 10:00, Pemrograman Web, di Lab RPL
        $jadwalSenin = DB::table('rombel_jadwal_pelajaran')->insertGetId([
            'id_rombel_mata_pelajaran' => $rombelMapelWeb,
            'hari' => 'Senin',
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '10:00:00',
            'id_ruangan' => $labRPL, // Harus di Lab RPL
            'created_at' => now()
        ]);

        // ==========================================
        // 4. DATA TRANSAKSI (ABSENSI)
        // ==========================================

        echo "Seeding Absensi...\n";
        
        // Skenario 1: Siswa 1 Tapping Tepat Waktu di Alat yang Benar
        DB::table('presensi')->insert([
            'id_siswa' => $siswaIds[0],
            'id_rombel_jadwal_pelajaran' => $jadwalSenin,
            'tanggal' => Carbon::now()->startOfWeek()->format('Y-m-d'), // Hari Senin minggu ini
            'jam_scan' => '06:55:00', // Datang lebih awal
            'id_device' => $deviceLab, // Alatnya benar (ESP32-LAB-001) yang ada di Lab RPL
            'status' => 'Hadir',
            'id_tahun_ajar' => $tahunAktif,
            'created_at' => now()
        ]);

        // Skenario 2: Siswa 2 Terlambat
        DB::table('presensi')->insert([
            'id_siswa' => $siswaIds[1],
            'id_rombel_jadwal_pelajaran' => $jadwalSenin,
            'tanggal' => Carbon::now()->startOfWeek()->format('Y-m-d'),
            'jam_scan' => '07:15:00', // Terlambat 15 menit
            'id_device' => $deviceLab,
            'status' => 'Terlambat',
            'id_tahun_ajar' => $tahunAktif,
            'created_at' => now()
        ]);

        // Skenario 3: (Simulasi Logic Error) Siswa 3 Tapping di Kantin saat Jam Pelajaran
        // (Secara logika sistem akan menolak, tapi ini contoh data tersimpan jika sistem meloloskan/mencatat log penolakan)
        /*
        DB::table('presensi')->insert([
            'id_siswa' => $siswaIds[2],
            'id_rombel_jadwal_pelajaran' => $jadwalSenin,
            'tanggal' => Carbon::now()->startOfWeek()->format('Y-m-d'),
            'jam_scan' => '07:30:00',
            'id_device' => $deviceKantin, // Perangkat SALAH (Kantin)
            'status' => 'Alpa', // Atau status kustom 'Lokasi Salah'
            'id_tahun_ajar' => $tahunAktif,
            'created_at' => now()
        ]);
        */

        echo "Seeding Selesai! Database FingerSync siap digunakan.\n";
    }
}