<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Gunakan locale Indonesia & seed statis biar konsisten
        $faker = Faker::create('id_ID');
        
        // ==========================================
        // 1. BERSIHKAN DATABASE (TRUNCATE)
        // ==========================================
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
        
        echo "🧹 Database bersih. Mulai seeding data realistis...\n";

        // ==========================================
        // 2. DATA MASTER (JURUSAN, TAHUN, RUANGAN & DEVICE)
        // ==========================================
        
        $jurusanId = DB::table('jurusan')->insertGetId([
            'nama' => 'Teknik Komputer dan Jaringan',
            'created_at' => now(), 'updated_at' => now()
        ]);

        $tahunAjarId = DB::table('tahun_ajar')->insertGetId([
            'tahun' => '2025/2026',
            'semester' => 'Ganjil',
            'status_aktif' => 1,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Ruangan + Device (Setiap ruangan punya 1 perangkat)
        $ruangans = ['Lab TKJ 1', 'Lab TKJ 2', 'Lab TKJ 3', 'Lab TKJ 4', 'Ruang 32', 'Ruang 33', 'Ruang 34', 'Ruang 35', 'Ruang 36'];
        $ruanganIds = []; 
        $deviceIds = []; // Mapping RuanganID => DeviceID

        foreach ($ruangans as $index => $namaRuang) {
            // Insert Ruangan
            $rId = DB::table('ruangan')->insertGetId([
                'nama_ruangan' => $namaRuang,
                'keterangan' => str_contains($namaRuang, 'Lab') ? 'Lantai 2 Gedung B' : 'Lantai 3 Gedung A',
                'created_at' => now(), 'updated_at' => now()
            ]);
            $ruanganIds[] = $rId;

            // Insert Device untuk Ruangan ini
            $devId = DB::table('device')->insertGetId([
                'id_device' => 'ESP32-' . strtoupper(Str::random(4)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'id_ruangan' => $rId,
                'status' => $faker->randomElement(['Online', 'Offline']),
                'created_at' => now(), 'updated_at' => now()
            ]);
            $deviceIds[$rId] = $devId;
        }

        // ==========================================
        // 3. MATA PELAJARAN (11 PER JENJANG)
        // ==========================================
        $mapelX = ['Pendidikan Agama', 'PPKn', 'Bahasa Indonesia', 'Matematika', 'Sejarah', 'Bahasa Inggris', 'Seni Budaya', 'PJOK', 'Informatika', 'IPAS', 'Dasar Kejuruan TKJ'];
        $mapelXI = ['Pendidikan Agama', 'PPKn', 'Bahasa Indonesia', 'Matematika', 'Bahasa Inggris', 'PJOK', 'PKK (Kewirausahaan)', 'Adm. Infrastruktur Jaringan', 'Adm. Sistem Jaringan', 'Teknologi Layanan Jaringan', 'Teknologi WAN'];
        $mapelXII = ['Pendidikan Agama', 'PPKn', 'Bahasa Indonesia', 'Matematika', 'Bahasa Inggris', 'PKK Lanjut', 'Keamanan Jaringan', 'Troubleshooting Jaringan', 'Adm. Server', 'Cloud Computing', 'PKL'];

        $allMapel = array_unique(array_merge($mapelX, $mapelXI, $mapelXII));
        $mapelDbIds = [];
        foreach ($allMapel as $m) {
            $mapelDbIds[$m] = DB::table('mata_pelajaran')->insertGetId([
                'nama' => $m, 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // ==========================================
        // 4. USER, ADMIN, GURU
        // ==========================================
        
        // Admin
        $uAdmin = DB::table('users')->insertGetId([
            'nama' => 'Administrator', 'username' => 'admin', 'password' => Hash::make('password'), 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('admin')->insert([
            'user_id' => $uAdmin, 'nama' => 'Super Admin', 'username' => 'admin', 'created_at' => now(), 'updated_at' => now()
        ]);

        // Guru BK (3 Orang)
        $guruBkIds = [];
        for ($i=1; $i<=3; $i++) {
            $gName = $faker->firstName;
            $username = strtolower($gName) . 'bk';
            $uid = DB::table('users')->insertGetId([
                'nama' => $gName . ' ' . $faker->lastName . ', S.Psi', 
                'username' => $username, 'password' => Hash::make('password'), 'role' => 'guru', 'created_at' => now(), 'updated_at' => now()
            ]);
            $guruBkIds[] = DB::table('guru')->insertGetId([
                'user_id' => $uid, 'nidn' => $faker->unique()->numerify('99########'),
                'nama' => DB::table('users')->where('id', $uid)->value('nama'),
                'gender' => $faker->randomElement(['L', 'P']), 'alamat' => $faker->address,
                'username' => $username, 'password' => Hash::make('password'), 'nohp' => $faker->phoneNumber,
                'is_bk' => 1, 'image' => 'default.png', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // Guru Mapel (10 Orang)
        $guruMapelIds = [];
        for ($i=1; $i<=10; $i++) {
            $gName = $faker->firstName;
            $username = strtolower($gName);
            $uid = DB::table('users')->insertGetId([
                'nama' => $gName . ' ' . $faker->lastName . ', S.Kom', 
                'username' => $username, 'password' => Hash::make('password'), 'role' => 'guru', 'created_at' => now(), 'updated_at' => now()
            ]);
            $guruMapelIds[] = DB::table('guru')->insertGetId([
                'user_id' => $uid, 'nidn' => $faker->unique()->numerify('20########'),
                'nama' => DB::table('users')->where('id', $uid)->value('nama'),
                'gender' => $faker->randomElement(['L', 'P']), 'alamat' => $faker->address,
                'username' => $username, 'password' => Hash::make('password'), 'nohp' => $faker->phoneNumber,
                'is_bk' => 0, 'image' => 'default.png', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // ==========================================
        // 5. KELAS, JADWAL, & ROMBEL PELAJARAN
        // ==========================================
        // Kita butuh menyimpan ID jadwal untuk generate presensi nanti
        // Struktur: $jadwalData[Hari][KelasID] = [List Jadwal IDs]
        $jadwalData = []; 
        $hariSekolah = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jamPelajaran = [
            ['07:00:00', '08:30:00'],
            ['08:45:00', '10:15:00'],
            ['10:30:00', '12:00:00'],
            ['13:00:00', '14:30:00']
        ];

        $kelasIds = []; // Simpan ID kelas untuk distribusi siswa
        $levels = ['X', 'XI', 'XII'];
        
        foreach ($levels as $lvl) {
            $targetMapel = ($lvl == 'X') ? $mapelX : (($lvl == 'XI') ? $mapelXI : $mapelXII);
            
            for ($k=1; $k<=3; $k++) {
                $namaKelas = "$lvl TKJ $k";
                $kelasId = DB::table('kelas')->insertGetId([
                    'nama' => $namaKelas, 'id_jurusan' => $jurusanId, 'created_at' => now(), 'updated_at' => now()
                ]);
                $kelasIds[] = $kelasId;

                // Create Rombel Mapel & Jadwal
                // Kita acak 11 mapel ini ke 5 hari kerja
                $shuffledMapel = $targetMapel;
                shuffle($shuffledMapel);
                $mapelIndex = 0;

                foreach ($hariSekolah as $hari) {
                    // Sehari ada 2-3 mapel
                    for ($j=0; $j<count($jamPelajaran); $j++) {
                        if ($mapelIndex >= count($shuffledMapel)) $mapelIndex = 0; // Ulang mapel kalo habis
                        
                        $namaM = $shuffledMapel[$mapelIndex];
                        $mapelIndex++;

                        // 1. Insert Rombel Mata Pelajaran
                        $rombelMapelId = DB::table('rombel_mata_pelajaran')->insertGetId([
                            'id_kelas' => $kelasId,
                            'id_mata_pelajaran' => $mapelDbIds[$namaM],
                            'id_guru' => $faker->randomElement($guruMapelIds),
                            'id_tahun_ajar' => $tahunAjarId,
                            'created_at' => now(), 'updated_at' => now()
                        ]);

                        // 2. Insert Jadwal Pelajaran (PENTING UNTUK PRESENSI)
                        // Pilih ruangan acak untuk pelajaran ini
                        $ruangId = $faker->randomElement($ruanganIds);
                        
                        $jadwalId = DB::table('rombel_jadwal_pelajaran')->insertGetId([
                            'id_rombel_mata_pelajaran' => $rombelMapelId,
                            'hari' => $hari,
                            'jam_mulai' => $jamPelajaran[$j][0],
                            'jam_selesai' => $jamPelajaran[$j][1],
                            'id_ruangan' => $ruangId,
                            'created_at' => now(), 'updated_at' => now()
                        ]);

                        // Simpan data jadwal untuk generate presensi
                        // Format: $allSchedules[] = [id, hari, jam_mulai, id_device, id_kelas]
                        $jadwalData[] = [
                            'id' => $jadwalId,
                            'hari' => $hari,
                            'jam_mulai' => $jamPelajaran[$j][0],
                            'id_device' => $deviceIds[$ruangId], // Ambil device yg nempel di ruangan tsb
                            'id_kelas' => $kelasId
                        ];
                    }
                }
            }
        }

        // ==========================================
        // 6. SISWA (300 ORANG TANPA GELAR)
        // ==========================================
        echo "👨‍🎓 Membuat 300 Siswa & Distribusi Kelas...\n";
        
        $studentsPerClass = []; // [KelasID => [SiswaID, SiswaID...]]

        for ($i=1; $i<=300; $i++) {
            $gender = $faker->randomElement(['L', 'P']);
            // Trik agar nama tidak ada gelar: Gunakan firstName + lastName manual
            $namaSiswa = $faker->firstName($gender == 'L' ? 'male' : 'female') . ' ' . $faker->lastName;
            
            $siswaId = DB::table('siswa')->insertGetId([
                'nis' => $faker->unique()->numerify('21####'),
                'nama' => $namaSiswa,
                'fingerprint_id' => $i,
                'id_jurusan' => $jurusanId,
                'gender' => $gender,
                'agama' => 'Islam',
                'alamat' => $faker->address,
                'nohp_siswa' => $faker->phoneNumber,
                'nohp_ortu' => $faker->phoneNumber,
                'email' => strtolower(str_replace(' ', '', $namaSiswa)) . rand(10,99) . '@siswa.sch.id',
                'nama_ayah' => $faker->firstName('male') . ' ' . $faker->lastName,
                'nama_ibu' => $faker->firstName('female') . ' ' . $faker->lastName,
                'image' => 'default.png',
                'status' => 'Aktif',
                'created_at' => now(), 'updated_at' => now()
            ]);

            // Assign ke Kelas (Distribusi Merata)
            // Rumus: (Index Siswa - 1) % Total Kelas
            $targetKelasId = $kelasIds[($i - 1) % count($kelasIds)];
            
            DB::table('rombel_kelas')->insert([
                'id_kelas' => $targetKelasId,
                'id_siswa' => $siswaId,
                'id_guru_wali_kelas' => $faker->randomElement($guruMapelIds),
                'id_guru_bk' => $faker->randomElement($guruBkIds),
                'id_tahun_ajar' => $tahunAjarId,
                'created_at' => now(), 'updated_at' => now()
            ]);

            $studentsPerClass[$targetKelasId][] = $siswaId;
        }

        // ==========================================
        // 7. GENERATE PRESENSI (30 HARI KEBELAKANG)
        // ==========================================
        echo "📅 Generating Presensi 30 Hari Terakhir (Sabar ya, agak lama)...\n";

        // Ambil periode 30 hari ke belakang dari hari ini
        $period = \Carbon\CarbonPeriod::create(now()->subDays(30), now());
        
        // Batch insert biar cepat (max 500 record per insert)
        $presensiBatch = [];

        foreach ($period as $date) {
            // Skip jika Sabtu atau Minggu
            if ($date->isWeekend()) continue;

            // Mapping nama hari Inggris ke Indonesia
            $hariInggris = $date->format('l');
            $hariIndo = match($hariInggris) {
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', default => ''
            };

            if (empty($hariIndo)) continue;

            // Cari jadwal yang aktif di hari ini
            $jadwalHariIni = array_filter($jadwalData, fn($j) => $j['hari'] == $hariIndo);

            foreach ($jadwalHariIni as $jadwal) {
                // Ambil semua siswa di kelas yang punya jadwal ini
                $siswaDiKelas = $studentsPerClass[$jadwal['id_kelas']] ?? [];

                foreach ($siswaDiKelas as $sid) {
                    // Tentukan Status Kehadiran (Weighted Random)
                    // 85% Hadir, 5% Terlambat, 3% Sakit, 2% Izin, 5% Alpha
                    $rand = rand(1, 100);
                    $status = 'Hadir';
                    $jamScan = $jadwal['jam_mulai']; // Default tepat waktu

                    if ($rand > 85 && $rand <= 90) {
                        $status = 'Terlambat';
                        // Telat 15-45 menit
                        $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(16, 45))->format('H:i:s');
                    } elseif ($rand > 90 && $rand <= 93) {
                        $status = 'Sakit'; $jamScan = '00:00:00';
                    } elseif ($rand > 93 && $rand <= 95) {
                        $status = 'Izin'; $jamScan = '00:00:00';
                    } elseif ($rand > 95) {
                        $status = 'Alpha'; $jamScan = '00:00:00';
                    } else {
                        // Hadir (Datang 0-15 menit sebelum atau max 10 menit setelah)
                        $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(-15, 10))->format('H:i:s');
                    }

                    $presensiBatch[] = [
                        'id_siswa' => $sid,
                        'id_rombel_jadwal_pelajaran' => $jadwal['id'],
                        'tanggal' => $date->format('Y-m-d'),
                        'jam_scan' => $jamScan,
                        'id_device' => $jadwal['id_device'],
                        'status' => $status,
                        'id_tahun_ajar' => $tahunAjarId,
                        'created_at' => $date->format('Y-m-d H:i:s'),
                        'updated_at' => $date->format('Y-m-d H:i:s')
                    ];

                    // Insert jika batch sudah 500 biar memori aman
                    if (count($presensiBatch) >= 500) {
                        DB::table('presensi')->insert($presensiBatch);
                        $presensiBatch = [];
                    }
                }
            }
        }

        // Insert sisa batch
        if (count($presensiBatch) > 0) {
            DB::table('presensi')->insert($presensiBatch);
        }

        echo "✅ SEEDING SELESAI! \n";
        echo "=======================================\n";
        echo "Admin    : admin / password \n";
        $guruSample = DB::table('users')->where('role', 'guru')->first();
        echo "Guru     : " . $guruSample->username . " / password \n";
        echo "=======================================\n";
    }
}