# SAIND - 

![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.x-purple?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

> Sistem Informasi Manajemen Data berbasis web yang dibangun dengan Laravel untuk mengelola operasional perusahaan, termasuk manajemen karyawan, keuangan, dan laporan.

## 📋 Tabel Konten

- [Fitur](#-fitur)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Instalasi](#-instalasi)
- [Konfigurasi Lingkungan](#-konfigurasi-lingkungan)
- [Struktur Folder](#-struktur-folder)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

## ✨ Fitur

- **Manajemen Pengguna & Karyawan**: Kelola data karyawan dan jabatan.
- **Manajemen Keuangan**: Pantau kas besar dan rekening perusahaan.
- **Multi-bahasa**: Dukungan untuk bahasa Indonesia (`lang/id`).
- **Autentikasi**: Laravel UI untuk sistem login dan registrasi.
- **Generasi PDF**: Cetak laporan menggunakan `Barryvdh/laravel-dompdf`.
- **Debugging**: Dilengkapi `Laravel Debugbar` untuk pengembangan.
- **Input Masking**: Format input otomatis untuk mata uang (`maskMoney JS`).

## 🛠 Teknologi yang Digunakan

### Backend
- **Framework**: Laravel 10.x
- **Bahasa**: PHP 8.x
- **Autentikasi**: Laravel UI
- **PDF**: Barryvdh/laravel-dompdf
- **Debugbar**: Barryvdh/laravel-debugbar

### Frontend
- **Template Engine**: Blade
- **Styling**: Bootstrap, SCSS, CSS
- **JavaScript**: Vanilla JS, Axios
- **Build Tool**: Vite

## 🚀 Instalasi

Ikuti langkah-langkah berikut untuk menginstal aplikasi secara lokal:

1.  **Clone Repository**
    ```bash
    git clone https://github.com/chandraes/saind.git
    cd saind
    ```

2.  **Instal Dependensi PHP**
    ```bash
    composer install
    ```

3.  **Instal Dependensi Node.js**
    ```bash
    npm install
    ```

4.  **Salin File Lingkungan**
    ```bash
    cp .env.example .env
    ```

5.  **Generate Kunci Aplikasi**
    ```bash
    php artisan key:generate
    ```

6.  **Jalankan Migrasi & Seeder**
    ```bash
    php artisan migrate --seed
    ```

7.  **Link Storage**
    ```bash
    php artisan storage:link
    ```

8.  **Build Aset Frontend**
    ```bash
    npm run build
    ```

9.  **Jalankan Server**
    ```bash
    php artisan serve
    ```

    Buka `http://127.0.0.1:8000` di browser Anda.

## ⚙️ Konfigurasi Lingkungan

Pastikan file `.env` Anda telah dikonfigurasi dengan benar, terutama untuk:

```env
DB_DATABASE=nama_database
DB_USERNAME=username
DB_PASSWORD=password
```

## 📁 Struktur Folder
```
saind/
├── app/                 # Logika aplikasi (Controllers, Models, dll.)
├── bootstrap/           # File bootstrap Laravel
├── config/              # File konfigurasi aplikasi
├── database/            # Migrasi dan Seeder database
├── lang/                # File terjemahan (bahasa Indonesia)
├── public/              # Aset publik dan titik masuk aplikasi
├── resources/           # View (Blade), aset frontend, dan bahasa
├── routes/              # Definisi route web dan API
├── storage/             # File yang diunggah dan cache
└── tests/               # Unit dan Feature Tests
```

## 📄 Lisensi
Proyek ini dilisensikan di bawah MIT License. Lihat file LICENSE  untuk detailnya.
