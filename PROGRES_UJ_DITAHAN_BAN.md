# Progres UJ Ditahan Ban

Terakhir diperbarui: 29 Oktober 2026. Checkout lokal: `/Users/unsri-perpus/Herd/saind`, branch `main` (Herd `saind.test`). Banyak perubahan maintenance sebelumnya belum di-commit; jangan reset/menimpa perubahan tersebut.

## Keputusan pengguna

- Dana lama disebut **UJ Ditahan Sparepart**, dana baru **UJ Ditahan Ban**. Saldo dan ledger terpisah, per kendaraan/bulan/tahun.
- Keduanya mengikuti flag `vehicles.uj_ditahan` yang sama untuk saat ini.
- Jika flag aktif, nominal Ban vendor–rute wajib > 0; pengeluaran UJ ditolak bila belum diisi. Hal yang sama berlaku untuk Sparepart.
- Ban menggunakan rekening penampung `rekenings.untuk = uang-jalan-ditahan` dan grup WA `kas-uj-ditahan` yang sama dengan Sparepart; pencatatannya tetap terpisah.
- Form penggantian ban luar nantinya memakai saldo UJ Ditahan Ban. Aki/filter/oli tetap memakai saldo Sparepart.
- Pengembangan bertahap untuk menghemat kuota. Jangan lanjut tahap berikut tanpa menyelesaikan dan melaporkan tahap aktif.

## Tahap 1 — selesai

- `vendor_uang_jalans.uj_ditahan_ban` bigint default 0.
- Model/factory `UjDitahanBan`, `UjDitahanBanDetail`; tabel `uj_ditahan_bans`, `uj_ditahan_ban_details` (unik kendaraan/tahun/bulan).
- Migrasi lokal sudah dijalankan: `2026_10_29_102252_add_uj_ditahan_ban_to_vendor_uang_jalans_table.php`, `2026_10_29_102252_create_uj_ditahan_bans_table.php`, `2026_10_29_102253_create_uj_ditahan_ban_details_table.php`.
- Form kesepakatan vendor memakai `StoreVendorUangJalanRequest`, input Cleave dengan field integer terpisah; tabel/label Sparepart dan Ban. Modal Lihat Uang Jalan juga sudah menampilkan keduanya.
- `VendorUjDitahanBanTest`: 14 tes lulus (65 assertions).
- Saat selesai tahap 1, 1.517 kesepakatan lama bernominal Ban 0; ledger Ban kosong. **Isi nominal Ban pada vendor–rute sebelum menjalankan UJ untuk kendaraan flag aktif setelah tahap 2.**

## Tahap 2 — selesai

Tujuan: hitung total UJ = nominal transfer + Sparepart + Ban saat flag aktif; kredit dua ledger dalam transaksi yang sama; void kedua endpoint membalik ke periode asal dan tidak berulang; form dan WA menunjukkan rincian dana yang benar.

Berkas utama: `app/Http/Controllers/FormKasUangJalanController.php` (`get_rute`, `keluar_store`, pesan WA), `app/Http/Controllers/TransaksiController.php` (`processVoid`, pesan WA), `resources/views/billing/kas-uang-jalan/keluar.blade.php`.

Penting: input `nominal_transaksi` adalah nominal transfer yang boleh diubah admin/SU; nominal dasar vendor bukan total yang harus dikurangi lagi. Backend lama menambahkan Sparepart, sehingga tahap 2 menambahkan Ban juga. User biasa wajib sesuai nominal dasar vendor; admin/SU tetap boleh mengubah nominal transfer.

Implementasi:

