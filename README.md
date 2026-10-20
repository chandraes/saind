# SAIND - 

![Laravel](https://img.shields.io/badge/Laravel-12.x-red?style=flat-square&logo=laravel)
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
- **Framework**: Laravel 12.x
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

## Integrasi potongan absensi — 20 Oktober 2026

- Form Gaji memakai periode tanggal 26 bulan sebelumnya–25 bulan gaji. Nilai harian dibulatkan dari (gaji pokok + tunjangan jabatan tetap + tunjangan keluarga tetap) / 25. Kerja efektif = durasi shift − durasi istirahat. Potongan hadir lengkap dibulatkan per hari berdasarkan jumlah menit telat setelah toleransi + pulang cepat, dibatasi satu nilai harian. Setengah hari = setengah nilai harian dibulatkan, tanpa tambahan terlambat; tidak masuk = satu nilai harian. Total akhir dibatasi 25% dasar upah, batas dibulatkan ke bawah agar tidak melebihi 25%.
- Tombol Tarik/Perbarui Data Absensi mengambil snapshot lengkap melalui POST `/api/v1/integration/payroll-snapshot`, lalu menyimpannya di `payroll_attendance_periods`. Tabel/popup/PDF/Excel tidak menarik API per baris. Pemetaan wajib cocok ID sumber; seluruh tanggal periode harus tersedia. Hari libur, cuti/sakit disetujui, tidak wajib absen, sebelum masa kerja/pencatatan tidak menghasilkan potongan.
- Pengajuan pending, shift belum selesai, jadwal tidak valid, pegawai belum tersinkron, respons tidak lengkap/gagal, atau revisi berubah memblokir pengesahan saat fitur aktif. Draf yang gagal diperbarui tidak ditimpa dengan nol.
- Sakelar aktif/nonaktif dan penyesuaian per pegawai/tanggal hanya role `su`, dengan alasan wajib dan audit `payroll_attendance_events`. Default migrasi aktif sesuai instruksi pemilik. Jika revisi sumber berubah saat refresh, penyesuaian lama direset. Menonaktifkan tidak menghitung ulang gaji yang sudah tersimpan. Pengesahan kebijakan perusahaan tetap tanggung jawab perusahaan, bukan digantikan aktivasi teknis.
- Simpan Gaji memeriksa fingerprint data gaji/kasbon/snapshot/penyesuaian, memvalidasi revisi remote dan mengunci periode remote menggunakan request UUID tetap, lalu menyimpan rekap, rincian potongan, pembayaran kasbon dan kas besar secara transaksional di SAIND. Jika koneksi/hasil remote belum pasti, status percobaan disimpan dan refresh/sakelar ditahan; ulangi penyimpanan transaksi yang sama. Ini bukan transaksi ACID lintas dua server: jika remote terkunci tetapi SAIND gagal, remote tetap terkunci untuk menjaga keamanan dan transaksi yang sama harus diselesaikan. Tidak ada unlock otomatis.
- Backend absensi harus dimigrasi dan dideploy lebih dahulu sebelum fitur dipakai ke VPS. Implementasi ini belum dideploy. Tidak memerlukan build mobile baru.
- Riwayat/slip gaji menampilkan potongan dari nilai tersimpan, bukan hitung ulang. Tidak menambahkan prorata atau penundaan kasbon otomatis. Pembatasan hukum gabungan jenis pemotongan tidak dianggap otomatis terpenuhi hanya karena saldo pembayaran positif.
- Rincian potongan menggunakan popup DataTable bersama (`public/assets/js/payroll-attendance-detail.js`): hari/tanggal, jadwal dan jam aktual dalam zona perusahaan, telat setelah toleransi/pulang cepat, menit kerja efektif, rumus nominal harian, dan alasan penyesuaian. Ringkasan menampilkan nilai harian, total sebelum batas, batas 25%, dan hasil akhir. Tersedia pada pratinjau serta rekap gaji tersimpan; rekap memakai JSON rincian yang dibekukan ketika gaji disahkan. Semua teks dinamis dibentuk melalui textContent, tanpa menyisipkan HTML pengguna. Tidak melakukan request API saat popup dibuka.

## Pantauan potongan periode berjalan — 20 Oktober 2026

- Tarik/Perbarui Data Absensi dapat dilakukan berulang selama periode 26–25. Snapshot periode diganti, bukan dijumlahkan; tabel, filter Hanya pegawai dengan potongan absensi, ringkasan jumlah/nominal dan rincian membaca salinan lokal. Tidak ada request absensi per baris atau saat membuka rincian.
- Kontrak `monitoring_version: 1` membedakan jadwal mendatang (`upcoming`), shift/batas pulang belum selesai (`in_progress`), dan pengajuan menunggu HR (`pending_leave`). Status tersebut belum menghasilkan potongan. Hari selesai tanpa masuk menjadi tidak masuk; masuk tanpa pulang baru menjadi setengah hari setelah batas pulang lewat. Tanggal shift malam tetap mengikuti tanggal mulai.
- `as_of` menunjukkan waktu server absensi saat snapshot ditarik; angka tetap sementara sampai seluruh periode dan masalah HR selesai. Refresh tidak mengunci periode. Konfirmasi Simpan Gaji tetap menjalankan validasi revisi serta penguncian yang sudah ada. Snapshot lama tanpa versi monitoring wajib diperbarui; backend lama ditolak dengan pesan yang jelas dan tidak menimpa draf.
- Pengujian lokal: 34 tes SAIND / 285 assertions dan 7 tes integrasi backend / 57 assertions lulus, termasuk refresh tanpa penggandaan, filter, pending HR, shift malam/batas pulang, revisi stabil, serta blokir pengesahan periode berjalan. Perubahan ini belum dideploy; deploy backend absensi terlebih dahulu, kemudian SAIND. Tidak ada migrasi atau build mobile baru.

20 Oktober 2026 — Sakelar utama absensi pada Form Gaji: `ATTENDANCE_ENABLED=false` mengalahkan pengaturan potongan database. Kalkulasi potongan menjadi nol dan simpan gaji tidak membutuhkan snapshot/persetujuan HR absensi atau penguncian API. Snapshot lama dan gaji tersimpan tidak dihapus/diubah. Saat env true, sakelar database tetap menentukan aktivasi potongan. Setelah perubahan env pada server yang memakai config cache, jalankan `php artisan config:cache`. Tes mencakup penyimpanan tanpa snapshot dan snapshot pending berpotongan yang diabaikan ketika env false; SAIND belum dideploy (unggahan manual oleh pemilik).
