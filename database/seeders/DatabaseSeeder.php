<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // KONFIGURASI SEEDER TANGGAL PRESENSI
        // Ubah tanggal di bawah ini sesuai keinginan Anda!
        // ==========================================
        $tglAwalPresensi = '2026-05-04'; // Format: YYYY-MM-DD
        $tglAkhirPresensi = '2026-06-03'; // Format: YYYY-MM-DD
        $faker = Faker::create('id_ID');

        // ==========================================
        // 1. BERSIHKAN DATABASE (TRUNCATE)
        // ==========================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables = [
            'users', 'admin', 'guru', 'siswa', 'jurusan', 'kelas', 
            'mata_pelajaran', 'tahun_ajar', 'ruangan', 'device', 
            'rombel_kelas', 'rombel_mata_pelajaran', 'rombel_jadwal_pelajaran', 'presensi',
            'fingerprint_inboxes', 'device_tasks'
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "🧹 Database bersih. Mulai seeding data...\n";

        // ==========================================
        // 2. DATA MASTER (JURUSAN & TAHUN AJAR)
        // ==========================================
        $jurusanId = DB::table('jurusan')->insertGetId([
            'kode' => 'TKJ',
            'nama' => 'Teknik Komputer dan Jaringan', // 1 Jurusan saja
            'created_at' => now(), 'updated_at' => now()
        ]);

        $tahunAjarId = DB::table('tahun_ajar')->insertGetId([
            'tahun' => '2025/2026',
            'semester' => 'Genap',
            'status_aktif' => 1,
            'created_at' => now(), 'updated_at' => now()
        ]);

        // ==========================================
        // 3. RUANGAN & DEVICE (3 Ruangan untuk 3 Kelas)
        // ==========================================
        $ruangans = ['Lab TKJ 1', 'Lab TKJ 2', 'Lab TKJ 3'];
        $ruanganIds = []; 
        $deviceIds = []; 

        foreach ($ruangans as $index => $namaRuang) {
            $rId = DB::table('ruangan')->insertGetId([
                'nama_ruangan' => $namaRuang,
                'keterangan' => 'Lantai 1 Gedung Praktik',
                'created_at' => now(), 'updated_at' => now()
            ]);
            $ruanganIds[] = $rId;

            $devId = DB::table('device')->insertGetId([
                'id_device' => 'ESP32-TKJ-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'id_ruangan' => $rId,
                'status' => 'Online',
                'created_at' => now(), 'updated_at' => now()
            ]);
            $deviceIds[$rId] = $devId;
        }

        // ==========================================
        // 4. MATA PELAJARAN
        // ==========================================
        $mapels = [
            'Bimbingan Konseling', // Mapel khusus BK
            'Pendidikan Agama', 'PPKn', 'Bahasa Indonesia', 'Matematika', 
            'Bahasa Inggris', 'Sejarah', 'Dasar Kejuruan TKJ', 
            'Seni Budaya', 'PJOK', 'Informatika'
        ];
        $mapelDbIds = [];
        foreach ($mapels as $m) {
            $mapelDbIds[$m] = DB::table('mata_pelajaran')->insertGetId([
                'nama' => $m, 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // ==========================================
        // 5. USER, ADMIN, GURU
        // ==========================================
        // Admin
        $uAdmin = DB::table('users')->insertGetId([
            'nama' => 'Administrator', 'username' => 'admin', 'password' => Hash::make('password'), 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()
        ]);
        DB::table('admin')->insert([
            'user_id' => $uAdmin, 'nama' => 'Super Admin', 'username' => 'admin', 'created_at' => now(), 'updated_at' => now()
        ]);

        // Guru BK (2 Orang. Maksimal mengampu 2 kelas)
        $guruBkIds = [];
        for ($i=1; $i<=2; $i++) {
            $gName = $faker->firstName;
            $username = 'gurubk' . $i;
            $uid = DB::table('users')->insertGetId([
                'nama' => $gName . ' ' . $faker->lastName . ', S.Psi', 
                'username' => $username, 'password' => Hash::make('password'), 'role' => 'guru', 'created_at' => now(), 'updated_at' => now()
            ]);
            $guruBkIds[] = DB::table('guru')->insertGetId([
                'user_id' => $uid, 'nidn' => $faker->unique()->numerify('99########'),
                'nama' => DB::table('users')->where('id', $uid)->value('nama'),
                'gender' => $faker->randomElement(['Laki-laki', 'Perempuan']), 'alamat' => $faker->address,
                'username' => $username, 'password' => Hash::make('password'), 'nohp' => $faker->phoneNumber,
                'is_bk' => 1, 'image' => 'default.png', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // Guru Mapel & Wali Kelas (10 Orang)
        $guruMapelIds = [];
        for ($i=1; $i<=10; $i++) {
            $gName = $faker->firstName;
            $username = 'guru' . $i;
            $uid = DB::table('users')->insertGetId([
                'nama' => $gName . ' ' . $faker->lastName . ', S.Pd', 
                'username' => $username, 'password' => Hash::make('password'), 'role' => 'guru', 'created_at' => now(), 'updated_at' => now()
            ]);
            $guruMapelIds[] = DB::table('guru')->insertGetId([
                'user_id' => $uid, 'nidn' => $faker->unique()->numerify('20########'),
                'nama' => DB::table('users')->where('id', $uid)->value('nama'),
                'gender' => $faker->randomElement(['Laki-laki', 'Perempuan']), 'alamat' => $faker->address,
                'username' => $username, 'password' => Hash::make('password'), 'nohp' => $faker->phoneNumber,
                'is_bk' => 0, 'image' => 'default.png', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()
            ]);
        }
        // 3 Guru pertama kita jadikan Wali Kelas
        $waliKelasIds = array_slice($guruMapelIds, 0, 3);

        // ==========================================
        // 6. KELAS, SISWA & ROMBEL KELAS
        // ==========================================
        echo "🏢 Membuat 3 Kelas (X TKJ 1, X TKJ 2, X TKJ 3) & 30 Siswa per Kelas...\n";
        $kelasNames = ['X TKJ 1', 'X TKJ 2', 'X TKJ 3'];
        $kelasData = [];
        $siswaProfiles = []; 
        $fingerprintCounter = 1;

        // Buat kelas terlebih dahulu
        foreach ($kelasNames as $index => $namaKelas) {
            $kelasId = DB::table('kelas')->insertGetId([
                'nama' => $namaKelas, 'id_jurusan' => $jurusanId, 'created_at' => now(), 'updated_at' => now()
            ]);

            // Guru BK: Index 0 dan 1 dapat BK 1. Index 2 dapat BK 2. (Maksimal 2 kelas per Guru BK)
            $guruBkAssigned = ($index < 2) ? $guruBkIds[0] : $guruBkIds[1];
            // Setiap kelas punya wali kelas beda
            $waliKelasAssigned = $waliKelasIds[$index];

            $kelasData[] = [
                'id' => $kelasId,
                'nama' => $namaKelas,
                'guru_bk' => $guruBkAssigned,
                'wali_kelas' => $waliKelasAssigned,
                'ruangan_id' => $ruanganIds[$index],
            ];
        }

        // Setup Profile Siswa di masing-masing kelas secara terpisah: 5 Teladan, 5 Bermasalah, 20 Biasa
        $classProfiles = [];
        for ($i = 0; $i < 3; $i++) {
            $types = array_merge(
                array_fill(0, 5, 'teladan'),
                array_fill(0, 5, 'bermasalah'),
                array_fill(0, 20, 'biasa')
            );
            shuffle($types);
            $classProfiles[$i] = $types;
        }

        // Generate siswa secara Round-Robin agar fingerprint_id & siswa_id 1 s.d 10 terbagi rata ke 3 kelas
        for ($s = 0; $s < 30; $s++) {
            foreach ($kelasData as $index => $k) {
                $gender = $faker->randomElement(['Laki-laki', 'Perempuan']);
                $namaSiswaAsli = $faker->firstName($gender == 'Laki-laki' ? 'male' : 'female') . ' ' . $faker->lastName;
                $profileType = $classProfiles[$index][$s];

                $siswaId = DB::table('siswa')->insertGetId([
                    'nis' => '26' . str_pad($fingerprintCounter, 4, '0', STR_PAD_LEFT),
                    'nama' => $namaSiswaAsli,
                    'fingerprint_id' => $fingerprintCounter,
                    'id_jurusan' => $jurusanId,
                    'gender' => $gender,
                    'agama' => 'Islam',
                    'alamat' => $faker->address,
                    'nohp_siswa' => $faker->phoneNumber,
                    'nohp_ortu' => $faker->phoneNumber,
                    'email' => strtolower(str_replace(' ', '', $namaSiswaAsli)) . $fingerprintCounter . '@siswa.sch.id',
                    'image' => 'default.png',
                    'status' => 'Aktif',
                    'created_at' => now(), 'updated_at' => now()
                ]);

                $siswaProfiles[$siswaId] = $profileType;

                // Masukkan siswa ke Rombel Kelas
                DB::table('rombel_kelas')->insert([
                    'id_kelas' => $k['id'],
                    'id_siswa' => $siswaId,
                    'id_guru_wali_kelas' => $k['wali_kelas'],
                    'id_guru_bk' => $k['guru_bk'],
                    'id_tahun_ajar' => $tahunAjarId,
                    'created_at' => now(), 'updated_at' => now()
                ]);

                $fingerprintCounter++;
            }
        }

        // ==========================================
        // 7. JADWAL PELAJARAN (SENIN-JUMAT)
        // ==========================================
        echo "📅 Menyusun Jadwal Pelajaran (Mapel BK diajar oleh Guru BK)...\n";
        $hariSekolah = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $jamPelajaran = [
            ['07:00:00', '08:30:00'],
            ['08:45:00', '10:15:00'],
            ['10:30:00', '12:00:00']
        ]; // 3 Sesi per hari

        $jadwalData = []; 

        foreach ($kelasData as $k) {
            $shuffledMapels = array_diff($mapels, ['Bimbingan Konseling']);
            shuffle($shuffledMapels);
            $mapelIndex = 0;

            // 7.a. Rombel Mapel khusus Bimbingan Konseling (Diajar oleh Guru BK)
            $rombelBkId = DB::table('rombel_mata_pelajaran')->insertGetId([
                'id_kelas' => $k['id'],
                'id_mata_pelajaran' => $mapelDbIds['Bimbingan Konseling'],
                'id_guru' => $k['guru_bk'], // Guru BK sesuai pembagian kelas
                'id_tahun_ajar' => $tahunAjarId,
                'created_at' => now(), 'updated_at' => now()
            ]);

            // Jadwalkan BK di hari Jumat, Sesi ke-3
            $jadwalIdBk = DB::table('rombel_jadwal_pelajaran')->insertGetId([
                'id_rombel_mata_pelajaran' => $rombelBkId,
                'hari' => 'Jumat',
                'jam_mulai' => $jamPelajaran[2][0],
                'jam_selesai' => $jamPelajaran[2][1],
                'id_ruangan' => $k['ruangan_id'], 
                'created_at' => now(), 'updated_at' => now()
            ]);

            $jadwalData[] = [
                'id' => $jadwalIdBk, 'hari' => 'Jumat', 'jam_mulai' => $jamPelajaran[2][0],
                'id_device' => $deviceIds[$k['ruangan_id']], 'id_kelas' => $k['id']
            ];

            // 7.b. Jadwalkan sisa pelajaran
            foreach ($hariSekolah as $hari) {
                for ($j=0; $j<count($jamPelajaran); $j++) {
                    // Skip jika ini jadwalnya BK (Jumat Sesi 3)
                    if ($hari == 'Jumat' && $j == 2) continue;

                    if ($mapelIndex >= count($shuffledMapels)) $mapelIndex = 0;
                    $mapelNama = $shuffledMapels[$mapelIndex];
                    $mapelIndex++;

                    $rombelMapelId = DB::table('rombel_mata_pelajaran')->insertGetId([
                        'id_kelas' => $k['id'],
                        'id_mata_pelajaran' => $mapelDbIds[$mapelNama],
                        'id_guru' => $faker->randomElement($guruMapelIds),
                        'id_tahun_ajar' => $tahunAjarId,
                        'created_at' => now(), 'updated_at' => now()
                    ]);

                    $jadwalId = DB::table('rombel_jadwal_pelajaran')->insertGetId([
                        'id_rombel_mata_pelajaran' => $rombelMapelId,
                        'hari' => $hari,
                        'jam_mulai' => $jamPelajaran[$j][0],
                        'jam_selesai' => $jamPelajaran[$j][1],
                        'id_ruangan' => $k['ruangan_id'],
                        'created_at' => now(), 'updated_at' => now()
                    ]);

                    $jadwalData[] = [
                        'id' => $jadwalId, 'hari' => $hari, 'jam_mulai' => $jamPelajaran[$j][0],
                        'id_device' => $deviceIds[$k['ruangan_id']], 'id_kelas' => $k['id']
                    ];
                }
            }
        }

        // ==========================================
        // 8. GENERATE PRESENSI
        // ==========================================
        echo "⏰ Generating Presensi ({$tglAwalPresensi} sampai {$tglAkhirPresensi})...\n";
        $startDate = Carbon::parse($tglAwalPresensi);
        $endDate = Carbon::parse($tglAkhirPresensi);
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        
        $presensiBatch = [];

        foreach ($period as $date) {
            if ($date->isWeekend()) continue; // Senin sampai Jumat saja

            $hariIndo = match($date->format('l')) {
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', default => ''
            };

            // Ambil jadwal hari ini untuk semua kelas
            $jadwalHariIni = array_filter($jadwalData, fn($j) => $j['hari'] == $hariIndo);

            foreach ($jadwalHariIni as $jadwal) {
                // Ambil semua murid di kelas ini
                $siswaIdsDiKelas = DB::table('rombel_kelas')
                    ->where('id_kelas', $jadwal['id_kelas'])
                    ->where('id_tahun_ajar', $tahunAjarId)
                    ->pluck('id_siswa');

                foreach ($siswaIdsDiKelas as $sid) {
                    $profileType = $siswaProfiles[$sid];
                    $status = 'Hadir';
                    $jamScan = $jadwal['jam_mulai'];

                    $rand = rand(1, 100);

                    // Logika profil presensi realistis
                    if ($profileType == 'teladan') {
                        // 100% Hadir Tepat waktu atau lebih awal
                        $status = 'Hadir';
                        $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(-30, -5))->format('H:i:s');
                    } 
                    else if ($profileType == 'bermasalah') {
                        // 30% Terlambat Parah, 40% Alpha, 20% Izin, 10% Hadir telat dikit
                        if ($rand <= 30) {
                            $status = 'Terlambat';
                            $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(46, 120))->format('H:i:s');
                        } elseif ($rand > 30 && $rand <= 70) {
                            $status = 'Alpha'; $jamScan = '00:00:00';
                        } elseif ($rand > 70 && $rand <= 90) {
                            $status = 'Izin'; $jamScan = '00:00:00';
                        } else {
                            $status = 'Terlambat';
                            $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(16, 45))->format('H:i:s');
                        }
                    } 
                    else { // Biasa saja
                        // 85% Hadir tepat waktu, 10% Terlambat, 3% Sakit, 2% Izin
                        if ($rand <= 85) {
                            $status = 'Hadir';
                            $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(-15, 10))->format('H:i:s');
                        } elseif ($rand > 85 && $rand <= 95) {
                            $status = 'Terlambat';
                            $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(16, 30))->format('H:i:s');
                        } elseif ($rand > 95 && $rand <= 98) {
                            $status = 'Sakit'; $jamScan = '00:00:00';
                        } else {
                            $status = 'Izin'; $jamScan = '00:00:00';
                        }
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

                    // Insert jika batch mencapai 500
                    if (count($presensiBatch) >= 500) {
                        DB::table('presensi')->insert($presensiBatch);
                        $presensiBatch = [];
                    }
                }
            }
        }

        if (count($presensiBatch) > 0) {
            DB::table('presensi')->insert($presensiBatch);
        }

        echo "✅ SEEDING SELESAI!\n";
        echo "=======================================\n";
        echo "Admin     : admin / password \n";
        echo "Guru BK   : gurubk1 / password (mengampu X TKJ 1 & X TKJ 2)\n";
        echo "=======================================\n";
    }
}