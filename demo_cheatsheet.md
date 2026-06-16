# 📄 Cheatsheet Demonstrasi Revisi Sidang Fingersync

Panduan praktis langkah demi langkah untuk mendemokan seluruh hasil revisi sistem Fingersync di hadapan dosen penguji.

---

## 🛠️ Persiapan Demo (Pre-requisites)
1. **Aplikasi Berjalan**: Pastikan server lokal aktif (`npm run dev` / `php artisan serve`).
2. **Akun Uji Coba**:
   * **Admin**: Siapkan username & password admin.
   * **Guru**: Siapkan akun Guru yang mengajar hari ini (dan bertindak sebagai Wali Kelas & Guru Pengganti).
3. **Alat ESP32 / Simulator API**:
   * Jika mendemokan dengan alat fisik, pastikan alat terhubung ke internet.
   * Jika tidak ada alat fisik saat sidang, siapkan **Postman** atau command line curl untuk menyimulasikan tap sidik jari siswa ke endpoint API:
     ```bash
     curl -X POST https://fingersync.satcloud.tech/api/scan \
          -H "Content-Type: application/json" \
          -d '{"id_device": "TKJ1", "fingerprint_id": 1}'
     ```

---

## 📅 POIN 1: Kalender Akademik & Hari Libur (Revisi 3)
*Tujuan: Menunjukkan integrasi API Hari Libur Nasional dan proteksi alat agar tidak menerima absen pada hari libur.*

### Langkah-langkah Demo:
1. **Manajemen Admin**:
   * Login sebagai **Admin**.
   * Buka sidebar, pilih menu **Hari Libur**.
   * Tunjukkan tombol **Sinkronisasi API**. Klik tombol tersebut untuk membuktikan data hari libur nasional (PHBN/PHBI) langsung ditarik otomatis dari API eksternal dan tersimpan di tabel.
   * Tunjukkan tombol **Tambah Hari Libur Manual** untuk menambahkan libur sekolah khusus (misal: Libur Akhir Semester).
2. **Uji Coba Proteksi Alat (Paling Penting!)**:
   * Tambahkan satu libur sekolah manual pada tanggal **hari ini**.
   * Lakukan simulasi scan sidik jari siswa (fisik atau via simulator POST/Curl).
   * **Hasil yang Diharapkan**:
     * Alat ESP32 akan menolak scan dan layar LCD menampilkan:
       ```
       AKSES DITOLAK!
       Hari Libur: [Nama Libur]
       ```
     * Respons API server mengembalikan `"status": "ERROR"` dengan nama hari libur terkait.
   * *Hapus/nonaktifkan kembali libur hari ini setelah mendemokan bagian ini agar uji coba selanjutnya berjalan lancar.*

---

## 📊 POIN 2: Log Presensi & Filter Kelas di Dashboard (Revisi 2)
*Tujuan: Menunjukkan kolom nama kelas dan filter kelas terintegrasi langsung pada tabel Log Presensi Terbaru di panel dashboard.*

### Langkah-langkah Demo:
1. **Dashboard Admin**:
   * Login sebagai **Admin**.
   * Buka halaman **Dashboard**, gulir ke card **"Log Presensi Terbaru"**.
   * Tunjukkan adanya kolom **Kelas** baru yang menampilkan nama kelas asal siswa secara instan.
   * Gunakan dropdown **Filter Kelas** di pojok kanan atas tabel. Pilih salah satu kelas (misal: `X TKJ 1`).
   * Tunjukkan bahwa log otomatis menyaring data scan 5 siswa terakhir dari kelas tersebut saja.
   * Klik tombol **"Lihat Semua Data"** di samping dropdown, dan tunjukkan bahwa sistem otomatis mengarahkan ke halaman histori presensi lengkap dengan filter kelas yang sama langsung terpilih.
2. **Dashboard Guru**:
   * Login sebagai **Guru**.
   * Tunjukkan card **"Log Presensi Terbaru"** yang serupa, tetapi dropdown **Filter Kelas** hanya memuat kelas-kelas yang diajar atau diwalikan oleh guru tersebut.
   * Klik **"Lihat Semua Data"** dan tunjukkan tautan berhasil mengarah ke riwayat absensi kelas terfilter milik guru.

---

