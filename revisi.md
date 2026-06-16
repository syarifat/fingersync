# REVISI 14 JUNI 2026 - SETELAH SIDANG

---

### 1. 🚪 Ada absensi untuk pulang
*Status: ✅ Selesai (16 Juni 2026)*

#### 📝 Walkthrough Implementasi:
*   **Database & Migrasi**: Menambahkan kolom `tipe_scan` (string, default `'datang'`) ke tabel `presensi`. Kolom `id_rombel_jadwal_pelajaran` dibuat nullable karena scan pulang tidak terikat pada satu jadwal mapel tertentu.
*   **Logika API Alat (`DeviceController@scan`)**:
    *   Mendeteksi kelas siswa dari rombel aktif tahun ajaran berjalan.
    *   Mencari jadwal pelajaran terakhir kelas tersebut pada hari berjalan.
    *   **Jendela Absen Pulang**: Dimulai dari jam selesai pelajaran terakhir hingga pukul 16:00:00. Jika tidak ada jadwal hari ini, fallback ke 12:00:00 s.d 16:00:00.
    *   Jika scan sidik jari dilakukan dalam jendela waktu tersebut, maka dicatat sebagai **Absen Pulang** (dengan `tipe_scan = 'pulang'` dan `id_rombel_jadwal_pelajaran = null`), mengirim notifikasi WA Pulang ke orang tua siswa, dan mengembalikan respons sukses pulang ke alat ESP32.
*   **Tampilan Admin & Guru (UI/UX)**:
    *   **Manajemen Presensi Admin**: Data scan pulang ditampilkan dengan deskripsi **"Absen Pulang Sekolah"** di kolom Jadwal/Mapel dan badge status **"PULANG"** (berwarna teal lembut `bg-teal-50 text-teal-600`).
    *   **Edit Presensi**: Detail "Absen Pulang Sekolah" dan waktu "Selesai KBM - 16:00" ditampilkan menggantikan "Mapel Tidak Ditemukan" untuk memudahkan pengeditan oleh admin.
    *   **Dashboard Admin & Guru**: Tabel log presensi terbaru mendeteksi scan pulang dan menampilkannya dengan label "Absen Pulang Sekolah" dan status "PULANG" (bg-teal-100 text-teal-800).
*   **Laporan & Rekap Sore WA (`KirimRekapSore`)**:
    *   Di rekap sore (pukul 16:00), sistem secara dinamis mengecek apakah siswa bersangkutan telah melakukan scan pulang hari ini.
    *   Menambahkan status checkout di baris terbawah info kehadiran harian siswa, baik di grup kelas maupun pesan individu wali murid:
        *   Jika sudah scan: `🚪 *Scan Pulang:* [Jam Scan] WIB (Sudah Pulang)`
        *   Jika belum scan: `🚪 *Scan Pulang:* - (Belum Scan Pulang / Bolos)`
*   **Seeder Database (`DatabaseSeeder`)**:
    *   Memperbarui seeder agar secara realistis menghasilkan data scan pulang untuk siswa-siswi pada jam pulang sekolah setelah jadwal pelajaran terakhir selesai (antara pukul 12:00 s.d 13:00) dengan probabilitas kehadiran yang disesuaikan berdasarkan profil siswa (teladan, biasa, bermasalah).

---

### 2. 📊 Menampilkan informasi siswa terlambat dikelompokkan berdasarkan nama kelas
*Status: ✅ Selesai (14 Juni 2026)*

#### 📝 Walkthrough Implementasi:
*   **Logika di Sisi Admin (`AdminController`)**: Mengambil tanggal presensi terakhir (`Presensi::max('tanggal')`), mencari semua siswa dengan status `'Terlambat'` pada tanggal tersebut, dan mengelompokkannya menggunakan method `groupBy` dari Collection Laravel berdasarkan nama kelas (`rombelJadwalPelajaran.rombelMataPelajaran.kelas.nama`).
*   **Logika di Sisi Guru (`DashboardController`)**:
    *   Mencari tanggal presensi terakhir dan nama harinya.
    *   Mengambil daftar ID kelas yang terhubung dengan Guru yang sedang login, baik kelas di mana ia bertindak sebagai Wali Kelas (`rombel_kelas`) maupun kelas yang diajarnya pada hari presensi terakhir tersebut/hari kalender ini (`rombel_jadwal_pelajaran` -> `rombel_mata_pelajaran`).
    *   Mengambil presensi siswa dengan status `'Terlambat'` pada tanggal terakhir tersebut yang merupakan bagian dari kelas-kelas milik Guru tersebut, lalu mengelompokkannya berdasarkan nama kelas.
