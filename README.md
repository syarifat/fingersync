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

Ikuti langkah-langkah berikut untuk menjalankan proyek di komputer lokal Anda:

### 1. Prasyarat
Pastikan Anda sudah menginstal:
* PHP >= 8.1
* Composer
* Node.js & NPM
* MySQL

### 2. Clone Repository
```bash
git clone [https://github.com/syarifat/fingersync.git](https://github.com/syarifat/fingersync.git)
cd fingersync