## 📈 POIN 3: Akumulasi Jumlah AIS & Laporan SP (Revisi 4)
*Tujuan: Menunjukkan kolom akumulasi ketidakhadiran (Alpha + Izin + Sakit) untuk dasar pengajuan Surat Peringatan (SP) siswa.*

### Langkah-langkah Demo:
1. **Rekap Wali Kelas (Tampilan Web)**:
   * Login sebagai **Guru** (yang berstatus Wali Kelas).
   * Buka menu **Wali Kelas** (Rekap Kehadiran Bulanan).
   * Tunjukkan kolom baru paling kanan bernama **"Jumlah AIS"**.
   * Jelaskan bahwa kolom ini otomatis menjumlahkan total ketidakhadiran siswa (`Alpha` + `Izin` + `Sakit`) pada bulan berjalan dan diberi badge berwarna **merah (*rose*)** tebal agar wali kelas bisa langsung melihat siswa mana yang sudah melampaui batas toleransi absen dan layak diberikan Surat Peringatan (SP).
2. **Laporan Bulanan (Hasil Export PDF)**:
   * Klik tombol **Export PDF** pada halaman rekap tersebut.
   * Tunjukkan pada file PDF yang diunduh:
     * Terdapat kolom baru bertuliskan **"AIS"** di sebelah kanan kolom `A` (Alpa).
     * Sel nilai AIS diblok dengan warna **merah muda (*rose*)** dan teks tebal agar kontras saat dicetak.
     * Tunjukkan keterangan legenda di bagian bawah tabel PDF yang menjelaskan singkatan **AIS**.

---

## 💬 POIN 4: Notifikasi Rekap Harian ke Grup WA Kelas (Revisi 5)
*Tujuan: Menunjukkan efisiensi pengiriman rekap harian secara kolektif ke Grup WhatsApp kelas untuk menghemat kuota Fonnte.*

### Langkah-langkah Demo:
1. **Menghubungkan Kelas dengan Grup WA**:
   * Login sebagai **Admin**.
   * Masuk ke menu **Data Kelas**, lalu klik **Edit** pada salah satu kelas.
   * Tunjukkan select dropdown **"Hubungkan ke Grup WhatsApp Wali Murid"**. Jelaskan bahwa daftar grup ditarik secara dinamis dari API Fonnte Anda (`fetch-group`).
   * Pilih grup uji coba yang sudah disiapkan, lalu klik **Simpan**.
2. **Uji Coba Kirim Rekap Sore (Simulasi Command)**:
   * Buka terminal di laptop Anda, jalankan perintah pengiriman rekap harian:
     ```bash
     php artisan absensi:rekap-sore
     ```
   * **Hasil yang Diharapkan**:
     * Sistem mendeteksi kelas yang memiliki grup WA terdaftar dan menyusun rekap seluruh kehadiran siswa kelas tersebut ke dalam satu pesan terformat.
     * Tunjukkan pesan WhatsApp masuk di grup uji coba (rekap kolektif rapi).
     * Jelaskan logika optimasi: jika jumlah siswa > 10, sistem otomatis memecahnya menjadi beberapa bagian pesan agar tidak melebihi batas panjang karakter WhatsApp.
     * Untuk kelas yang belum dihubungkan ke grup WA, sistem otomatis menggunakan mekanisme *fallback* (mengirim laporan individu ke nomor orang tua siswa satu per satu).

---

## 🔄 POIN 5: Kondisi Khusus Guru & Kegiatan Serentak (Revisi 6)
*Tujuan: Menunjukkan fleksibilitas sistem ketika KBM reguler terganggu oleh kegiatan serentak sekolah atau ketidakhadiran guru.*

### Skenario 5.1: Kegiatan Sekolah Serentak (Misal: Ujian/PORSENI)
1. **Pendaftaran Kegiatan**:
   * Login sebagai **Admin**, buka menu **Kegiatan Sekolah**.
   * Tambahkan kegiatan baru: nama *"Ujian Akhir Semester"*, tipe *"Serentak"*, tanggal hari ini, jam scan datang `06:30 - 09:00`, jam scan pulang `12:00 - 16:00`.