*   **Tampilan Halaman (UI/UX - Admin & Guru)**:
    *   Ditambahkan kartu info khusus **"Siswa Terlambat Kehadiran"** di Dashboard Admin dan Dashboard Guru (hanya jika ada siswa terlambat pada tanggal presensi terakhir).
    *   Menggunakan kartu premium dengan border/latar belakang rose lembut (`bg-rose-50/30`), badge jumlah siswa terlambat per kelas, dan list detail nama siswa beserta jam scan sidik jari mereka untuk kemudahan monitoring.
    *   Telah dirapikan pula kode duplikat nama kelas pada jadwal mengajar di Dashboard Guru.

---

### 3. 📅 Connect api kalender akademik, berisi hari libur phbn phbi dll. Dan buat alat juga terpengaruh dengan ini, misal libur berarti alat tidak bisa memproses data atau gimana nanti respond nya
*Status: ✅ Selesai (14 Juni 2026)*

#### 📝 Walkthrough Implementasi:
*   **Database & Model (`hari_liburs`)**: Dibuat tabel database `hari_liburs` untuk menampung data libur nasional (PHBN/PHBI) dan libur sekolah manual. Model `HariLibur` dilengkapi dengan helper statis `HariLibur::isHoliday($date)` dan `HariLibur::getHolidayName($date)`.
*   **Koneksi API Kalender**: Dibuat `AcademicCalendarService` untuk mengambil data hari libur nasional resmi secara otomatis dari `https://api-hari-libur.vercel.app/api`.
*   **Proteksi Alat (ESP32)**: Menambahkan pengecekan di `DeviceController@scan`. Apabila siswa melakukan scan di hari libur, API akan mengembalikan respons JSON `{"status": "ERROR", "message": "Hari Libur: [Nama Libur]"}`. Layar LCD alat akan langsung menampilkan pesan `"AKSES DITOLAK!"` beserta nama hari liburnya tanpa perlu mengubah kode firmware alat.
*   **Bypass Otomatis Cron Job**: Perintah `absensi:cek-anomali` dan `absensi:rekap-sore` otomatis menghentikan proses pengiriman notifikasi dan input Alpha otomatis pada hari libur agar menghindari spam/kesalahan laporan ke orang tua/guru.
*   **Interface Admin (UI/CRUD)**: Ditambahkan halaman manajemen **Hari Libur** baru di panel admin untuk melakukan CRUD libur sekolah secara manual dan tombol sinkronisasi instan API Hari Libur Nasional.

---

### 4. 📈 Ditambahkan informasi jumlah AIS(Alpha, Izin, Sakit) yang nantinya bisa pengembangan untuk menentukan SP siswa
*Status: ✅ Selesai (14 Juni 2026)*