- `FormKasUangJalanController::get_rute` mengembalikan `uj_ditahan_ban`.
- `keluar_store`: nominal Ban wajib positif untuk flag aktif (termasuk admin/SU). Total kas = transfer + Sparepart + Ban; validasi user biasa dan pengecekan saldo kas memakai total tersebut. Flag nonaktif tidak menambahkan/mencatat kedua dana.
- Kredit Sparepart lama dan `UjDitahanBanService::credit` berada dalam transaksi yang sama dengan Kas UJ, transaksi angkutan, dan ritase. Service mengunci kendaraan dan mencegah kredit ulang transaksi yang sama.
- `TransaksiController::processVoid` dipakai kedua endpoint dan memanggil `UjDitahanBanService::reverse`: pengurangan periode sumber, audit keluar, anti pembalikan ulang; rollback mencakup kas + Sparepart + Ban.
- `keluar.blade.php`: rincian Sparepart/Ban terpisah dan total kotor mencakup keduanya; input transfer menggunakan Cleave dan field integer tersembunyi `nominal_transaksi_raw`.
- WA rincian Ban menggunakan rekening/grup yang sama, tetapi menampilkan saldo Ban saja. Notifikasi transfer keluar/void mengurangi kedua dana dari angka kotor, sehingga nominal transfer benar. `GroupWa` pada keluar_store di-resolve melalui container agar tes tidak mengirim WA nyata.
- Tidak ada migrasi tambahan tahap 2 dan tidak ada transaksi/WA nyata dibuat untuk QA.

Hasil tes terakhir: **110 tes PHP lulus (719 assertions)** untuk lima file di bawah, plus **6 tes JS lulus** (2 perhitungan total UJ dan 4 regresi formatter nominal maintenance). Pint dan `git diff --check` bersih. Tes SQLite belum membuktikan konkurensi dua koneksi MySQL.

```sh
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --compact tests/Feature/UjDitahanBanFlowTest.php tests/Feature/VendorUjDitahanBanTest.php tests/Feature/UjDitahanPencairanTest.php tests/Feature/AkiMaintenanceTest.php tests/Feature/FilterOliGantiTest.php
node --test tests/Unit/UangJalanHeldAmounts.test.cjs
vendor/bin/pint --dirty --format agent
git diff --check
```

`UjDitahanBanFlowTest`: 15 kasus, termasuk nominal Ban kosong untuk user/admin, flag nonaktif, transfer admin/SU, larangan manipulasi transfer user, saldo kas cukup untuk dua dana, lookup rute, dua endpoint void, serta rollback ketika insert Ban gagal saat keluar/void.

Peringatan aktivasi: **semua kesepakatan lama dimigrasikan dengan Ban=0. Isi nominal Ban vendor–rute sebelum memakai form keluar untuk flag aktif; backend sekarang menolak nominal Ban yang belum diisi sesuai keputusan pengguna.**

Audit tambahan `TransaksiVoidTest.php`: ekspektasi judul WA disesuaikan menjadi Sparepart. Hasil audit ulang: 11 tes lulus, 2 gagal. Dua kegagalan baseline rekening BRI/456 vs BCA/123 tetap ada; kebijakan rekening tidak diubah. Baris assertion sekarang sekitar 150, bukan 146 karena penambahan setup tabel Ban.

## Tahap 3 — selesai

Pembaruan 29 Oktober 2026:

- Halaman `/billing/uj-ditahan-ban`, detail `/{account}`, pencairan POST `/{account}/cairkan`, bukti privat `/bukti/{detail}`, serta `/rekap/uj-ditahan-ban` tersedia. Menu Billing/Rekap memiliki pintasan Ban terpisah.
- `UjDitahanBanController`, `WithdrawUjDitahanBanRequest`, `UjDitahanBanPayoutService` dan views `billing/uj-ditahan-ban` + `public/assets/js/uj-ditahan-ban.js` mengelola fitur baru.
- Daftar saldo aktif (`saldo != 0`) dan rekap selesai (`saldo = 0`) memakai filter bulan/tahun, default bulan berjalan, Select2, DataTables server-side infinite scroll, batas maksimal 100 baris per permintaan, pencarian, grand total hasil filter dan head `table-success`.
- Detail menampilkan driver, tanggal, uraian, masuk/keluar, rekening dan bukti PDF. Ringkasan detail adalah total periode, footer mengikuti pencarian mutasi.
- Pencairan user/admin/SU: nominal integer positif, saldo cukup, rekening tujuan lengkap, PDF maksimal 5 MB, dan saldo Ban bulan sebelumnya wajib habis. Vehicle dan master dikunci dalam DB transaction; ledger + saldo atomik. Dana Sparepart tidak tersentuh.
- Migrasi `2026_10_29_121916_add_request_token_to_uj_ditahan_ban_details_table.php` **sudah diterapkan lokal**, hanya menambah nullable UUID unik. Token mencegah kirim ulang pencairan. File privat disimpan setelah validasi saldo dan dibersihkan bila transaksi gagal.
- Bukti PDF diakses melalui route terproteksi; tidak dibuka lewat public storage. Notifikasi WA menggunakan grup lama, dijalankan setelah commit; exception tidak mengubah transaksi yang sudah berhasil menjadi pesan gagal pencairan.
- Label dana lama di menu, halaman saldo/rekap, checkout dan otorisasi/rekap maintenance menjadi **UJ Ditahan Sparepart**. Flag kendaraan diberi label **UJ Ditahan Sparepart & Ban** karena masih mengaktifkan kedua dana.
- Tes `UjDitahanBanManagementTest`: 24 kasus termasuk replay, nominal invalid, saldo kurang, saldo bulan lama, saldo persis, rollback/file cleanup, kegagalan WA, hak akses, upload/rekening wajib, pencarian dan bounded loading. Cakupan cutoff: hanya admin/SU, rekening driver lengkap, seluruh saldo positif menjadi keluar, saldo nol/negatif ditolak, replay ditolak termasuk setelah kredit baru, kegagalan insert rollback, kegagalan WA tidak membatalkan transaksi tersimpan.
- Verifikasi gabungan terakhir: **134 tes PHP lulus (845 assertions)** dari enam file di bawah; **6 tes JS lulus**. Browser Firefox lokal telah menampilkan halaman saldo, ringkasan, Select2 dan tabel kosong tanpa error pemuatan. Tidak dibuat transaksi finansial atau WA nyata untuk QA.

