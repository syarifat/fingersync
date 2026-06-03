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
* **Format Teks WhatsApp yang Dikirim:**
  ```text
  Halo Ayah/Ibu dari *[Nama Siswa]*,

  Kami menginformasikan bahwa ananda telah *Tiba di Sekolah* dan melakukan presensi pertama pada jam *[Jam Scan (Format H:i)] WIB*.

  Semoga ananda belajar dengan baik hari ini. Terima kasih.
  ```
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
* **Format Teks WhatsApp yang Dikirim:**
  ```text
  ⚠️ *Peringatan Anomali Kehadiran*

  Kelas: *[Nama Kelas]*
  Mapel: *[Nama Mapel]*
  Kondisi: Mapel sudah berjalan > 1 Jam, namun siswa berikut belum melakukan presensi sama sekali:

  ➖ [Nama Siswa 1]
  ➖ [Nama Siswa 2]

  Mohon tindak lanjut dari Wali Kelas / Guru BK.
  ```
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
* **Format Teks WhatsApp yang Dikirim:**
  ```text
  📝 *LAPORAN PRESENSI HARIAN*
  ----------------------------------
  Nama: *[Nama Siswa]*
  Tanggal: [Tanggal Terformat (Contoh: 24 Mei 2026)]
  ----------------------------------

  Berikut detail kehadiran ananda hari ini:

  [Icon Kehadiran] *[Jam Mulai]* | [Nama Mapel]
  Status: _[Status Kehadiran]_

  ----------------------------------
  Demikian laporan harian ini kami sampaikan. Terima kasih atas perhatian Ayah/Ibu.
  ```
  *(Catatan Aturan Icon Kehadiran & Status Kehadiran):*
  * **Hadir** / **Terlambat** -> Icon: ✅ | Status: _Hadir_ atau _Terlambat_
  * **Sakit** -> Icon: 🤒 | Status: _Sakit_
  * **Izin** -> Icon: ✉️ | Status: _Izin_
  * **Alpha** -> Icon: ❌ | Status: _Alpha / Tanpa Keterangan_ (Otomatis diganti statusnya menjadi Alpha di database jika tidak melakukan presensi seharian)

---

## Kesimpulan Arsitektur
Dengan pemisahan logika antara API *Synchronous* (Real-time Arrival) dan proses *Asynchronous* via Cronjob (Anomali & Rekap Sore), *FingerSync* mampu menjaga *resource* server (CPU dan RAM) tetap ringan meskipun harus memproses ribuan data log absensi setiap harinya. Penggunaan *Fonnte* sebagai eksekutor pesan juga memastikan tidak adanya *bottleneck* (penumpukan antrean) di level aplikasi utama.
