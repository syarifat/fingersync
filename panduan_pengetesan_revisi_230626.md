# 🧪 Panduan Pengetesan Fitur Baru & Revisi (23 Juni 2026)

Dokumen ini berisi panduan langkah demi langkah untuk menguji seluruh fitur baru dan perbaikan yang telah selesai diimplementasikan berdasarkan revisi terbaru.

---

## 📅 Fitur 1: Kolom Ruang (Ganti Device) & Lokalisasi Detail AIS

### A. Verifikasi Kolom Ruang di Menu Riwayat Presensi
1. Login ke panel Admin (**Username:** `admin`, **Password:** `password`).
2. Masuk ke menu **Manajemen Data Presensi** (`/admin/presensi`).
3. Perhatikan kolom **Ruang** (sebelumnya bernama **Device**).
4. Pastikan kolom menampilkan nama ruangan asli (contoh: `Lab TKJ 1`) yang diambil dari relasi `$row->device->ruangan->nama_ruangan`.
5. Jika presensi diinput manual oleh sistem/admin, kolom harus secara otomatis menampilkan fallback `Manual/Sistem`.

### B. Verifikasi Detail AIS Siswa
1. Pada menu **Manajemen Data Presensi**, cari baris siswa lalu klik tombol **Detail AIS** di kolom Aksi.
2. Di halaman Detail AIS (`/admin/presensi/siswa/{id}/detail-ais`):
   * **Hari & Tanggal:** Pastikan berformat bahasa Indonesia penuh (contoh: `Selasa, 23 Juni 2026` bukan `Tuesday, 23 June 2026`).
   * **Jam Scan:** Pastikan teks sub-info `"Jam Scan: ..."` di bawah hari dan tanggal sudah dihapus sepenuhnya.
   * **Kolom Ruang:** Pastikan kolom di tabel detail bertuliskan **Ruang** dan menampilkan nama ruangan/lokasi (bukan ID device/nama device).

---

## 📊 Fitur 2: Filter Status Dinamis & Akumulasi AIS

### A. Filter Status di Riwayat Presensi
1. Buka kembali halaman **Manajemen Data Presensi**.
2. Pada bagian Filter, cari dropdown **Status**.
3. Pastikan pilihan status di dalam dropdown diambil secara dinamis dari database (bukan hardcoded).
4. Pilih salah satu status (misal: `Alpha` atau `Sakit`), lalu klik **Filter**. Pastikan tabel memuat data presensi sesuai status yang dipilih saja.

### B. Akumulasi Kolom AIS
1. Di halaman **Manajemen Data Presensi**, perhatikan kolom **AIS** di sebelah kolom Nama Siswa.
2. Pastikan kolom menampilkan akumulasi total ketidakhadiran siswa tersebut (`Alpha/Alpa` + `Izin` + `Sakit`) pada tahun ajaran aktif dalam bentuk badge merah yang mencolok.

---

## 🔄 Fitur 3: KBM Khusus Guru (Izin KBM & Validasi Tanggal)

### A. Pembatasan Hari di Form Input (Realtime Javascript)
1. Masuk ke menu **KBM Khusus Guru** (`/admin/kbm-khusus`) -> Klik **Tambah Baru**.
2. Pilih salah satu jadwal pelajaran di dropdown **Jadwal Pelajaran** (misal: jadwal yang berlangsung setiap hari **Senin**).
3. Klik input tanggal **Tanggal Berhalangan**.
4. **Hasil yang Diharapkan:** Kalender pemilih tanggal hanya memperbolehkan Anda memilih tanggal yang jatuh pada hari **Senin** (hari-hari selain Senin akan dinonaktifkan/disabled secara otomatis).

### B. Logika Respon Mesin Absensi (Izin dengan Tugas vs Libur)
1. Buat jadwal KBM Khusus untuk hari ini:
   * **Status:** Izin
   * **Berikan Tugas:** Centang (Aktif)
   * Simulasikan scan IoT (atau via Postman/Curl). **Hasil:** Respons sukses dengan pesan `"Mengerjakan Tugas yang Diberikan"`.
2. Edit jadwal KBM Khusus tersebut:
   * **Berikan Tugas:** Hilangkan centang (Nonaktif)
   * Simulasikan scan IoT. **Hasil:** Respons error/ditolak dengan pesan `"Jadwal Libur, Guru Sedang Izin"`.

---

## 💬 Fitur 4: Konsep Baru Notifikasi WhatsApp (Grup Kelas)

Seluruh notifikasi realtime individu/personal ke orang tua telah dihapus. Notifikasi kini dikirim secara terjadwal langsung ke masing-masing Grup WhatsApp kelas wali murid (yang dikonfigurasi melalui edit Kelas).

### A. Pengujian via Endpoint Web (Trigger Manual)
Anda dapat memicu pengiriman pesan WhatsApp terjadwal secara manual dengan mengakses URL berikut di browser Anda:

1. **Rekap Hadir Pagi (Pukul 08:00 WIB):**
   * **URL:** `http://127.0.0.1:8000/cron/hadir-pagi/FINGERSYNC-SECURE-123`
   * **Hasil:** Mengirim daftar siswa yang *Sudah Hadir* (beserta jam scan) dan *Belum Hadir* diurutkan berdasarkan abjad A-Z ke grup WA kelas.
2. **Rekap Sore & Auto Alpha (Pukul 16:00 WIB):**
   * **URL:** `http://127.0.0.1:8000/cron/rekap-sore/FINGERSYNC-SECURE-123`
   * **Hasil:** Melakukan auto-alpha untuk siswa yang bolos, lalu mengirim ringkasan KBM harian ke grup WA.
3. **Rekap Pulang Sekolah (Pukul 17:30 WIB):**
   * **URL:** `http://127.0.0.1:8000/cron/pulang-sore/FINGERSYNC-SECURE-123`
   * **Hasil:** Mengompilasi data kepulangan (siswa yang sudah scan pulang vs yang belum/bolos) diurutkan A-Z ke grup WA.
   * *Catatan: Jendela scan pulang sekarang diperluas dari jam selesai pelajaran terakhir hingga pukul 17:30 WIB.*

### B. Pengujian via Script CLI PHP (Simulasi Komplit)
Anda dapat menjalankan script simulasi otomatis di terminal untuk menguji ketiga jenis pesan di atas sekaligus:
```bash
php scratch/test_new_messages.php
```
Periksa output terminal untuk memastikan data presensi virtual berhasil dibuat dan rekap berhasil dikirim ke API Fonnte.
