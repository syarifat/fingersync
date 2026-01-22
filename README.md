# 🛡️ FingerSync - Smart Presence System

<p align="center">
  <img src="public/img/logo.png" alt="FingerSync Logo" width="150">
</p>

**FingerSync** adalah sistem manajemen presensi sekolah modern yang mengintegrasikan teknologi biometrik (sidik jari) dan IoT dengan platform berbasis Cloud. Dirancang untuk mempermudah sekolah dalam memantau kehadiran siswa, guru, dan staf secara *real-time*, akurat, dan anti-titip absen.

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-B73BFE?style=for-the-badge&logo=vite&logoColor=white)

---

## ✨ Fitur Utama

* **📊 Dashboard Interaktif**: Monitoring statistik kehadiran harian secara *real-time*.
* **🏫 Manajemen Akademik Lengkap**:
    * Pengelolaan Data Siswa, Guru, dan Karyawan.
    * Manajemen Rombel (Rombongan Belajar) & Kenaikan Kelas.
    * Plotting Guru Mata Pelajaran.
    * Penjadwalan Otomatis (Hari, Jam, Ruangan).
* **📡 Integrasi IoT**: Mendukung sinkronisasi data dari perangkat *fingerprint* berbasis ESP32/Arduino.
* **📆 Filter Tahun Ajaran**: Arsip data presensi yang rapi berdasarkan tahun ajaran aktif.
* **📱 Responsif Mobile**: Tampilan UI/UX yang optimal di Desktop, Tablet, dan Smartphone.
* **📑 Laporan Otomatis**: Rekapitulasi kehadiran yang siap cetak.

---

## 🛠️ Tech Stack

Aplikasi ini dibangun menggunakan teknologi modern untuk memastikan performa dan kemudahan pengembangan:

| Kategori | Teknologi |
| :--- | :--- |
| **Backend Framework** | Laravel 12 |
| **Frontend** | Blade Templates, Alpine.js |
| **Styling** | Tailwind CSS |
| **Database** | MySQL |
| **Build Tool** | Vite |
| **Authentication** | Laravel Breeze |

---

## 🚀 Cara Instalasi (Localhost)

Ikuti langkah-langkah berikut secara berurutan untuk menjalankan proyek di komputer lokal Anda:

### 1. Prasyarat Sistem
Pastikan komputer Anda sudah terinstal aplikasi berikut:
* **PHP** >= 8.2
* **Composer**
* **Node.js** & **NPM**
* **MySQL** (via XAMPP, Laragon, atau Docker)

### 2. Clone Repository
Unduh source code proyek dan masuk ke dalam direktorinya:

```bash
git clone https://github.com/syarifat/fingersync.git
cd fingersync
```

### 3. Install Dependencies
Install seluruh library yang dibutuhkan untuk Backend (Laravel) dan Frontend:

```bash
# Install library PHP
composer install

# Install library JavaScript
npm install
```

### 4. Konfigurasi Environment (.env)
Duplikat file contoh konfigurasi menjadi file `.env` aktif:

**Windows:**
```bash
copy .env.example .env
```

**Mac/Linux:**
```bash
cp .env.example .env
```

⚠️ **PENTING:** Buka file `.env` dengan text editor, lalu sesuaikan konfigurasi database.
Pastikan Anda sudah membuat database kosong bernama `fingersync` di MySQL/phpMyAdmin.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fingersync
DB_USERNAME=root
DB_PASSWORD=
```

*(Sesuaikan DB_USERNAME dan DB_PASSWORD dengan settingan lokal Anda)*

### 5. Generate Application Key
Buat kunci enkripsi unik untuk aplikasi:

```bash
php artisan key:generate
```

### 6. Setup Database (Migrate & Seed)
Jalankan perintah ini untuk membuat tabel database dan mengisi data awal (Admin default, dll):

```bash
php artisan migrate:fresh --seed
```

### 7. Jalankan Aplikasi
Buka **dua terminal** berbeda agar fungsi backend dan frontend berjalan bersamaan:

**Terminal 1 (Server Laravel):**
```bash
php artisan serve
```

**Terminal 2 (Vite / Frontend):**
```bash
npm run dev
```

### 8. Akses Aplikasi
Buka browser dan kunjungi alamat berikut:

```
http://localhost:8000
```
