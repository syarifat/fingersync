<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID'); // Pakai locale Indonesia biar nama realistis

        // 1. NONAKTIFKAN FOREIGN KEY CHECK UNTUK MEMBERSIHKAN DATA LAMA
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables = [
            'users', 'admin', 'guru', 'siswa', 'jurusan', 'kelas', 
            'mata_pelajaran', 'tahun_ajar', 'ruangan', 'device', 
            'rombel_kelas', 'rombel_mata_pelajaran', 'rombel_jadwal_pelajaran', 'presensi'
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "Database cleaned. Starting seeding...\n";

        // ==========================================
        // 2. SETUP DATA MASTER (JURUSAN, TAHUN AJAR, RUANGAN)
        // ==========================================
        
        // Jurusan
        $jurusanId = DB::table('jurusan')->insertGetId([
            'nama' => 'Teknik Komputer dan Jaringan',
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Tahun Ajar
        $tahunAjarId = DB::table('tahun_ajar')->insertGetId([
            'tahun' => '2025/2026',
            'semester' => 'Ganjil',
            'status_aktif' => 1,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Ruangan (TKJ 1-4, Ruang 32-36)
        $ruanganIds = [];
        $namaRuangan = ['Lab TKJ 1', 'Lab TKJ 2', 'Lab TKJ 3', 'Lab TKJ 4', 'Ruang 32', 'Ruang 33', 'Ruang 34', 'Ruang 35', 'Ruang 36'];
        foreach ($namaRuangan as $ruang) {
            $ruanganIds[] = DB::table('ruangan')->insertGetId([
                'nama_ruangan' => $ruang,
                'keterangan' => str_contains($ruang, 'Lab') ? 'Ruang Praktik' : 'Ruang Teori',
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // ==========================================
        // 3. SETUP MATA PELAJARAN (11 PER TINGKAT)
        // ==========================================
        
        // Kita simpan nama mapel dulu, nanti insert ID nya
        $mapelX = [
            'Pendidikan Agama', 'PPKn', 'Bahasa Indonesia', 'Matematika', 'Sejarah', 
            'Bahasa Inggris', 'Seni Budaya', 'PJOK', 'Informatika', 'IPAS', 'Dasar Kejuruan TKJ'
        ];
        $mapelXI = [
            'Pendidikan Agama', 'PPKn', 'Bahasa Indonesia', 'Matematika', 'Bahasa Inggris', 
            'PJOK', 'PKK (Kewirausahaan)', 'Adm. Infrastruktur Jaringan', 'Adm. Sistem Jaringan', 
            'Teknologi Layanan Jaringan', 'Teknologi WAN'
        ];
        $mapelXII = [
            'Pendidikan Agama', 'PPKn', 'Bahasa Indonesia', 'Matematika', 'Bahasa Inggris', 
            'PKK (Kewirausahaan) Lanjut', 'Keamanan Jaringan', 'Troubleshooting Jaringan', 
            'Adm. Server', 'Cloud Computing', 'PKL (Praktek Kerja Lapangan)'
        ];

        // Gabung unik untuk insert ke tabel mata_pelajaran
        $allMapelNames = array_unique(array_merge($mapelX, $mapelXI, $mapelXII));
        $mapelIds = []; // Map Nama => ID
        
        foreach ($allMapelNames as $namaMapel) {
            $id = DB::table('mata_pelajaran')->insertGetId([
                'nama' => $namaMapel,
                'created_at' => now(), 'updated_at' => now()
            ]);
            $mapelIds[$namaMapel] = $id;
        }

        // ==========================================
        // 4. SETUP USER & GURU (10 UMUM + 3 BK)
        // ==========================================

        // Buat 1 Admin
        $userIdAdmin = DB::table('users')->insertGetId([
            'nama' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('admin')->insert([
            'user_id' => $userIdAdmin,
            'nama' => 'Super Admin',
            'username' => 'admin',
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Buat 3 Guru BK
        $guruBkIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $firstName = strtolower($faker->firstName);
            $username = $firstName . 'bk';
            
            $userId = DB::table('users')->insertGetId([
                'nama' => $faker->name,
                'username' => $username,
                'password' => Hash::make('password'),
                'role' => 'guru',
                'created_at' => now(), 'updated_at' => now()
            ]);

            $guruId = DB::table('guru')->insertGetId([
                'user_id' => $userId,
                'nidn' => $faker->unique()->numerify('19##########'),
                'nama' => DB::table('users')->where('id', $userId)->value('nama'),
                'gender' => $faker->randomElement(['L', 'P']),
                'alamat' => $faker->address,
                'username' => $username,
                'password' => Hash::make('password'),
                'nohp' => $faker->phoneNumber,
                'is_bk' => 1, // Guru BK
                'status' => 'Aktif',
                'created_at' => now(), 'updated_at' => now()
            ]);
            $guruBkIds[] = $guruId;
        }

        // Buat 10 Guru Mapel (Umum/Jurusan)
        $guruMapelIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $firstName = strtolower($faker->unique()->firstName); // Username 1 kata
            
            $userId = DB::table('users')->insertGetId([
                'nama' => $faker->name . ', S.Kom', // Gelar asal biar keren
                'username' => $firstName,
                'password' => Hash::make('password'),
                'role' => 'guru',
                'created_at' => now(), 'updated_at' => now()
            ]);

            $guruId = DB::table('guru')->insertGetId([
                'user_id' => $userId,
                'nidn' => $faker->unique()->numerify('20##########'),
                'nama' => DB::table('users')->where('id', $userId)->value('nama'),
                'gender' => $faker->randomElement(['L', 'P']),
                'alamat' => $faker->address,
                'username' => $firstName,
                'password' => Hash::make('password'),
                'nohp' => $faker->phoneNumber,
                'is_bk' => 0, // Guru Biasa
                'status' => 'Aktif',
                'created_at' => now(), 'updated_at' => now()
            ]);
            $guruMapelIds[] = $guruId;
        }

        // ==========================================
        // 5. SETUP KELAS (9 KELAS)
        // ==========================================
        
        $kelasData = []; // Simpan ID kelas berdasarkan tingkat
        $levels = ['X', 'XI', 'XII'];
        
        foreach ($levels as $level) {
            for ($i = 1; $i <= 3; $i++) {
                $namaKelas = "$level TKJ $i";
                $kelasId = DB::table('kelas')->insertGetId([
                    'nama' => $namaKelas,
                    'id_jurusan' => $jurusanId,
                    'created_at' => now(), 'updated_at' => now()
                ]);
                
                $kelasData[] = [
                    'id' => $kelasId,
                    'nama' => $namaKelas,
                    'level' => $level // X, XI, atau XII
                ];

                // --- SETUP ROMBEL MATA PELAJARAN UNTUK KELAS INI ---
                // Tentukan mapel berdasarkan tingkat kelas
                $targetMapel = match($level) {
                    'X' => $mapelX,
                    'XI' => $mapelXI,
                    'XII' => $mapelXII,
                };

                foreach ($targetMapel as $namaM) {
                    // Assign Guru secara random dari 10 guru mapel
                    DB::table('rombel_mata_pelajaran')->insert([
                        'id_kelas' => $kelasId,
                        'id_mata_pelajaran' => $mapelIds[$namaM],
                        'id_guru' => $faker->randomElement($guruMapelIds),
                        'id_tahun_ajar' => $tahunAjarId,
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                }
            }
        }

        // ==========================================
        // 6. SETUP SISWA & ROMBEL KELAS (300 SISWA)
        // ==========================================

        echo "Creating 300 students and assigning to classes...\n";

        $totalSiswa = 300;
        $totalKelas = count($kelasData); // 9
        $siswaPerKelas = ceil($totalSiswa / $totalKelas); // Sekitar 33-34 siswa per kelas
        
        $counterSiswa = 0;
        $kelasIndex = 0;

        for ($i = 1; $i <= $totalSiswa; $i++) {
            $gender = $faker->randomElement(['L', 'P']);
            $namaSiswa = $gender == 'L' ? $faker->name('male') : $faker->name('female');
            
            // Insert Siswa
            $siswaId = DB::table('siswa')->insertGetId([
                'nis' => $faker->unique()->numerify('21####'), // NIS 6 digit
                'nama' => $namaSiswa,
                'fingerprint_id' => $i, // Urut 1-300
                'id_jurusan' => $jurusanId,
                'gender' => $gender,
                'agama' => 'Islam',
                'alamat' => $faker->address,
                'nohp_siswa' => $faker->phoneNumber,
                'nohp_ortu' => $faker->phoneNumber,
                'email' => $faker->userName . '@student.sch.id',
                'nama_ayah' => $faker->name('male'),
                'nama_ibu' => $faker->name('female'),
                'status' => 'Aktif',
                'created_at' => now(), 'updated_at' => now()
            ]);

            // Tentukan Kelas untuk siswa ini (Distribusi merata)
            // Jika kelas saat ini sudah penuh (34 siswa), pindah ke kelas berikutnya
            // (Logika sederhana: modulo)
            $currentKelas = $kelasData[$kelasIndex];

            // Insert Rombel Kelas
            DB::table('rombel_kelas')->insert([
                'id_kelas' => $currentKelas['id'],
                'id_siswa' => $siswaId,
                // Wali kelas diambil random dari guru mapel
                'id_guru_wali_kelas' => $faker->randomElement($guruMapelIds), 
                // Guru BK diambil random dari 3 guru BK
                'id_guru_bk' => $faker->randomElement($guruBkIds),
                'id_tahun_ajar' => $tahunAjarId,
                'created_at' => now(), 'updated_at' => now()
            ]);

            $counterSiswa++;
            
            // Pindah kelas jika sudah rata (300 / 9 = 33 sisa 3)
            if ($counterSiswa % $siswaPerKelas == 0 && $kelasIndex < $totalKelas - 1) {
                $kelasIndex++;
            } else if ($kelasIndex < $totalKelas - 1 && $i > ($siswaPerKelas * ($kelasIndex + 1))) {
                 $kelasIndex++; // Fallback agar sisa siswa masuk ke kelas terakhir
            }
        }

        echo "Seeding completed successfully! \n";
        echo "Admin: admin / password \n";
        echo "Guru Sample: " . DB::table('users')->where('role', 'guru')->first()->username . " / password \n";
    }
}