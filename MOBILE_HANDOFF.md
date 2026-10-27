# DSGroup Hauling — checkpoint 27 Oktober 2026

Baca file ini dahulu saat melanjutkan; jangan audit ulang seluruh proyek atau riwayat browser. Pengguna meminta penggunaan Codex hemat, terutama pembacaan browser. Lihat pembaruan terakhir di bawah sebelum memakai checkpoint awal.

## Pembaruan terakhir: Industrial Light dan Database mobile
- Pengguna telah menguji aplikasi di iPhone dan menyatakan berjalan baik setelah Save compliance. Tidak perlu mengulang setup Apple.
- Pengguna memilih mockup A **Industrial Light** dan meminta Database → Biodata Vendor dan Vehicle, dibatasi role. Sudah diimplementasikan, committed dan pushed; backend SUDAH deploy ke kedua staging. Mobile sedang dibangun oleh Xcode Cloud (belum diverifikasi upload/Testing).
- Mobile branch `codex/workspace-onboarding`, commit `044fcc8`; origin/main dan main lokal sudah sama, checkout bersih. Termasuk perubahan ikon dari logo `/Users/unsri-perpus/Herd/logo-truk-batubara` (20 PNG launcher), tema, logo header, navigasi Beranda/Database/Akun, pencarian/pagination, daftar dan detail mobile. File baru: `lib/screens/mobile_home.dart`, `lib/screens/database_screen.dart`, `lib/workspaces/database_api.dart`, `test/database_test.dart`, `assets/brand/truck.png`.
- Backend worktree branch `codex/mobile-api-foundation`, commit `87002d0`, pushed ke origin branch tersebut; checkout bersih. Kedua staging `/var/www/dev_saind` dan `/var/www/dev_saind_1` sudah fast-forward ke 87002d0; config dan route cache sukses. Tidak ada migrasi, reload Nginx, atau restart PHP-FPM. File baru: `app/Http/Controllers/Api/DatabaseController.php`, `app/Http/Resources/MobileVendorResource.php`, `app/Http/Resources/MobileDatabaseVehicleResource.php`; perubahan `routes/api.php`, `config/mobile.php`, `MobileUserResource`, tests foundation.
- Endpoint baru GET `/api/v1/database/vendors`, `/vendors/{id}`, `/vehicles`, `/vehicles/{id}`; **hanya admin dan su**, mengikuti route master web. `permissions.database` dari login/me mengontrol visibilitas menu mobile. Middleware server tetap menolak role lain. Endpoint operasional `/api/v1/vehicles` yang lama tetap tidak berubah.
- Biodata menampilkan identitas/kontak/admin rekening pada detail berizin; list tidak mengirim rekening. Vehicle menampilkan identitas, vendor, tanggal dokumen, tidak mengirim rekening kendaraan. Seluruh fitur Database baca saja, tanpa CRUD.
- Mobile memverifikasi identitas workspace sebelum mengirim token, tidak mengikuti URL pagination dari server, reset data saat workspace/token berubah. Search disubmit eksplisit dan pagination memakai query yang sudah diterapkan.
- Verifikasi terakhir: 27 PHPUnit tests /137 assertions, 36 Flutter tests, analyze bersih; build iOS simulator dan Android debug berhasil. Warning build Android berupa SDK XML/tools tidak selaras dan native access Java, nonfatal. Pint dan diff-check lulus. Review independen menemukan search `0` yang diperbaiki dan ditambahkan regresinya. Tes teks besar 2x pada 375px menemukan overflow header dan sudah diperbaiki.
- Untuk tes backend worktree tanpa .env: gunakan `APP_KEY=01234567890123456789012345678901 DB_CONNECTION=sqlite DB_DATABASE=:memory: vendor/bin/phpunit tests/Feature/MobileApiFoundationTest.php` (key dummy khusus tes; bukan key aplikasi). Artisan test menghasilkan warning konfigurasi .env; PHPUnit langsung bersih. Jangan ubah production key.
- Verifikasi live: HTTPS login dan workspace 200 di kedua staging; endpoint vendors/vehicles tanpa token 401. Route cache di kedua staging memuat middleware admin,su untuk keempat endpoint. Backend main/production tidak diubah.
- Xcode Cloud sudah otomatis terpicu push main mobile 044fcc8; GitHub check Archive-iOS berstatus in_progress. Build UUID `a21dd5a9-1396-4c26-b30b-81f07eb537cf`; detail https://appstoreconnect.apple.com/teams/6c229912-7a66-4e81-a02d-76d0c4c080e7/apps/6827374664/ci/builds/a21dd5a9-1396-4c26-b30b-81f07eb537cf/action/9d0ae3e8-49b1-42d1-bb8a-f4daed5c1d52 . Periksa status melalui `gh api repos/chandraes/saind-mobile/commits/044fcc80294e4cbe0695734a21ad66b2c8dc4aa3/check-runs` dengan jq field name,status,conclusion,details_url, hindari browser penuh.
- Langkah berikut saat diminta: cek hasil cloud build ini dan TestFlight, bukan deploy atau push ulang. Apple masih bisa meminta compliance. Pengguna perlu refresh/login sesi agar menerima permissions baru; menu Database hanya admin/su.

