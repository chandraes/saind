@php($sections = \App\Support\VendorDashboardMenu::sections(auth()->user()))

<div class="vendor-dashboard" data-vendor-dashboard>
    <div class="vendor-dashboard-intro">
        <div>
            <span class="vendor-dashboard-eyebrow">{{ auth()->user()->canViewVendorFinance() ? 'AKUN VENDOR' : 'OPERASIONAL VENDOR' }}</span>
            <p class="mb-0 text-muted">{{ auth()->user()->canViewVendorFinance() ? 'Pantau dokumen, keuangan, dan statistik armada vendor Anda.' : 'Pantau dokumen perjalanan dan statistik armada vendor Anda.' }}</p>
        </div>
        <span class="vendor-dashboard-access"><span aria-hidden="true"></span>Akses baca saja</span>
    </div>
    @foreach ($sections as $section)
        <section class="vendor-dashboard-section" aria-labelledby="vendor-section-{{ $section['key'] }}">
            <h2 id="vendor-section-{{ $section['key'] }}">{{ $section['title'] }}</h2>
            <div class="vendor-dashboard-grid vendor-dashboard-grid-{{ $section['key'] }}" data-vendor-menu-row="{{ $section['key'] }}">
                @foreach ($section['menus'] as $menu)
                    <a class="vendor-menu-card" href="{{ $menu['url'] }}">
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
<link rel="stylesheet" href="{{ asset('assets/css/vendor-operational-dashboard.css') }}?v={{ filemtime(public_path('assets/css/vendor-operational-dashboard.css')) }}">
@endpush
