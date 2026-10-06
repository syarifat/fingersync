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
        $tglAwalPresensi = '2026-09-01'; // Format: YYYY-MM-DD
        $tglAkhirPresensi = '2026-09-20'; // Format: YYYY-MM-DD
        $faker = Faker::create('id_ID');

        // ==========================================
        // 1. BERSIHKAN DATABASE (TRUNCATE)
        // ==========================================
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        $tables = [
            'users', 'admin', 'guru', 'siswa', 'jurusan', 'kelas', 
            'mata_pelajaran', 'tahun_ajar', 'ruangan', 'device', 
            'rombel_kelas', 'rombel_mata_pelajaran', 'rombel_jadwal_pelajaran', 'presensi',
            'fingerprint_inboxes', 'device_tasks', 'hari_liburs', 'kegiatan_sekolah', 'guru_kbm_khusus'
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

        // 3. RUANGAN & DEVICE (4 Ruangan Lab TKJ)
        // ==========================================
        $ruangans = ['Lab TKJ 1', 'Lab TKJ 2', 'Lab TKJ 3', 'Lab TKJ 4'];
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
                'id_device' => 'TKJ' . ($index + 1),
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

        // Guru BK (2 Orang. Sesuai use case nyata 1 Guru BK membina banyak kelas, Gelar S.Psi)
        $guruBkNames = [
            0 => ['Dian Safitri, S.Psi', 'Perempuan'],   // Membina X TKJ 1 & X TKJ 2
            1 => ['Rahmat Hidayat, S.Psi', 'Laki-laki'], // Membina X TKJ 3 & X TKJ 4
        ];
        $guruBkIds = [];
        for ($i=0; $i<2; $i++) {
            $username = 'gurubk' . ($i + 1);
            $nama = $guruBkNames[$i][0];
            $gender = $guruBkNames[$i][1];
            $uid = DB::table('users')->insertGetId([
                'nama' => $nama, 'username' => $username, 'password' => Hash::make('password'), 'role' => 'guru', 'created_at' => now(), 'updated_at' => now()
            ]);
            $guruBkIds[$i] = DB::table('guru')->insertGetId([
                'user_id' => $uid, 'nidn' => $faker->unique()->numerify('99########'),
                'nama' => $nama,
                'gender' => $gender, 'alamat' => $faker->address,
                'username' => $username, 'password' => Hash::make('password'), 'nohp' => $faker->phoneNumber,
                'is_bk' => 1, 'image' => 'default.png', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // Guru Mapel & Kejuruan (13 Orang dengan Spesialisasi Jelas)
        // Spesialisasi:
        // G1: Kejuruan TKJ & Informatika (S.Kom) -> Wali Kelas X TKJ 1, nohp 087859017087
        // G2: Kejuruan TKJ & Informatika (S.T)   -> Wali Kelas X TKJ 2, nohp 0895414397561
        // G3: Matematika (S.Pd)                  -> Wali Kelas X TKJ 3
        // G4: Bahasa Indonesia (S.Pd)            -> Wali Kelas X TKJ 4
        // G5: Bahasa Inggris (S.Pd)
        // G6: Pendidikan Agama (S.Pd.I)
        // G7: PPKn (S.Pd)
        // G8: PJOK (S.Pd)
        // G9: Sejarah (S.Pd)
        // G10: Seni Budaya (S.Pd)
        // G11: Matematika (S.Pd)
        // G12: Bahasa Indonesia (S.Pd)
        // G13: Bahasa Inggris (S.Pd)
        $guruSpec = [
            1  => ['nama' => 'Fajar Ramadhan, S.Kom', 'gender' => 'Laki-laki', 'nohp' => '087859017087'],
            2  => ['nama' => 'Dedi Kurniawan, S.T',   'gender' => 'Laki-laki', 'nohp' => '0895414397561'],
            3  => ['nama' => 'Hendra Wijaya, S.Pd',   'gender' => 'Laki-laki', 'nohp' => null],
            4  => ['nama' => 'Budi Santoso, S.Pd',    'gender' => 'Laki-laki', 'nohp' => null],
            5  => ['nama' => 'Eko Prasetyo, S.Pd',    'gender' => 'Laki-laki', 'nohp' => null],
            6  => ['nama' => 'Ahmad Fauzi, S.Pd.I',   'gender' => 'Laki-laki', 'nohp' => null],
            7  => ['nama' => 'Siti Rahmawati, S.Pd',  'gender' => 'Perempuan', 'nohp' => null],
            8  => ['nama' => 'Bambang Pamungkas, S.Pd','gender' => 'Laki-laki','nohp' => null],
            9  => ['nama' => 'Agus Setiawan, S.Pd',   'gender' => 'Laki-laki', 'nohp' => null],
            10 => ['nama' => 'Maya Indah, S.Pd',      'gender' => 'Perempuan', 'nohp' => null],
            11 => ['nama' => 'Rina Marlina, S.Pd',    'gender' => 'Perempuan', 'nohp' => null],
            12 => ['nama' => 'Sri Wahyuni, S.Pd',     'gender' => 'Perempuan', 'nohp' => null],
            13 => ['nama' => 'Dewi Lestari, S.Pd',    'gender' => 'Perempuan', 'nohp' => null],
        ];

        $guruMapelIds = [];
        foreach ($guruSpec as $num => $gInfo) {
            $username = 'guru' . $num;
            $uid = DB::table('users')->insertGetId([
                'nama' => $gInfo['nama'], 'username' => $username, 'password' => Hash::make('password'), 'role' => 'guru', 'created_at' => now(), 'updated_at' => now()
            ]);
            $guruMapelIds[$num] = DB::table('guru')->insertGetId([
                'user_id' => $uid, 'nidn' => $faker->unique()->numerify('20########'),
                'nama' => $gInfo['nama'],
                'gender' => $gInfo['gender'], 'alamat' => $faker->address,
                'username' => $username, 'password' => Hash::make('password'),
                'nohp' => $gInfo['nohp'] ?: $faker->phoneNumber,
                'is_bk' => 0, 'image' => 'default.png', 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()
            ]);
        }
        // 4 Guru pertama kita jadikan Wali Kelas
        $waliKelasIds = [
            0 => $guruMapelIds[1],
            1 => $guruMapelIds[2],
            2 => $guruMapelIds[3],
            3 => $guruMapelIds[4],
        ];

        // ==========================================
        // 6. KELAS, SISWA & ROMBEL KELAS
        // ==========================================
        echo "🏢 Membuat 4 Kelas (X TKJ 1, X TKJ 2, X TKJ 3, X TKJ 4) & 30 Siswa per Kelas...\n";
        $kelasNames = ['X TKJ 1', 'X TKJ 2', 'X TKJ 3', 'X TKJ 4'];
        $kelasData = [];
        $siswaProfiles = []; 
        $fingerprintCounter = 1;

        // Buat kelas terlebih dahulu
        foreach ($kelasNames as $index => $namaKelas) {
            $grupWa = null;
            if ($index === 0) {
                $grupWa = '120363428223127027@g.us';
            } elseif ($index === 1) {
                $grupWa = '120363410062664882@g.us';
            }

            $kelasId = DB::table('kelas')->insertGetId([
                'nama' => $namaKelas, 
                'id_jurusan' => $jurusanId, 
                'id_grup_wa' => $grupWa,
                'created_at' => now(), 'updated_at' => now()
            ]);

            // Guru BK: 1 Guru BK membina 2 kelas (TKJ 1-2 oleh gurubk1, TKJ 3-4 oleh gurubk2)
            $guruBkAssigned = ($index < 2) ? $guruBkIds[0] : $guruBkIds[1];
            // Setiap kelas punya wali kelas beda
            $waliKelasAssigned = $waliKelasIds[$index];

            $kelasData[] = [
                'id' => $kelasId,
                'class_num' => $index + 1,
                'nama' => $namaKelas,
                'guru_bk' => $guruBkAssigned,
                'wali_kelas' => $waliKelasAssigned,
                'ruangan_id' => $ruanganIds[$index],
            ];
        }

        // Setup Profile Siswa di masing-masing kelas secara terpisah: 5 Teladan, 5 Bermasalah, 20 Biasa
        $classProfiles = [];
        for ($i = 0; $i < 4; $i++) {
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
                    'nohp_ortu' => ($fingerprintCounter == 1) ? '087842949212' : (($fingerprintCounter == 2) ? '085536949348' : $faker->phoneNumber),
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

        // 7.a. Rombel Mapel (Memetakan Guru Spesialis ke Rombel Mapel Setiap Kelas)
        $rombelMapelIds = []; // Key: kelas_id => [mapel_nama => id]

        // Helper fungsi pemetaan guru spesialis berdasarkan kelas dan mapel
        $getTeacherForMapel = function($classNum, $mapelNama) use ($guruMapelIds, $guruBkIds) {
            if ($mapelNama === 'Bimbingan Konseling') {
                return ($classNum <= 2) ? $guruBkIds[0] : $guruBkIds[1];
            }
            // Guru Kejuruan (Produktif TKJ & Informatika mengajar 2 mapel kejuruan)
            if ($mapelNama === 'Dasar Kejuruan TKJ' || $mapelNama === 'Informatika') {
                return ($classNum <= 2) ? $guruMapelIds[1] : $guruMapelIds[2];
            }
            // Guru Mapel Umum Spesialis Murni (Hanya mengajar 1 bidang studi):
            return match($mapelNama) {
                'Matematika'       => ($classNum <= 2) ? $guruMapelIds[3] : $guruMapelIds[11],
                'Bahasa Indonesia' => ($classNum <= 2) ? $guruMapelIds[4] : $guruMapelIds[12],
                'Bahasa Inggris'   => ($classNum <= 2) ? $guruMapelIds[5] : $guruMapelIds[13],
                'Pendidikan Agama' => $guruMapelIds[6],
                'PPKn'             => $guruMapelIds[7],
                'PJOK'             => $guruMapelIds[8],
                'Sejarah'          => $guruMapelIds[9],
                'Seni Budaya'      => $guruMapelIds[10],
                default            => $guruMapelIds[1]
            };
        };

        foreach ($kelasData as $k) {
            $classNum = $k['class_num'];
            foreach ($mapels as $mapelNama) {
                $teacherId = $getTeacherForMapel($classNum, $mapelNama);
                $rombelMapelIds[$k['id']][$mapelNama] = DB::table('rombel_mata_pelajaran')->insertGetId([
                    'id_kelas' => $k['id'],
                    'id_mata_pelajaran' => $mapelDbIds[$mapelNama],
                    'id_guru' => $teacherId,
                    'id_tahun_ajar' => $tahunAjarId,
                    'created_at' => now(), 'updated_at' => now()
                ]);
            }
        }

        // 7.b. Penjadwalan Pelajaran secara Konflik-Free & Realistis (15 Slot: Senin s/d Jumat, 3 Sesi/hari)
        $classTimetable = [
            1 => [
                "Bimbingan Konseling", "Bahasa Inggris", "Sejarah",
                "Bahasa Indonesia", "Bahasa Inggris", "Matematika",
                "Pendidikan Agama", "Dasar Kejuruan TKJ", "PJOK",
                "Dasar Kejuruan TKJ", "Bahasa Indonesia", "Matematika",
                "Seni Budaya", "PPKn", "Informatika"
            ],
            2 => [
                "PPKn", "Informatika", "Pendidikan Agama",
                "Sejarah", "Seni Budaya", "Bahasa Indonesia",
                "Bahasa Indonesia", "Matematika", "Dasar Kejuruan TKJ",
                "Bahasa Inggris", "Matematika", "Dasar Kejuruan TKJ",
                "Bahasa Inggris", "PJOK", "Bimbingan Konseling"
            ],
            3 => [
                "Bahasa Indonesia", "Pendidikan Agama", "Dasar Kejuruan TKJ",
                "Bahasa Indonesia", "Bimbingan Konseling", "Matematika",
                "Informatika", "PPKn", "Bahasa Inggris",
                "Bahasa Inggris", "Dasar Kejuruan TKJ", "Sejarah",
                "PJOK", "Seni Budaya", "Matematika"
            ],
            4 => [
                "Bimbingan Konseling", "Matematika", "Seni Budaya",
                "Dasar Kejuruan TKJ", "Bahasa Inggris", "Bahasa Indonesia",
                "Bahasa Indonesia", "Sejarah", "Pendidikan Agama",
                "PJOK", "Matematika", "Dasar Kejuruan TKJ",
                "PPKn", "Bahasa Inggris", "Informatika"
            ]
        ];

        for ($slot = 0; $slot < 15; $slot++) {
            $hariIndex = intdiv($slot, 3);
            $sesiIndex = $slot % 3;
            $hari = $hariSekolah[$hariIndex];
            $jamMulai = $jamPelajaran[$sesiIndex][0];
            $jamSelesai = $jamPelajaran[$sesiIndex][1];

            foreach ($kelasData as $k) {
                $classNum = $k['class_num'];
                $ruanganId = $k['ruangan_id'];
                $deviceId = $deviceIds[$ruanganId] ?? null;

                $mapelNama = $classTimetable[$classNum][$slot];
                $rombelMapelId = $rombelMapelIds[$k['id']][$mapelNama];

                $jadwalId = DB::table('rombel_jadwal_pelajaran')->insertGetId([
                    'id_rombel_mata_pelajaran' => $rombelMapelId,
                    'hari' => $hari,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'id_ruangan' => $ruanganId,
                    'created_at' => now(), 'updated_at' => now()
                ]);

                $jadwalData[] = [
                    'id' => $jadwalId, 
                    'hari' => $hari, 
                    'jam_mulai' => $jamMulai, 
                    'id_device' => $deviceId, 
                    'id_kelas' => $k['id']
                ];
            }
        }
          // ==========================================
        // 7.c. HARI LIBUR, KEGIATAN SEKOLAH, GURU KBM KHUSUS
        // ==========================================
        echo "📅 Menyemai Hari Libur, Kegiatan Sekolah, dan Guru KBM Khusus...\n";
        
        // Seed Hari Libur (e.g. 2026-06-04)
        $tglLibur = '2026-06-04';
        DB::table('hari_liburs')->insert([
            'nama' => 'Hari Raya Keagamaan',
            'tanggal_mulai' => $tglLibur,
            'tanggal_selesai' => $tglLibur,
            'jenis' => 'nasional',
            'keterangan' => 'Libur Nasional',
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Seed Kegiatan Sekolah Serentak (e.g. 2026-06-10)
        $tglKegiatan = '2026-06-10';
        $kegiatanId = DB::table('kegiatan_sekolah')->insertGetId([
            'nama_kegiatan' => 'PORSENI Sekolah',
            'tanggal' => $tglKegiatan,
            'tipe' => 'serentak',
            'jam_mulai_datang' => '06:30:00',
            'jam_selesai_datang' => '09:00:00',
            'jam_mulai_pulang' => '12:00:00',
            'jam_selesai_pulang' => '16:00:00',
            'keterangan' => 'Pekan Olahraga dan Seni Antar Kelas',
            'created_at' => now(), 'updated_at' => now()
        ]);

        // Seed Guru KBM Khusus (e.g. 2026-06-05, ambil jadwal pertama kelas pertama)
        $tglKhusus = '2026-06-05';
        $hariKhususIndo = 'Jumat';
        $jadwalIzin = collect($jadwalData)->first(fn($j) => $j['hari'] == $hariKhususIndo);
        if ($jadwalIzin) {
            DB::table('guru_kbm_khusus')->insert([
                'id_rombel_jadwal_pelajaran' => $jadwalIzin['id'],
                'tanggal' => $tglKhusus,
                'status' => 'izin',
                'id_guru_pengganti' => null,
                'keterangan' => 'Belajar Mandiri: Kerjakan LKS Hal 50',
                'created_at' => now(), 'updated_at' => now()
            ]);
        }
        
        // Ambil jadwal lain di hari yang sama untuk Guru Digantikan
        $jadwalDiganti = collect($jadwalData)->first(fn($j) => $j['hari'] == $hariKhususIndo && $j['id'] != ($jadwalIzin['id'] ?? null));
        if ($jadwalDiganti) {
            // Guru Pengganti: cari guru lain selain yang mengampu jadwal ini
            $guruUtamaId = DB::table('rombel_jadwal_pelajaran')
                ->join('rombel_mata_pelajaran', 'rombel_jadwal_pelajaran.id_rombel_mata_pelajaran', '=', 'rombel_mata_pelajaran.id')
                ->where('rombel_jadwal_pelajaran.id', $jadwalDiganti['id'])
                ->value('rombel_mata_pelajaran.id_guru');
                
            $guruPenggantiId = collect($guruMapelIds)->first(fn($g) => $g != $guruUtamaId);

            DB::table('guru_kbm_khusus')->insert([
                'id_rombel_jadwal_pelajaran' => $jadwalDiganti['id'],
                'tanggal' => $tglKhusus,
                'status' => 'diganti',
                'id_guru_pengganti' => $guruPenggantiId,
                'keterangan' => 'Didampingi guru pengganti untuk materi praktikum.',
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // ==========================================
        // 8. GENERATE PRESENSI
        // ==========================================
        echo "⏰ Generating Presensi ({$tglAwalPresensi} sampai {$tglAkhirPresensi})...\n";
        $startDate = Carbon::parse($tglAwalPresensi);
        $endDate = Carbon::parse($tglAkhirPresensi);
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        
        $presensiBatch = [];

        $basePresensi = [
            'id_siswa' => null,
            'id_rombel_jadwal_pelajaran' => null,
            'id_kegiatan_sekolah' => null,
            'tipe_scan_kegiatan' => null,
            'tipe_scan' => 'datang',
            'tanggal' => null,
            'jam_scan' => null,
            'id_device' => null,
            'status' => 'Hadir',
            'id_tahun_ajar' => null,
            'created_at' => null,
            'updated_at' => null,
        ];

        foreach ($period as $date) {
            $tglStr = $date->format('Y-m-d');
            if ($date->isWeekend()) continue; // Senin sampai Jumat saja

            // Skip jika Hari Libur
            if ($tglStr == $tglLibur) {
                continue;
            }

            // Jika tanggal kegiatan sekolah serentak, buat presensi datang & pulang kegiatan
            if ($tglStr == $tglKegiatan) {
                foreach ($kelasData as $k) {
                    $siswaIdsDiKelas = DB::table('rombel_kelas')
                        ->where('id_kelas', $k['id'])
                        ->where('id_tahun_ajar', $tahunAjarId)
                        ->pluck('id_siswa');

                    $deviceId = $deviceIds[$k['ruangan_id']] ?? null;

                    foreach ($siswaIdsDiKelas as $sid) {
                        $profileType = $siswaProfiles[$sid];
                        $jamDatang = '07:00:00';
                        $jamPulang = '13:00:00';

                        if ($profileType == 'teladan') {
                            $jamDatang = Carbon::parse('07:00:00')->addMinutes(rand(-20, -5))->format('H:i:s');
                            $jamPulang = Carbon::parse('13:00:00')->addMinutes(rand(5, 30))->format('H:i:s');
                        } elseif ($profileType == 'bermasalah') {
                            $jamDatang = Carbon::parse('07:00:00')->addMinutes(rand(-5, 45))->format('H:i:s');
                            $jamPulang = Carbon::parse('13:00:00')->addMinutes(rand(-10, 15))->format('H:i:s');
                        } else {
                            $jamDatang = Carbon::parse('07:00:00')->addMinutes(rand(-15, 15))->format('H:i:s');
                            $jamPulang = Carbon::parse('13:00:00')->addMinutes(rand(-5, 20))->format('H:i:s');
                        }

                        // Presensi Datang Kegiatan
                        $presensiBatch[] = array_merge($basePresensi, [
                            'id_siswa' => $sid,
                            'id_kegiatan_sekolah' => $kegiatanId,
                            'tipe_scan_kegiatan' => 'datang',
                            'tanggal' => $tglStr,
                            'jam_scan' => $jamDatang,
                            'id_device' => $deviceId,
                            'id_tahun_ajar' => $tahunAjarId,
                            'created_at' => $date->format('Y-m-d') . ' ' . $jamDatang,
                            'updated_at' => $date->format('Y-m-d') . ' ' . $jamDatang
                        ]);

                        // Presensi Pulang Kegiatan
                        $presensiBatch[] = array_merge($basePresensi, [
                            'id_siswa' => $sid,
                            'id_kegiatan_sekolah' => $kegiatanId,
                            'tipe_scan_kegiatan' => 'pulang',
                            'tanggal' => $tglStr,
                            'jam_scan' => $jamPulang,
                            'id_device' => $deviceId,
                            'id_tahun_ajar' => $tahunAjarId,
                            'created_at' => $date->format('Y-m-d') . ' ' . $jamPulang,
                            'updated_at' => $date->format('Y-m-d') . ' ' . $jamPulang
                        ]);
                    }
                }
                continue; // Lanjut ke tanggal berikutnya
            }

            $hariIndo = match($date->format('l')) {
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', default => ''
            };

            // Ambil jadwal hari ini untuk semua kelas
            $jadwalHariIni = array_filter($jadwalData, fn($j) => $j['hari'] == $hariIndo);

            // Tentukan status harian untuk setiap siswa terlebih dahulu
            $siswaDailyStatus = [];
            foreach ($kelasData as $k) {
                $siswaIdsDiKelas = DB::table('rombel_kelas')
                    ->where('id_kelas', $k['id'])
                    ->where('id_tahun_ajar', $tahunAjarId)
                    ->pluck('id_siswa');

                foreach ($siswaIdsDiKelas as $sid) {
                    $profileType = $siswaProfiles[$sid];
                    $rand = rand(1, 100);
                    $dailyStatus = 'Normal';

                    if ($profileType == 'teladan') {
                        $dailyStatus = 'Normal';
                    } elseif ($profileType == 'bermasalah') {
                        // Bermasalah: 15% Izin full day, 85% Normal (Bisa Alpha di mapel tertentu)
                        if ($rand <= 15) {
                            $dailyStatus = 'Izin';
                        } else {
                            $dailyStatus = 'Normal';
                        }
                    } else { // Biasa
                        // 3% Sakit full day, 2% Izin full day, 95% Normal
                        if ($rand <= 3) {
                            $dailyStatus = 'Sakit';
                        } elseif ($rand > 3 && $rand <= 5) {
                            $dailyStatus = 'Izin';
                        } else {
                            $dailyStatus = 'Normal';
                        }
                    }

                    $siswaDailyStatus[$sid] = $dailyStatus;
                }
            }

            // Untuk melacak apakah siswa melakukan check-in setidaknya sekali pada hari ini
            $hasCheckinToday = [];

            foreach ($jadwalHariIni as $jadwal) {
                // Ambil semua murid di kelas ini
                $siswaIdsDiKelas = DB::table('rombel_kelas')
                    ->where('id_kelas', $jadwal['id_kelas'])
                    ->where('id_tahun_ajar', $tahunAjarId)
                    ->pluck('id_siswa');

                foreach ($siswaIdsDiKelas as $sid) {
                    $profileType = $siswaProfiles[$sid];
                    $dailyStatus = $siswaDailyStatus[$sid] ?? 'Normal';
                    
                    if ($dailyStatus === 'Sakit' || $dailyStatus === 'Izin') {
                        $status = $dailyStatus;
                        $jamScan = '00:00:00';
                    } else {
                        // Siswa Normal: bisa Hadir, Terlambat, atau Alpha per mata pelajaran
                        $status = 'Hadir';
                        $jamScan = $jadwal['jam_mulai'];
                        $rand = rand(1, 100);

                        if ($profileType == 'teladan') {
                            $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(-30, -5))->format('H:i:s');
                            $hasCheckinToday[$sid] = true;
                        } elseif ($profileType == 'bermasalah') {
                            // 45% Alpha (bolos mata pelajaran ini), 40% Terlambat, 15% Hadir
                            if ($rand <= 45) {
                                $status = 'Alpha';
                                $jamScan = '00:00:00';
                            } elseif ($rand > 45 && $rand <= 85) {
                                $status = 'Terlambat';
                                $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(16, 90))->format('H:i:s');
                                $hasCheckinToday[$sid] = true;
                            } else {
                                $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(-5, 10))->format('H:i:s');
                                $hasCheckinToday[$sid] = true;
                            }
                        } else { // Biasa
                            // 90% Hadir, 7% Terlambat, 3% Alpha (bolos mata pelajaran ini)
                            if ($rand <= 90) {
                                $status = 'Hadir';
                                $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(-15, 10))->format('H:i:s');
                                $hasCheckinToday[$sid] = true;
                            } elseif ($rand > 90 && $rand <= 97) {
                                $status = 'Terlambat';
                                $jamScan = Carbon::parse($jadwal['jam_mulai'])->addMinutes(rand(16, 30))->format('H:i:s');
                                $hasCheckinToday[$sid] = true;
                            } else {
                                $status = 'Alpha';
                                $jamScan = '00:00:00';
                            }
                        }
                    }

                    $presensiBatch[] = array_merge($basePresensi, [
                        'id_siswa' => $sid,
                        'id_rombel_jadwal_pelajaran' => $jadwal['id'],
                        'tanggal' => $date->format('Y-m-d'),
                        'jam_scan' => $jamScan,
                        'id_device' => $jadwal['id_device'],
                        'status' => $status,
                        'id_tahun_ajar' => $tahunAjarId,
                        'created_at' => $date->format('Y-m-d H:i:s'),
                        'updated_at' => $date->format('Y-m-d H:i:s')
                    ]);

                    // Insert jika batch mencapai 500
                    if (count($presensiBatch) >= 500) {
                        DB::table('presensi')->insert($presensiBatch);
                        $presensiBatch = [];
                    }
                }
            }

            // Seeding Absen Pulang setelah KBM selesai
            foreach ($kelasData as $k) {
                $siswaIdsDiKelas = DB::table('rombel_kelas')
                    ->where('id_kelas', $k['id'])
                    ->where('id_tahun_ajar', $tahunAjarId)
                    ->pluck('id_siswa');

                $deviceId = $deviceIds[$k['ruangan_id']] ?? null;

                foreach ($siswaIdsDiKelas as $sid) {
                    // Jika status harian siswa Sakit/Izin, atau jika tidak melakukan check-in sama sekali hari ini, skip scan pulang
                    $dailyStatus = $siswaDailyStatus[$sid] ?? 'Normal';
                    if ($dailyStatus === 'Sakit' || $dailyStatus === 'Izin' || !isset($hasCheckinToday[$sid])) {
                        continue;
                    }

                    $profileType = $siswaProfiles[$sid] ?? 'biasa';
                    
                    // Probabilitas scan pulang realistis
                    $randPulang = rand(1, 100);
                    $shouldSeedPulang = false;
                    
                    if ($profileType == 'teladan') {
                        $shouldSeedPulang = true;
                    } elseif ($profileType == 'bermasalah') {
                        // Hanya 60% yang scan pulang, sisanya bolos pulang
                        if ($randPulang <= 60) {
                            $shouldSeedPulang = true;
                        }
                    } else { // Biasa
                        // 95% scan pulang
                        if ($randPulang <= 95) {
                            $shouldSeedPulang = true;
                        }
                    }

                    if ($shouldSeedPulang) {
                        // Jam selesai KBM hari itu adalah 12:00:00
                        if ($profileType == 'teladan') {
                            $jamScanPulang = Carbon::parse('12:00:00')->addMinutes(rand(1, 15))->format('H:i:s');
                        } elseif ($profileType == 'bermasalah') {
                            $jamScanPulang = Carbon::parse('12:00:00')->addMinutes(rand(5, 120))->format('H:i:s');
                        } else {
                            $jamScanPulang = Carbon::parse('12:00:00')->addMinutes(rand(2, 45))->format('H:i:s');
                        }
                        
                        $presensiBatch[] = array_merge($basePresensi, [
                            'id_siswa' => $sid,
                            'tipe_scan' => 'pulang',
                            'tanggal' => $tglStr,
                            'jam_scan' => $jamScanPulang,
                            'id_device' => $deviceId,
                            'id_tahun_ajar' => $tahunAjarId,
                            'created_at' => $date->format('Y-m-d') . ' ' . $jamScanPulang,
                            'updated_at' => $date->format('Y-m-d') . ' ' . $jamScanPulang
                        ]);

                        // Insert jika batch mencapai 500
                        if (count($presensiBatch) >= 500) {
                            DB::table('presensi')->insert($presensiBatch);
                            $presensiBatch = [];
                        }
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
        echo "Guru BK 1 : gurubk1 / password (Binaan: X TKJ 1 & X TKJ 2)\n";
        echo "Guru BK 2 : gurubk2 / password (Binaan: X TKJ 3 & X TKJ 4)\n";
        echo "Total     : 4 Kelas (X TKJ 1 - 4), 120 Siswa\n";
        echo "=======================================\n";
    }
}