## Status terverifikasi
- Mobile: `/Users/unsri-perpus/Herd/saind-mobile`, Flutter 3.47.7 / Dart 3.13.5; iOS dan Android. Fitur workspace QR/alamat HTTPS, login, switch, logout; nota muat masih dummy.
- GitHub privat: https://github.com/chandraes/saind-mobile . Akun `chandraes` terhubung. Xcode Cloud diberi akses hanya ke repository ini, sudah diotorisasi pengguna.
- Mobile branch lokal `codex/workspace-onboarding`; commit terakhir `0c640cf`; `main` lokal dan `origin/main` saat checkpoint sama. Periksa status singkat sebelum mengubah apa pun.
- Bundle ID `com.dsgroups.dsgroupHauling`; Apple team `PF7P867H2Q`; App Store Connect app `6827374664`.
- Workflow aktif `TestFlight Internal - DSGroup Hauling`: push main, clean build, Archive iOS scheme Runner, internal-only TestFlight, grup `Tim Internal` (1 anggota). Build action duplikat telah dihapus. Xcode latest release saat setup 27.1.
- Build cloud 3 BERHASIL archive, signing, ekspor, dan upload. Tiga warning mobile_scanner nonfatal. Distribusi belum diverifikasi setelah compliance.
- Pengguna SUDAH menekan Save compliance sendiri (pesan terakhir). Jangan minta ulang atau klik Save lagi. Status Testing/Ready to Test setelahnya belum diperiksa.
- Build manual 1 sebelumnya berhasil diuji pengguna di iPhone.

## CI dan pengujian
- `.flutter-version`, `ios/ci_scripts/ci_post_clone.sh`, `test/ci_post_clone_test.py`, dan manifest Xcode Cloud sudah committed/pushed.
- Script clone SDK resmi sesuai pin; warmup `flutter --version` sebelum parsing JSON; precache iOS; pub get enforce lockfile; analyze; test; build release config-only dengan CI_BUILD_NUMBER. Project memakai Swift Package Manager, tanpa Podfile.
- Cold SDK JSON parsing bug build 1 diperbaiki pada 0c640cf; cloud build 2 dan archive build 3 berhasil.
- Terakhir: 6 Python regression tests dan 30 Flutter tests lulus, analyze bersih. Jangan ulang semua tes tanpa perubahan.
- Tidak ada perubahan ITSAppUsesNonExemptEncryption pada Info.plist. Jawaban compliance build 3: standard encryption selain/di samping OS Apple, No France. Save oleh pengguna. Jangan mengasumsikan deklarasi exemption untuk build berikutnya.

## Lokasi backend
- Backend utama `/Users/unsri-perpus/Herd/saind`, main; jangan campur pengembangan mobile dengan main.
- Worktree `/Users/unsri-perpus/.codex/worktrees/mobile-api-foundation/saind`, branch `codex/mobile-api-foundation`, checkpoint commit `a738de8`.
- Dua staging: dev-saind.ds-groups.com dan dev-saind-1.ds-groups.com; database terpisah per workspace/perusahaan. Jangan ganggu aplikasi lain di VPS.
- Rencana produk: satu aplikasi global, banyak workspace dengan backend/database terpisah. OCR tanpa AI terlebih dahulu, 3–4 format tambang dinamis. Data nota masuk penampungan/review sebelum mengisi kas-uang-jalan/keluar; tidak otomatis mengeluarkan uang.

## Lanjutan minimal ketika diminta
1. Periksa hanya status build 3 di TestFlight dan grup Tim Internal; jika Testing/Ready to Test, setup distribusi selesai. Jangan membuat build baru hanya untuk mengecek.
2. Fitur berikutnya perlu ditentukan pengguna; scanning nota belum diimplementasikan.

## Tautan langsung (hindari navigasi berulang)
- TestFlight build 3: https://appstoreconnect.apple.com/teams/6c229912-7a66-4e81-a02d-76d0c4c080e7/apps/6827374664/testflight/ios/9bb651e4-2376-4212-89fe-39f920f1852b
- Cloud build 3: https://appstoreconnect.apple.com/teams/6c229912-7a66-4e81-a02d-76d0c4c080e7/xcode-cloud/products/57cfdba7-ee76-4585-8669-c2bb454e0d61/builds/821fbd4b-ce1d-4ceb-aca4-b85be02762bd/summary
- Workflow ID `311BE875-D068-4D34-9E35-C96A6E0F433F`.

## Penghematan konteks/browser
- Utamakan connector/API/CLI yang memadai. Browser hanya untuk kemampuan yang tidak tersedia di sana.
- Buka tautan langsung; ambil snapshot pertama secukupnya, lalu diff setelah aksi. Jangan mencetak AX penuh, log panjang, atau seluruh sumber editor. Ringkas target status, tombol, dan error yang diperlukan saja.
- Gunakan screenshot hanya bila AX tidak cukup; jangan ulang snapshot tanpa perubahan. Jangan polling rapat untuk build Apple.
- Simpan hasil yang sudah terverifikasi, baca file/rentang terkait saja, satu tugas terfokus per sesi. Tidak memakai subagent tanpa kebutuhan/otorisasi yang berlaku.
- Catatan ini bukan bukti keadaan remote terkini. Verifikasi sempit hanya saat melanjutkan.