**Keputusan cutoff sudah diterima:** seluruh saldo periode dicatat keluar ke rekening driver kendaraan seperti cutoff lama; proses ditolak jika driver, bank, nomor rekening atau nama pemilik rekening tidak lengkap. Nilai kosong, spasi, `-` dan `0` ditolak.

Cutoff tersedia pada POST `/billing/uj-ditahan-ban/{account}/cutoff` dan tombol/modal **Cutoff saldo** pada detail untuk admin/SU saja, dikonfirmasi Swal. Backend tidak mempercayai nominal/rekening dari frontend: nominal memakai saldo terkunci, rekening berasal dari driver terkunci. Vehicle/master/driver dikunci dan token unik diperiksa; audit keluar + saldo nol + total keluar atomik. Dana Sparepart tidak berubah. Notifikasi dikirim setelah commit. Ini pencatatan cutoff sesuai mekanisme lama, bukan integrasi transfer bank otomatis.

Tahap 3 selesai; tidak ada migrasi tambahan untuk cutoff dan tidak dilakukan pencairan/cutoff/WA nyata untuk pengujian. Tahap 4 dilanjutkan dan selesai pada pembaruan berikut.

```sh
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --compact tests/Feature/UjDitahanBanManagementTest.php tests/Feature/UjDitahanBanFlowTest.php tests/Feature/VendorUjDitahanBanTest.php tests/Feature/UjDitahanPencairanTest.php tests/Feature/AkiMaintenanceTest.php tests/Feature/FilterOliGantiTest.php
node --test tests/Unit/UangJalanHeldAmounts.test.cjs tests/Unit/MaintenanceCurrency.test.cjs
```

## Tahap 4 — selesai

Keputusan pengguna: saldo Ban dicek ketika checkout, dicek ulang dan dipotong ketika admin/SU menyetujui, memakai saldo positif bulan tertua dahulu, rekening tujuan wajib. Kas Besar tetap tersedia. **Dibayar Sendiri hanya tersedia untuk admin/SU**; role user ditolak backend bila mengirim metode tersebut. Ini khusus checkout ban, tidak mengubah kebijakan Aki/Filter sebelumnya.

Implementasi:

