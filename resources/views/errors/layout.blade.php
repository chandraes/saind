@php
    $errorCode = (int) trim($__env->yieldContent('code', '500'));
    $pages = [
        401 => ['title' => 'Perlu login untuk melanjutkan', 'message' => 'Silakan masuk kembali agar Anda dapat mengakses halaman ini.', 'phase' => 'Menunggu pemeriksaan identitas', 'description' => 'Truk berhenti di pos pemeriksaan dengan gerbang terkunci.', 'scene' => 'checkpoint', 'note' => 'Masuk terlebih dahulu'],
        402 => ['title' => 'Pembayaran diperlukan', 'message' => 'Akses ini memerlukan penyelesaian pembayaran. Hubungi administrator untuk informasi lebih lanjut.', 'phase' => 'Menunggu penyelesaian pembayaran', 'description' => 'Truk berhenti di gerbang pembayaran.', 'scene' => 'payment', 'note' => 'Pembayaran diperlukan'],
        403 => ['title' => 'Akses halaman dibatasi', 'message' => 'Akun Anda belum memiliki izin untuk membuka halaman ini. Hubungi administrator jika akses diperlukan.', 'phase' => 'Akses jalur dibatasi', 'description' => 'Truk berhenti di depan palang pembatas akses.', 'scene' => 'barrier', 'note' => 'Akses dibatasi'],
        404 => ['title' => 'Halaman tidak ditemukan', 'message' => 'Alamat halaman mungkin berubah atau sudah tidak tersedia. Silakan kembali ke beranda.', 'phase' => 'Mencari jalur yang tersedia', 'description' => 'Truk menemukan jalur yang terputus lalu berbalik untuk mencari jalan lain.', 'scene' => 'missing', 'note' => 'Jalur tidak ditemukan'],
        419 => ['title' => 'Sesi Anda telah berakhir', 'message' => 'Muat ulang halaman dan masuk kembali, lalu ulangi aktivitas Anda.', 'phase' => 'Izin perjalanan perlu diperbarui', 'description' => 'Truk menunggu di pos dengan dokumen perjalanan dan penanda waktu berakhir.', 'scene' => 'expired', 'note' => 'Perbarui sesi Anda'],
        429 => ['title' => 'Terlalu banyak permintaan', 'message' => 'Tunggu beberapa saat sebelum mencoba kembali.', 'phase' => 'Truk menunggu giliran', 'description' => 'Tiga truk mengantre di jalur pemeriksaan.', 'scene' => 'queue', 'note' => 'Tunggu giliran'],
        500 => ['title' => 'Terjadi gangguan sistem', 'message' => 'Kami belum dapat memproses permintaan Anda. Silakan coba kembali beberapa saat lagi.', 'phase' => 'Truk mengalami gangguan mesin', 'description' => 'Truk berhenti dengan panel mesin terbuka dan tanda gangguan.', 'scene' => 'breakdown', 'note' => 'Gangguan mesin'],
        503 => ['title' => 'Sistem sedang dalam pemeliharaan', 'message' => 'Kami sedang menyiapkan sistem agar dapat digunakan kembali. Silakan coba beberapa saat lagi.', 'phase' => 'Truk sedang diperbaiki', 'description' => 'Mekanik memperbaiki truk yang ditopang dongkrak di area servis.', 'scene' => 'service', 'note' => 'Area servis'],
    ];
    $page = $pages[$errorCode] ?? $pages[500];
    $homeUrl = route('home');
    $primaryUrl = in_array($errorCode, [401, 419], true) ? route('login') : $homeUrl;
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $errorCode }} — {{ $page['title'] }} | SAIND</title>
    <link rel="icon" href="{{ asset('saind.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/error-pages.css') }}">
    <script src="{{ asset('assets/js/error-pages.js') }}" defer></script>
</head>
<body class="error-page" data-error-page data-error-code="{{ $errorCode }}" data-error-scene="{{ $page['scene'] }}" data-error-phase="{{ $page['phase'] }}">
    <header>
        <a class="brand" href="{{ $homeUrl }}">SAIND<small>Sistem operasional terpadu</small></a>
        <span class="error-tag"><i aria-hidden="true"></i>Error {{ $errorCode }}</span>
    </header>
    <main class="hero">
        <section class="copy" aria-labelledby="error-title">
            <div class="error-meta">Error {{ $errorCode }}</div>
            <h1 id="error-title">{{ $page['title'] }}</h1>
            <p>{{ $page['message'] }}</p>
            <div class="actions">
                <a class="primary" href="{{ $primaryUrl }}">{{ in_array($errorCode, [401, 419], true) ? 'Masuk kembali' : 'Kembali ke beranda' }} <span aria-hidden="true">↗</span></a>
                <a class="secondary" href="{{ $homeUrl }}" data-error-back>Halaman sebelumnya</a>
            </div>
        </section>
        <div class="scene" role="img" aria-label="{{ $page['description'] }}">
            @include('errors.illustration', ['page' => $page])
        </div>
        <div class="error-number" aria-hidden="true">{{ $errorCode }}</div>
        <div class="scene-status"><span class="dot" aria-hidden="true"></span><span data-error-stage>{{ $page['phase'] }}</span></div>
    </main>
    <footer class="footer">
        <span>SAIND · Sistem operasional terpadu</span>
        <button type="button" class="animation-toggle" data-error-play hidden>Jeda animasi</button>
    </footer>
</body>
</html>
