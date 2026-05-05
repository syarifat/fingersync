# Dokumentasi Alur Notifikasi WhatsApp - FingerSync
*Disusun sebagai bahan panduan presentasi teknis.*

---

## Pendahuluan
Sistem presensi **FingerSync** tidak hanya bertindak sebagai pencatat kehadiran pasif, melainkan sebuah ekosistem pintar yang secara proaktif mendistribusikan informasi ke tiga pilar utama: **Sekolah (Guru BK/Wali Kelas), Siswa, dan Orang Tua**. 

Proaktivitas ini diwujudkan melalui **3 Pilar Logika Pengiriman WhatsApp Otomatis** yang diatur sedemikian rupa agar tidak melakukan *spamming*, namun tetap akurat.

---

## 1. Notifikasi *Real-time* Kedatangan Siswa
Fitur ini bertujuan untuk menjawab kekhawatiran orang tua dengan memberikan kepastian langsung bahwa anak telah tiba dengan selamat di kelas.

* **Metode Trigger:** Instan (API Request)
* **File Controller:** `app/Http/Controllers/Api/DeviceController.php`
* **Penerima Pesan:** Orang Tua Wali Murid
* **Alur Logika & Algoritma:**
  1. Alat ESP32 mendeteksi sidik jari dan mengirimkan *payload* berupa `id_device` dan `fingerprint_id` ke *endpoint* API Laravel.
  2. Sistem mencari relasi siswa berdasarkan ID sidik jari dan ruangan alat.
  3. Sistem memvalidasi apakah ada Jadwal Pelajaran yang sedang aktif.
  4. **Logika Anti-Spam (*Gatekeeper*):** Sistem mengecek tabel `Presensi`. **HANYA JIKA** ini adalah tap absensi PERTAMA anak tersebut pada hari ini, maka *request* tembak API Fonnte (WhatsApp) dijalankan. Jika anak tersebut menempelkan jarinya lagi di pergantian jam pelajaran, data kehadiran akan tersimpan ke database, tetapi WhatsApp *tidak* akan dikirimkan lagi.

---

## 2. Razia Anomali Kehadiran (Sistem Deteksi Bolos)
Membantu penegakan kedisiplinan sekolah. Seringkali guru terlalu sibuk mengajar sehingga tidak menyadari ada anak yang "hilang" (bolos) atau terlambat sangat parah di jam pelajarannya.

* **Metode Trigger:** Terjadwal (Cronjob / Scheduler)
* **Waktu Eksekusi:** Setiap 30 Menit sekali selama jam sekolah berlangsung.
* **File Command:** `app/Console/Commands/CekAnomaliAbsensi.php`
* **Penerima Pesan:** Wali Kelas & Guru Bimbingan Konseling (BK)
* **Alur Logika & Algoritma:**
  1. Ketika Cronjob berjalan, sistem memutar jarum jam virtual ke belakang dan mencari: *"Apakah ada jadwal mapel yang dimulai antara 60 menit hingga 90 menit yang lalu?"* (Memberikan toleransi 1 jam keterlambatan/izin ke toilet).
  2. Jika ditemukan jadwal yang cocok, sistem menarik seluruh daftar siswa di rombel/kelas tersebut.
  3. Sistem membandingkan absen setiap siswa secara silang dengan tabel `Presensi`. Siswa yang datanya *null* (kosong) di mapel tersebut dimasukkan ke dalam daftar *blacklist* (array).
  4. **Rapelisasi Pesan:** Daripada mengirim belasan WhatsApp terpisah untuk setiap anak yang membolos, algoritma menggabungkannya dalam 1 daftar (*implode*) lalu menembakkan pesannya kepada nomor Wali Kelas dan Guru BK terkait agar ditindaklanjuti.

---

## 3. Laporan Rapor Kehadiran Harian (*End-of-Day Recap*)
Memberikan transparansi yang mutlak. Orang tua memiliki hak untuk mengetahui rekam jejak anaknya selama seharian di sekolah secara transparan tanpa harus menunggu pembagian rapor semester.

* **Metode Trigger:** Terjadwal (Cronjob / Scheduler)
* **Waktu Eksekusi:** Tepat pukul 16:00 WIB Setiap Hari Kerja
* **File Command:** `app/Console/Commands/KirimRekapSore.php`
* **Penerima Pesan:** Orang Tua Wali Murid
* **Alur Logika & Algoritma:**
  1. Tepat saat jam pulang, *Cronjob* menyalakan skrip rekapitulasi.
  2. Sistem mengambil seluruh entitas Siswa yang berstatus *Aktif*.
  3. Melalui relasi Eloquent (`siswa->rombelKelas`), sistem merekonstruksi urutan jadwal mata pelajaran anak tersebut khusus untuk hari ini.
  4. Sistem melakukan *looping* untuk membandingkan jadwal dengan tabel absensi, lalu melabeli status pada setiap mapel (Contoh: Matematika -> **Hadir**, Fisika -> **Alpa**, Bahasa Inggris -> **Hadir**).
  5. Seluruh rekap dirender menjadi format teks WhatsApp yang rapi dan dikirimkan kepada Orang Tua.

---

## Kesimpulan Arsitektur
Dengan pemisahan logika antara API *Synchronous* (Real-time Arrival) dan proses *Asynchronous* via Cronjob (Anomali & Rekap Sore), *FingerSync* mampu menjaga *resource* server (CPU dan RAM) tetap ringan meskipun harus memproses ribuan data log absensi setiap harinya. Penggunaan *Fonnte* sebagai eksekutor pesan juga memastikan tidak adanya *bottleneck* (penumpukan antrean) di level aplikasi utama.