2. **Uji Coba Scan Siswa**:
   * Lakukan simulasi scan siswa pada waktu pagi hari.
   * **Hasil yang Diharapkan**:
     * KBM Reguler dan batas ruangan kelas dibypass. Siswa berhasil absen datang.
     * Tampilan LCD ESP32:
       ```
       ABSENSI BERHASIL!
       [Nama Siswa]
       Status: Hadir (Datang)
       Kegiatan: Ujian Akhir
       ```
     * Pesan WA Orang Tua: *"Ananda [Nama] telah tiba di sekolah untuk mengikuti kegiatan Ujian Akhir Semester pada jam [Waktu] WIB."*
     * Lakukan scan pada siang hari (dalam rentang pulang) -> status tercatat sebagai scan **Pulang** dengan notifikasi khusus pulang kegiatan.
3. **Verifikasi Data Presensi & PDF Laporan**:
   * Buka menu **Presensi** di panel Admin.
   * Pilih kelas yang terdaftar untuk siswa bersangkutan (pencarian filter kelas menggunakan data kelas asal siswa).
   * Tunjukkan baris presensi bermutu tinggi dengan badge oranye khusus **"Kegiatan: Ujian Akhir Semester"** beserta tipe scan (Datang/Pulang).
   * Cari nama siswa di kolom **Cari Siswa** untuk membuktikan fungsionalitas pencarian nama/NIS secara responsif.
   * Klik tombol **Export PDF**, pilih bulan berjalan, lalu unduh PDF.
   * Buka file PDF dan tunjukkan tabel rekap khusus bernama **"Kegiatan Sekolah (Serentak)"** yang hanya menampilkan kolom-kolom tanggal kegiatan sekolah serentak tersebut, dengan status **H** bagi siswa yang hadir scan kegiatan.

---

### Skenario 5.2: Guru Berhalangan Hadir (Izin / Absen)
1. **Pendaftaran Kondisi**:
   * Login sebagai **Admin**, buka menu **KBM Khusus Guru**.
   * Tambahkan kondisi: pilih jadwal KBM hari ini, tanggal hari ini, status **Izin** (atau **Absen**), isi tugas mandiri: *"Mengerjakan LKS halaman 40"*.
2. **Uji Coba Scan & Dampak**:
   * Lakukan simulasi scan siswa pada jam pelajaran tersebut.
   * **Hasil yang Diharapkan**:
     * Siswa tetap bisa absen masuk (Hadir/Terlambat).
     * Tampilan LCD ESP32 berubah menginfokan status kelas:
       ```
       KBM Mandiri (Izin)
       [Nama Siswa]
       Status: Hadir
       [Nama Mapel] (Mandiri)
       ```
     * Pesan WA Orang Tua menginfokan kondisi guru: *"...Guru mapel [Mapel] sedang berhalangan hadir. Ananda belajar mandiri di kelas (Tugas: Mengerjakan LKS halaman 40)."*
     * Di Dashboard Guru yang bersangkutan, jadwal kelas tersebut ditandai dengan badge merah bertuliskan **"Anda Izin (Tugas Mandiri)"**.

---

### Skenario 5.3: Guru Digantikan (Substitute Teacher)
1. **Pendaftaran Guru Pengganti**:
   * Login sebagai **Admin**, pada menu **KBM Khusus Guru**, ubah status menjadi **Diganti** dan pilih **Guru B** sebagai Guru Pengganti.
2. **Uji Coba Dashboard & Hak Akses**:
   * Login sebagai **Guru Utama (Guru A)** -> Jadwal tersebut di dashboard ditandai sebagai **"Digantikan"** dan tombol "Lihat Presensi" dinonaktifkan.
   * Login sebagai **Guru Pengganti (Guru B)**:
     * Di dashboard Guru B, otomatis muncul card jadwal baru bertuliskan **"Jadwal Mengajar Tambahan (Guru Pengganti)"**.
     * Guru B memiliki hak akses penuh untuk mengklik **Lihat Presensi** dan mengubah kehadiran siswa kelas tersebut secara manual khusus pada hari itu.
   * Lakukan scan siswa pada jam tersebut -> LCD menampilkan *"Guru Pengganti: [Nama Guru B]"*.
   * Notifikasi WA Orang Tua: *"...KBM hari ini didampingi oleh Guru Pengganti [Nama Guru B]."*