- `CheckoutBanGantiRequest`: otorisasi role, whitelist metode sesuai role, nominal integer rupiah positif dan rekening wajib untuk Kas Besar/UJ Ditahan Ban. Tidak lagi menghapus karakter sembarang dari nominal sebelum validasi.
- `MaintenanceBanUjDitahanService`: saldo Ban seluruh bulan positif, penguncian kendaraan/master, pengecekan saldo, debit FIFO lintas bulan, mutasi keluar berelasi invoice, pencegahan debit ulang. Saldo Sparepart/Kas Besar/utang vendor tidak disentuh untuk metode Ban.
- `BillingController::form_ganti_ban_checkout`: kunci kendaraan dan item keranjang, cek saldo Ban tanpa memotong, simpan invoice pending+detail dan hapus item snapshot secara atomik. Nomor invoice lebih pendek memakai prefix BAN + waktu + suffix acak. Tanggal pembuatan item keranjang dipertahankan pada detail.
- `otorisasi_maintenance_ban_luar_approve`: hanya admin/SU, kunci invoice dan cek status di dalam transaksi, kunci kendaraan, debit Ban dan update log/pivot ban+status invoice dalam satu transaksi. Saldo kurang atau log gagal -> seluruh perubahan rollback, invoice tetap pending. Cabang Kas Besar/utang vendor lama tetap dipakai untuk metode Kas Besar.
- `otorisasi_maintenance_ban_luar_reject`: hanya admin/SU, kunci invoice, hanya pending bisa ditolak. Invoice yang telah disetujui tidak dapat ditolak atau disetujui ulang. Penolakan pending tidak memotong saldo.
- Keranjang ban memakai Select2, Cleave library yang sudah ada + hidden integer, ringkasan saldo hanya ketika UJ Ditahan Ban dipilih, form rekening tujuan, Swal konfirmasi/notifikasi, header `table-success`. Aksi hapus/clear juga menampilkan hasil Swal.
- Otorisasi dan detail/rekap invoice mengenali label metode Ban serta nominal/rekening; saldo positif seluruh bulan ditampilkan saat relevan. Ringkasan saldo antrean dimuat dalam satu query agregat. Filter rekap mendukung metode Ban. Action otorisasi hanya muncul admin/SU.
- WA pembayaran Ban ke grup `kas-uj-ditahan` setelah commit; kegagalan notifikasi menghasilkan success + warning, tidak membatalkan transaksi tersimpan.
- Migrasi **sudah diterapkan lokal**: `2026_10_29_130509_add_ban_invoice_reference_to_uj_ditahan_ban_details.php`, menambahkan nullable FK `ban_ganti_invoice_id` (restrict deletion). Tidak menghapus saldo/data lama.

Verifikasi: **149 tes PHP lulus (930 assertions)** dari tujuh file, termasuk 15 kasus baru `BanMaintenancePaymentTest`. **8 tes JS lulus**, termasuk formatter metode UJ Ditahan Ban. Pint dan `git diff --check` bersih. Tes mencakup checkout tidak debit, saldo kurang menjaga keranjang, FIFO saat persetujuan, cek ulang saldo, rollback ketika log gagal, role/self-paid, required rekening, nominal canonical positif, penolakan/replay, Kas Besar tetap bisa checkout, rendering keranjang/otorisasi/detail dan saldo lintas bulan.

```sh
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --compact tests/Feature/BanMaintenancePaymentTest.php tests/Feature/UjDitahanBanManagementTest.php tests/Feature/UjDitahanBanFlowTest.php tests/Feature/VendorUjDitahanBanTest.php tests/Feature/UjDitahanPencairanTest.php tests/Feature/AkiMaintenanceTest.php tests/Feature/FilterOliGantiTest.php
node --test tests/Unit/UangJalanHeldAmounts.test.cjs tests/Unit/MaintenanceCurrency.test.cjs
vendor/bin/pint --dirty --format agent
git diff --check
```

Keranjang ban tetap mengikuti pola lama bersama (belum per pengguna). Tidak ada transaksi finansial/WA nyata dibuat untuk QA. Tes SQLite tidak membuktikan konkurensi dua koneksi MySQL. Tidak ada perubahan kebijakan dua baseline void rekening BRI/456 vs BCA/123.

## Pekerjaan berikutnya

3. Selesai: saldo, detail, pencairan, cutoff dan rekap Ban; label dana lama menjadi Sparepart.
4. Selesai: pembayaran ban luar dengan saldo Ban, pemeriksaan checkout + persetujuan, debit FIFO dan transaksi atomik. Seluruh tahap yang direncanakan selesai; tunggu permintaan pengguna untuk pengembangan tambahan.

## Batasan/temuan lama (jangan diperbaiki diam-diam)