#### 📝 Walkthrough Implementasi:
*   **Tampilan Web (Panel Wali Kelas)**: Memperbarui view [index.blade.php](file:///Users/syarifat/Data/my_project/fingersync/resources/views/guru/walikelas/index.blade.php). Menambahkan kolom baru **"Jumlah AIS"** di tabel rekapitulasi kehadiran bulanan siswa. Kolom ini menghitung akumulasi total ketidakhadiran siswa (`Alpha` + `Izin` + `Sakit`) dan menampilkan jumlahnya dengan badge berwarna merah (*rose*) untuk memudahkan pemantauan dan dasar pengajuan Surat Peringatan (SP) siswa oleh Guru BK/Wali Kelas.
*   **Laporan PDF (Hasil Export)**: Memperbarui template PDF [pdf.blade.php](file:///Users/syarifat/Data/my_project/fingersync/resources/views/admin/presensi/pdf.blade.php) yang digunakan oleh Admin dan Wali Kelas saat melakukan export laporan presensi bulanan. Menambahkan kolom **"AIS"** di baris Total sebelah kanan setelah kolom `A` (Alpa) serta memperluas header. Kolom ini secara otomatis menjumlahkan total ketidakhadiran siswa (`A` + `I` + `S`) pada bulan berjalan dan menyorotinya dengan latar belakang merah muda (*rose*) serta teks merah tebal. Legend keterangan di bawah tabel PDF juga telah diperbarui dengan penjelasan kolom AIS.

---

### 5. 💬 Notifikasi rekap harian kalau bisa dikirim ke grub wali siswa per kelas
*Status: ✅ Selesai (14 Juni 2026)*

#### 📝 Walkthrough Implementasi:
*   **Database & Model (`kelas`)**: Menambahkan kolom `id_grup_wa` di tabel `kelas` untuk menyimpan WhatsApp Group ID unik dari Fonnte (contoh: `120363073948572834@g.us`).
*   **Sync Grup Fonnte (`WhatsAppService`)**: Dibuat method helper `WhatsAppService::getGroups()` untuk menyinkronkan dan mengambil daftar grup WhatsApp aktif langsung dari Fonnte API.
*   **UI Manajemen Admin**: Memperbarui form edit kelas dan daftar kelas di panel admin. Admin kini dapat memilih grup WhatsApp wali kelas dari select dropdown secara langsung berdasarkan daftar grup aktif dari Fonnte.
*   **Logic Pengiriman Terkelompok (`KirimRekapSore`)**: Merombak total logic di `absensi:rekap-sore` agar mengelompokkan laporan kehadiran berdasarkan kelas. 
    *   Jika kelas memiliki grup WA yang terhubung, rekap seluruh siswa dalam kelas tersebut digabungkan menjadi satu pesan terformat rapi dan dikirim langsung ke grup WA.
    *   Pesan grup dipecah secara cerdas (maksimal 10 siswa per pesan) untuk menghindari kegagalan pengiriman di Fonnte karena batas panjang karakter WhatsApp.
    *   Jika kelas belum dihubungkan ke grup WA, sistem otomatis menggunakan opsi cadangan (*fallback*), yaitu mengirimkan laporan individu ke WhatsApp orang tua masing-masing siswa.

---

### 6. 🔄 Logika kondisi khusus guru izin, absen, atau diganti, serta kegiatan sekolah serentak
*Status: ✅ Selesai (14 Juni 2026)*

#### 📝 Walkthrough Implementasi:
*   **Database & Migrasi**:
    *   Tabel `kegiatan_sekolah`: menyimpan agenda kegiatan serentak sekolah (PORSENI, Ujian Tengah Semester, dll.) beserta konfigurasi window waktu absen datang dan pulang.
    *   Tabel `guru_kbm_khusus`: mencatat status khusus guru (izin, absen, diganti) pada tanggal dan jadwal tertentu, termasuk relasi ke `id_guru_pengganti`.
    *   Tabel `presensi`: kolom `id_rombel_jadwal_pelajaran` dibuat nullable, ditambahkan foreign key `id_kegiatan_sekolah` dan string `tipe_scan_kegiatan` ('datang' atau 'pulang').
*   **Logika API Scan Alat (`DeviceController@scan`)**:
    *   **Bypass KBM Reguler**: Jika ada kegiatan serentak hari ini, verifikasi jadwal dan ruangan kelas dilewati. Siswa dapat menempelkan jari di alat mana saja. Scan dalam rentang waktu pagi tercatat sebagai "Datang", sedangkan siang/sore tercatat sebagai "Pulang" untuk kegiatan sekolah tersebut. Notifikasi WhatsApp ke orang tua otomatis menyesuaikan detail nama kegiatan.
    *   **Kondisi Guru**: Jika ada record KBM khusus untuk guru:
        *   Jika Guru **Izin/Absen**: Scan sidik jari siswa tetap diterima (Hadir/Terlambat), respons LCD alat berubah menjadi "KBM Mandiri / Guru Izin", dan notifikasi WhatsApp orang tua mengabarkan bahwa KBM mandiri karena guru berhalangan.
        *   Jika Guru **Diganti**: Scan siswa diterima normal, LCD menampilkan nama mapel (Pengganti) & nama Guru Pengganti, serta memberikan hak akses absensi kelas kepada Guru Pengganti di dashboard mereka.
*   **Dashboard & Riwayat Guru**:
    *   Di `DashboardController` & `RiwayatAbsensiController`, jika guru utama digantikan hari ini, jadwal reguler disembunyikan/diberi label "Digantikan oleh [Guru Pengganti]" dan tombol "Lihat Presensi" dinonaktifkan.
    *   Jika guru login bertindak sebagai Guru Pengganti hari ini, jadwal mengajar tambahan otomatis muncul di dashboard mereka.
    *   Pengecekan otorisasi di `PresensiController` & `RiwayatAbsensiController` diperluas agar memperbolehkan Guru Pengganti mengelola absensi kelas tersebut khusus pada tanggal penggantian.
*   **Interface Admin (CRUD Premium)**:
    *   Dibuat CRUD **Kegiatan Sekolah** dan **KBM Khusus Guru** dengan tampilan modern, responsif, dan konsisten menggunakan skema warna orange/gray fingersync.
    *   Sidebar navigation telah diperbarui untuk menyertakan tautan menu baru tersebut tepat di bawah menu Hari Libur.



HASIL
- point 1 done
- point 2 done
- point 3 done
- point 4 done
- point 5.1(kegiatan serentak) sudah done tapi saya bingung dimana melihat hasil data presensinya? di menu presensi tidak ada, apakah perlu mengubah tampilan logika di menu presensi, lalu hasilnya di export harusnya juga berpengaruh kan tulisannya apa? apa ya Hsaja? (REVISI DONE: Presensi kegiatan serentak kini muncul di menu presensi dengan badge & detail khusus, filter kelas disesuaikan berdasarkan kelas siswa, dan rekap PDF memuat tabel virtual Kegiatan Sekolah dengan tanda H pada tanggal kegiatan)
- point 5.2(guru berhalangan hadir) done
- point 5.3(guru digantikan) done