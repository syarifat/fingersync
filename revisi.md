# REVISI 14 JUNI 2026 - SETELAH SIDANG

---

### 1. 🚪 Ada absensi untuk pulang
*Status: ⏳ Belum dikerjakan*
*   **Deskripsi**: Menambahkan pencatatan scan pulang pada alat absensi (ESP32) dan rekap data presensi pulang di database.

---

### 2. 📊 Menampilkan informasi siswa terlambat dikelompokkan berdasarkan nama kelas
*Status: ⏳ Belum dikerjakan*
*   **Deskripsi**: Mengelompokkan riwayat presensi siswa dengan status 'Terlambat' berdasarkan nama kelas masing-masing di dashboard admin/guru.

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
*Status: ⏳ Belum dikerjakan*
*   **Deskripsi**: Menghitung akumulasi jumlah ketidakhadiran (Alpha, Izin, Sakit) siswa di rekapitulasi, yang dapat digunakan oleh admin/BK untuk menentukan Surat Peringatan (SP).

---

### 5. 💬 Notifikasi rekap harian kalau bisa dikirim ke grub wali siswa per kelas
*Status: ⏳ Belum dikerjakan*
*   **Deskripsi**: Mengirimkan ringkasan rekap kehadiran harian kelas langsung ke grup WhatsApp wali siswa per kelas menggunakan API Fonnte.

---

### 6. 🔄 Logika kondisi khusus guru izin, absen, atau diganti, serta kegiatan sekolah serentak
*Status: ⏳ Belum dikerjakan*
*   **Deskripsi**: Membuat sistem custom kegiatan di sisi admin yang mempengaruhi pesan notifikasi serta perizinan scan (hanya datang/pulang jika kegiatan serentak tanpa kelas reguler).