- Dua kasus `TransaksiVoidTest:146` sudah gagal sebelum tahap 2: tes mengharapkan rekening asal BRI/456, kode memakai rekening Kas UJ BCA/123. Butuh keputusan sebelum mengganti aturan/ekspektasi.
- Void Sparepart dapat membuat saldo periode negatif bila uang sudah dipakai. Ikuti perilaku lama untuk Ban; jangan memindahkan saldo ke bulan lain tanpa persetujuan.
- Cutoff lama punya early return tanpa rollback; pencairan lama menormalisasi nominal sebelum validasi dan WA pasca-commit dapat error. Belum diperbaiki.
- Tes menggunakan SQLite, belum mensimulasikan dua koneksi MySQL bersamaan. Jangan melakukan transaksi/WA nyata untuk QA.

## Perintah verifikasi

Gunakan `DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test --compact ...` untuk tes lokal terisolasi. PHP berubah: `vendor/bin/pint --dirty --format agent`. Cek `git diff --check`. Baca daftar tes tahap 2 di pembaruan progres berikutnya sebelum mengulang inspeksi seluruh proyek.

## Penyesuaian navigasi — 29 Oktober 2026

Menu Billing dan Rekap kini masing-masing memiliki satu tombol **UJ DITAHAN**. Tombol membuka modal pilihan **Sparepart** / **Ban** melalui partial bersama `resources/views/layouts/uj-ditahan-menu.blade.php`. Pilihan mengarah ke route saldo/rekap masing-masing yang sudah ada. Tidak mengubah perhitungan atau pencatatan dana. Kompilasi Blade (`php artisan view:cache` dengan koneksi SQLite terisolasi) dan `git diff --check` lulus.

## Penyegaran desain UJ Ditahan Ban — 29 Oktober 2026

Halaman saldo, rekap dan detail memakai desain bersama navy/emerald/amber, header dengan ikon ban SVG, kartu total dengan ikon/aksen warna, panel filter, indikator jumlah data, ikon kendaraan/PDF/detail, empty state dan modal pencairan/cutoff yang diperbarui. CSS terisolasi pada `public/assets/css/uj-ditahan-ban.css` dan dimuat dengan cache version. Header tabel tetap `table-success`, Select2/Swal/Cleave dan infinite scroll tetap digunakan. Tidak mengubah backend atau perhitungan saldo. Verifikasi: kompilasi Blade berhasil; 24 tes `UjDitahanBanManagementTest` lulus (126 assertions); syntax JS/diff check lulus. Halaman saldo telah ditinjau di browser Firefox lokal; log browser terbaru tidak menunjukkan error.

## Penanda saldo pada filter bulan — 29 Oktober 2026

Pilihan bulan di saldo/rekap UJ Ditahan Ban menampilkan **❗** bila ada saldo Ban tidak nol pada tahun terpilih, mengikuti aturan Sparepart (termasuk saldo negatif). Memakai satu query distinct bulan pada request halaman HTML saja, tanpa memuat seluruh master atau menambah query pada request infinite scroll. Saldo nol/tahun lain/dana Sparepart tidak menandai bulan Ban. Tahun dipilih lalu diterapkan melalui tombol Tampilkan periode. Verifikasi: 25 tes UjDitahanBanManagementTest lulus (146 assertions), termasuk batas tahun dan saldo positif/nol/negatif; Pint/diff check lulus.

## WA otorisasi UJ Ditahan Sparepart — 29 Oktober 2026

Pengguna meminta WA setelah persetujuan aki/filter & oli memakai UJ Ditahan Sparepart, mengikuti pola kirimWaNotifikasi Billing. Keputusan: grand total hanya gabungan Sparepart + Ban **kendaraan tersebut**, bukan seluruh kendaraan. Pesan ke grup `kas-uj-ditahan` dan `vehicle.driver.no_hp` yang valid. Metode Kas Besar/Dibayar Sendiri tidak memakai notifikasi baru ini.

`MaintenanceUjDitahanService::notify` dipanggil kedua controller setelah transaksi persetujuan commit. Isi: judul maintenance, invoice, nomor lambung/uraian, nominal, bank/no rekening/atas nama, Total UJ Ditahan Sparepart: nomor lambung, Total UJ Ditahan Ban: nomor lambung, Grand Total UJ Ditahan: nomor lambung. Ketiga total dihitung dari SUM saldo semua periode kendaraan setelah pembayaran (saldo negatif ikut jumlah neto). Tidak menampilkan Kas Besar/modal investor.

