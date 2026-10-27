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

20 Oktober 2026 — Unduh rincian potongan per karyawan: popup rincian Form Gaji dan rekap gaji tersimpan menyediakan Unduh rincian PDF, dengan indikator proses dan hasil Swal. PDF A4 landscape memuat seluruh tanggal periode, jadwal/jam aktual dalam zona perusahaan, keterangan/menit, rumus, penyesuaian dan total sebelum/sesudah batas 25%. Pratinjau membaca snapshot lokal; rekap membaca JSON beku, bukan master atau API terbaru. Endpoint mengikuti autentikasi/role Form Gaji; data tidak tersedia mengembalikan 404. Tidak menambah dependensi/migrasi atau build mobile, SAIND tetap unggahan manual oleh pemilik.


26 Oktober 2026 — Integrasi SAIND dialihkan dari /var/www/dev_saind (dev-saind.ds-groups.com) ke /var/www/saind (saind-hauling.ds-groups.com), sesuai izin pemilik. Token yang sama dipindahkan secara privat di server, tanpa dicetak atau disalin ke Git. Dev ATTENDANCE_ENABLED=false dengan cache konfigurasi dibangun ulang; production enabled=true, URL https://absensi.ds-groups.com, send_immediately=true. Sebanyak35 metadata versi sinkronisasi diteruskan sebelum aktivasi; backup terbatas metadata sync tanpa token/.env ada di production storage/app/private/backups/saind-integration-handover-20261026T082703Z/sync-before.json. Tidak menyalin atau mengubah record pegawai asal. Rekonsiliasi resmi memeriksa35pegawai, mengirim1perubahan jabatan Rimbo Satibi source38 menjadi SECURITY (versi2), antrean/error0. SAIND absensi company1 tetap35pegawai(19aktif/16nonaktif), ID/nama/status cocok; IUP company2 tetap15 dan review company3 tetap1. Observer Karyawan saved/deleted terdaftar, pengiriman afterCommit; retry setiap menit dan rekonsiliasi lima menit. Cron production sudah ada dan log menunjukkan eksekusi per menit, tidak dibuat cron ganda atau mengubah cron aplikasi lain. Web dev/production HTTP302 normal ke login. Tes AttendanceConnectorTest22lulus/117assertions. Tidak membuat payroll atau mengubah perangkat/wajah/jadwal/cuti/absensi maupun build mobile.


26 Oktober 2026 — Preferensi UI: seluruh input nominal pengembangan berikutnya memakai Cleave.js yang sudah tersedia, pemisah ribuan titik dan angka rupiah penuh. Kirim nilai mentah tanpa pemisah ke backend; validasi server tetap wajib. Halaman dasar minimum BPJS mengikuti pola ini, bukan input number polos.


26 Oktober 2026 — Pengaturan dasar minimum BPJS Kesehatan tahunan selesai lokal. Keputusan pemilik: satu wilayah per project, tanpa input wilayah; tahun berlaku, jenis UMK/UMP, nominal minimum dan referensi wajib. Menu Database menuju halaman baru; GET dan POST dilindungi role admin/su, bukan hanya menu disembunyikan. Tabel bpjs_health_minimums memiliki unique year, nominal rupiah penuh1..12juta, updated_by dan timestamps. Tidak ada nilai minimum asumsi yang diisikan ke database operasional lokal. Payroll memakai tahun periode, floor tahunan dan ceiling12juta untuk Kesehatan perusahaan4%/pegawai1%, tanpa membatasi TK. Pratinjau yang belum memiliki pengaturan diberi peringatan; POST eksekusi ditolak sebelum lock absensi dan perubahan kas, termasuk jika tombol dilewati. Nilai pengaturan dikunci/dibaca ulang di transaksi final. Memo per-request mencegah query berulang per pegawai. SAIND PDF/Excel juga memakai tahun payroll. Rekap tersimpan tidak dihitung ulang. Migrasi hanya tabel baru telah diterapkan lokal, tidak deploy VPS.

UI diseragamkan pada SAIND/IUP: ringkasan tarif, form tahun, status tersimpan, tabel pencarian dengan header table-success, Font Awesome existing, SweetAlert konfirmasi/hasil/loading, dan Cleave.js untuk ribuan titik dengan field canonical integer. SVG Bootstrap Icons shield-plus gratis/MIT dipasang identik dengan pemberitahuan lisensi di public/images/bpjs-health.LICENSE.txt; sumber https://github.com/twbs/icons/blob/main/icons/shield-plus.svg. Aturan selalu memakai Cleave dicatat di AGENTS.md atas instruksi pemilik. Verifikasi akhir: SAIND46tes/388assertions dan IUP64tes/513assertions lulus, Pint/diffcheck/Blade/JS lulus. Browser IUP memverifikasi halaman, format4.000.000 dan konfirmasi dibatalkan; tidak menyimpan nilai contoh atau menjalankan gaji. Tampilan memuat2query pada pemeriksaan browser. Pengujian SAIND mencakup role admin/su, penolakan user, tahunbelumdiatur, perubahan tahun, batasfloor/cap, kas dan rekap immutable. Composer.lock SAIND yang telah berubah sebelumnya tidak disentuh.


### 2026-10-26 — Isolasi integrasi saat pengujian

Pengujian menonaktifkan integrasi secara default, menghapus kredensial nyata dari konfigurasi tes, menggunakan target `https://attendance.test`, dan menolak HTTP tanpa fake. Tes konektor tetap dapat mengaktifkan integrasi menggunakan endpoint serta respons tiruan eksplisit.
