@php
    $documentMenus = [
        ['title' => 'Nota Muat Masuk', 'description' => 'Pantau pengajuan dan hasil pemeriksaan nota.', 'route' => 'billing.nota-muat.index', 'icon' => 'nota-muat-masuk.svg'],
        ['title' => 'Nota Bongkar', 'description' => 'Lihat perjalanan dan progres pembongkaran.', 'route' => 'vendor-operational.nota-bongkar', 'icon' => 'bongkar.svg'],
        ['title' => 'Nota Bayar Vendor', 'description' => 'Periksa nota dan rincian perjalanan vendor.', 'route' => 'vendor-operational.nota-bayar', 'icon' => 'bayar.svg'],
        ['title' => 'Invoice Vendor', 'description' => 'Telusuri invoice dan nota yang tercantum.', 'route' => 'vendor-operational.invoice', 'icon' => 'invoice-bayar.svg'],
    ];
    $statisticMenus = [
        ['title' => 'Perform Unit', 'description' => 'Performa dan aktivitas kendaraan.', 'route' => 'statistik.perform-unit', 'icon' => 'perform-unit.svg'],
        ['title' => 'Perform Unit All', 'description' => 'Rekap ritase bulanan seluruh unit vendor Anda.', 'route' => 'statistik.perform-unit.all-vendor', 'icon' => 'all-vendor.svg'],
        ['title' => 'Statistik Upah Gendong', 'description' => 'Riwayat upah gendong per unit.', 'route' => 'statistik.upah-gendong', 'icon' => 'statistik-ug.svg'],
        ['title' => 'Statistik Filter & Oli Mesin', 'description' => 'Pemakaian dan histori penggantian.', 'route' => 'statistik.filter-oli', 'icon' => 'filter-oli-mesin.svg'],
        ['title' => 'Statistik Aki', 'description' => 'Kondisi dan histori aki kendaraan.', 'route' => 'statistik.aki', 'icon' => 'aki.svg'],
        ['title' => 'Statistik Ban Luar', 'description' => 'Pemakaian dan histori ban per posisi.', 'route' => 'statistik.ban-luar', 'icon' => 'db-ban.svg'],
    ];
@endphp

<div class="vendor-dashboard" data-vendor-dashboard>
    <div class="vendor-dashboard-intro">
        <div>
            <span class="vendor-dashboard-eyebrow">OPERASIONAL VENDOR</span>
            <p class="mb-0 text-muted">Pantau dokumen perjalanan dan statistik armada vendor Anda.</p>
        </div>
        <span class="vendor-dashboard-access"><span aria-hidden="true"></span>Akses baca saja</span>
    </div>
    @foreach ([['key' => 'documents', 'title' => 'Nota & perjalanan', 'menus' => $documentMenus], ['key' => 'statistics', 'title' => 'Statistik operasional', 'menus' => $statisticMenus]] as $section)
        <section class="vendor-dashboard-section" aria-labelledby="vendor-section-{{ $section['key'] }}">
            <h2 id="vendor-section-{{ $section['key'] }}">{{ $section['title'] }}</h2>
            <div class="vendor-dashboard-grid vendor-dashboard-grid-{{ $section['key'] }}" data-vendor-menu-row="{{ $section['key'] }}">
                @foreach ($section['menus'] as $menu)
                    <a class="vendor-menu-card" href="{{ route($menu['route']) }}">
                        <span class="vendor-menu-icon"><img src="{{ asset('images/'.$menu['icon']) }}" alt="" width="48" height="48"></span>
                        <h3>{{ $menu['title'] }}</h3>
                        <p>{{ $menu['description'] }}</p>
                        <span class="vendor-menu-link">Lihat data <span aria-hidden="true">→</span></span>
                    </a>
                @endforeach
            </div>
        </section>
    @endforeach
</div>
@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-operational-dashboard.css') }}">
@endpush