Nomor driver harus sesuai format mobile Indonesia 08 / 628 / +628, panjang nasional 10–13 digit; spasi, titik, tanda kurung, dash dibersihkan. Huruf, placeholder, nomor telepon tetap, dan panjang tidak sesuai ditolak. Nomor kosong/tidak valid dilewati; grup tetap dikirim. Prefix + dihapus untuk pengiriman, prefix 08 dipertahankan mengikuti konvensi lama. Pengiriman grup/driver dicoba terpisah agar salah satu gagal tidak menghalangi lainnya. Exception pengiriman menghasilkan success+warning, persetujuan/debit tetap tersimpan dan tidak dapat diulang. Memakai GroupWa/StarSender yang sudah ada; status penerimaan aktual tetap bergantung layanan tersebut.

Tidak ada migrasi/dependensi baru, tidak ada WA nyata dikirim saat QA. 167 tes PHP lulus (1069 assertions), termasuk pengujian isi/saldo setelah debit, isolasi kendaraan, tepat satu notifikasi grup per approval, driver menerima pesan yang sama, tidak mengirim saat checkout/replay, gagal WA tidak membatalkan approval, validasi nomor dan driver tetap dicoba bila grup gagal. Pint dan git diff --check lulus.

## Penggabungan WA setoran UJ Ditahan — 29 Oktober 2026

`FormKasUangJalanController::keluar_store`: pesan Sparepart/Ban terpisah digabung menjadi **Form Uang Jalan Ditahan**. Nominal adalah setoran Sparepart + Ban. Footer memuat Total Saldo Sparepart kendaraan, Total Saldo Ban kendaraan dan Grand Total UJ Ditahan. Untuk kedua grup (kas-uang-jalan/kas-uj-ditahan) grand total adalah gabungan saldo positif kedua dana seluruh kendaraan, mengikuti filter saldo positif lama. Pengguna memutuskan grand total pesan driver **hanya kendaraan driver tersebut**. Template rincian dipakai bersama untuk menghindari perbedaan isi antar pesan; warning dokumen/maintenance tetap ikut. Pesan Ban terpisah tidak dikirim lagi.

Jika flag aktif dan driver bernomor tersedia: grup Kas UJ menerima 2 pesan (pengeluaran + ditahan gabungan), grup UJ Ditahan 1 (ditahan gabungan), driver 2 (ditahan gabungan + pengeluaran), vendor 1 (pengeluaran). Pembukuan/saldo tidak diubah. Flag nonaktif tetap tanpa pesan ditahan. Validasi nomor driver/vendor pada keluar_store mengikuti kode lama, belum diperluas dalam perubahan template ini. 168 tes PHP lulus (1093 assertions), termasuk jumlah pesan/penerima, nominal gabungan, footer berbeda grup vs driver, warning dan larangan judul lama. Pint/diff check lulus; tanpa WA nyata saat QA.

## Penggabungan notifikasi void UJ Ditahan — 30 Oktober 2026

Permintaan tahap awal: gabungkan void Sparepart dan Ban saja. `TransaksiController::processVoid` kini mengirim satu pesan **Void Uang Jalan Ditahan** ke grup `kas-uj-ditahan` jika ada data Sparepart atau pembalikan Ban. Nominal gabungan dengan rincian nominal Sparepart/Ban serta alasan. Pesan Ban terpisah dihapus. Format rekening dan footer saldo Kas UJ tetap dari template sebelumnya, belum diubah menjadi saldo dana. Tidak menambah penerima driver/vendor. Berlaku untuk endpoint void transaksi dan tagihan yang memakai processVoid bersama. Pembukuan tidak berubah. 19 tes UjDitahanBanFlowTest lulus (115 assertions) plus 2 tes notifikasi TransaksiVoidTest lulus (9 assertions), meliputi dua endpoint, Sparepart saja/Ban saja, satu pesan, jumlah dan alasan. Dua kegagalan baseline tes void rekening lama tetap di luar perubahan ini. Pint/diff check lulus, tanpa WA nyata saat QA.

## Pemindahan tombol cutoff — 30 Oktober 2026

Tombol dan modal cutoff dipindah dari detail Ban ke kolom Aksi daftar saldo aktif per kendaraan/bulan. Cairkan saldo tetap hanya pada detail. Cutoff tersedia admin/SU untuk saldo positif, tidak pada rekap saldo nol/saldo negatif. Data baris berisi URL/token unik dan preview rekening driver; modal tunggal diisi berdasarkan baris yang diklik menggunakan textContent. Modal memperlihatkan kendaraan/periode/saldo/rekening, lalu Swal meminta konfirmasi akhir. Otorisasi dan validasi saldo/rekening backend tetap berlaku. Driver dimuat eager agar tidak ada N+1 per baris. 26 tes UjDitahanBanManagementTest lulus (157 assertions), termasuk lokasi aksi, hak role dan saldo; syntax JS, Pint, diff check lulus.

## Penyamaan WA pencairan Sparepart/Ban — 30 Oktober 2026

Permintaan: judul **Form Pencairan UJ Ditahan Sparepart** / **Form Pencairan UJ Ditahan Ban**, layout mengikuti template Sparepart dengan kedua saldo kendaraan. Keputusan pengguna: footer grand total grup adalah gabungan saldo Sparepart + Ban seluruh kendaraan; driver hanya kedua dana kendaraannya. Total dihitung dari saldo positif seluruh periode setelah pencairan, mengikuti pola pencairan lama. Pesan memuat nomor lambung, periode, uraian, nominal, bank/nama/no rekening, Total Saldo UJ Ditahan Sparepart kendaraan, Total Saldo UJ Ditahan Ban kendaraan dan Grand Total UJ Ditahan sesuai penerima.

DRY: `UjDitahanWhatsAppService` menampung format pencairan bersama dan pengiriman grup+driver dengan validasi nomor seluler Indonesia. `MaintenanceUjDitahanService` memakai fungsi kirim yang sama sehingga validasi nomor dan penanganan exception tidak diduplikasi. Pesan maintenance tidak berubah. Grup/driver dicoba terpisah; error WA tidak membatalkan pencairan yang sudah commit. Ban completed (termasuk cutoff Ban) memakai formatter baru; pencairan manual Sparepart memakainya setelah commit. Cutoff Sparepart lama belum direfaktor. Blade saldo/detail Sparepart memakai feedback bersama agar warning WA terlihat.

Graphify AST lokal pada empat file relevan dipakai untuk penelusuran; cache hanya di /tmp/saind-payout-graph, tanpa API/cloud dan tidak di-commit. Tidak ada migrasi/dependensi/perubahan hitungan debit. 175 tes PHP lulus (1177 assertions), termasuk format kedua pencairan, dua saldo setelah potong, grand total beda grup/driver, isolasi saldo kendaraan lain, WA gagal tetap success+warning dan regresi maintenance/nomor HP. Pint/diff check lulus. Semua pengiriman WA pada pengujian dipalsukan, tidak ada pesan nyata dikirim.

## Hasil pengujian integrasi — 30 Oktober 2026

Perubahan WA terakhir di-commit pada main sebagai 0839f9c. Kode mobile tidak dimasukkan ke main. Branch codex/saind-dev-integration menggabungkan perubahan main dengan codex/mobile-api-foundation untuk pengujian bersama; merge kode e496932. Branch integrasi dipublikasikan sesuai izin pengguna.

Deployment integrasi ke environment pengujian yang diminta pengguna berhasil setelah pencadangan. Tidak dibuat environment atau database baru. Production dan environment pengujian lain tidak diubah. Konfigurasi dan identitas workspace mobile dipertahankan. Dependency mengikuti lock dan migrasi berhasil. Detail infrastruktur/cadangan tidak dipublikasikan dalam catatan ini.

Validasi: 261 tes PHP lulus (1753 assertions), mencakup mobile API, isolasi vendor, maintenance/UJ Ditahan dan payroll. Source hasil merge sama dengan salinan yang diuji. Build aset dan 6 tes JS QR berhasil. Pemeriksaan HTTP memastikan login/aset/workspace tersedia dan endpoint terlindungi menolak pengguna tanpa autentikasi. Tidak dilakukan transaksi finansial atau pengiriman WA nyata untuk QA.

Sebelum menguji UJ keluar untuk kendaraan flag aktif, nominal Ban pada kesepakatan vendor-rute wajib diisi karena default migrasinya nol. Pengujian bisnis manual dilanjutkan pada environment pengujian.
