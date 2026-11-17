@php($sections = $sections ?? \App\Support\VendorDashboardMenu::sections(auth()->user()))
@php($operasional = auth()->user()->seesAllVendors())

<div class="vendor-dashboard" data-vendor-dashboard>
    <div class="vendor-dashboard-intro">
        <div>
            <span class="vendor-dashboard-eyebrow">{{ $dashboardLabel ?? ($operasional ? (auth()->user()->isExecutive() ? (auth()->user()->isDireksi() ? 'DIREKSI' : 'GENERAL MANAGER') : 'OPERASIONAL') : (auth()->user()->canViewVendorFinance() ? 'AKUN VENDOR' : 'OPERASIONAL VENDOR')) }}</span>
            <p class="mb-0 text-muted">@isset($dashboardDescription){{ $dashboardDescription }}@else{{ $operasional ? (auth()->user()->isExecutive() ? 'Pantau dokumen, nilai bayar, dan statistik armada seluruh vendor.' : 'Pantau dokumen perjalanan dan statistik armada seluruh vendor.') : (auth()->user()->canViewVendorFinance() ? 'Pantau dokumen, keuangan, dan statistik armada vendor Anda.' : 'Pantau dokumen perjalanan dan statistik armada vendor Anda.') }}@endisset</p>
        </div>
        <span class="vendor-dashboard-access"><span aria-hidden="true"></span>Akses baca saja</span>
    </div>
    @foreach ($sections as $section)
        <section class="vendor-dashboard-section" aria-labelledby="vendor-section-{{ $section['key'] }}">
            <h2 id="vendor-section-{{ $section['key'] }}">{{ $section['title'] }}</h2>
            <div class="vendor-dashboard-grid vendor-dashboard-grid-{{ $section['key'] }}" data-vendor-menu-row="{{ $section['key'] }}">
                @foreach ($section['menus'] as $menu)
                    @if (isset($menu['url']))
                    <a class="vendor-menu-card" href="{{ isset($menu['modal']) ? '#' : $menu['url'] }}" @isset($menu['modal']) data-bs-toggle="modal" data-bs-target="#{{ $menu['modal'] }}" @endisset>
                    @else
                        <div class="vendor-menu-card bg-light">
                    @endif
                        <span class="vendor-menu-icon"><img src="{{ asset('images/'.$menu['icon']) }}" alt="" width="48" height="48"></span>
                        <h3>{{ $menu['title'] }}</h3>
                        <p>{{ $menu['description'] }}</p>
                    @if (isset($menu['url']))
                        <span class="vendor-menu-link">Lihat data <span aria-hidden="true">→</span></span>
                    </a>
                    @else
                        <span class="text-muted small mt-auto">Belum tersedia</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach
</div>
@if (auth()->user()->canViewNolamHeldCash())
@include('layouts.uj-ditahan-menu', ['menuArea' => 'vendor-operational'])
@endif
@if ($operasional)
{{-- Seperti admin: Nota Bayar dibuka per vendor; eksekutif membuka Nota Tagihan per customer. --}}
@include('per-vendor-operational.picker-modal', ['id' => \App\Support\VendorDashboardMenu::VENDOR_PICKER, 'title' => 'Pilih Vendor', 'icon' => 'fa-truck',
    'action' => route('vendor-operational.nota-bayar'), 'name' => 'vendor_id', 'label' => 'Nama Vendor', 'placeholder' => '-- Pilih Vendor --',
    'options' => \App\Models\Vendor::query()->orderBy('nama')->get(['id', 'nama', 'nickname'])->mapWithKeys(fn ($v) => [$v->id => $v->nama.($v->nickname ? ' ('.$v->nickname.')' : '')])])
@if (auth()->user()->isExecutive())
@include('per-vendor-operational.picker-modal', ['id' => \App\Support\VendorDashboardMenu::CUSTOMER_PICKER, 'title' => 'Pilih Customer', 'icon' => 'fa-building',
    'action' => url('transaksi/nota-tagihan'), 'pathTemplate' => route('billing.nota-tagihan', ['customer' => '__ID__']), 'name' => 'customer', 'label' => 'Nama Customer', 'placeholder' => '-- Pilih Customer --',
    'options' => \App\Models\Customer::query()->where('status', 1)->orderBy('nama')->get(['id', 'nama', 'singkatan'])->mapWithKeys(fn ($c) => [$c->id => $c->nama.($c->singkatan ? ' ('.$c->singkatan.')' : '')])])
@endif
@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script>
    $(function () {
        $('.dashboard-picker-select').each(function () {
            $(this).select2({theme: 'bootstrap-5', width: '100%', placeholder: this.dataset.placeholder, dropdownParent: $(this).closest('.modal')});
        });
        // Customer menjadi bagian path (/transaksi/nota-tagihan/{customer}).
        $('form[data-path-template]').on('submit', function (event) {
            event.preventDefault();
            window.location.href = this.dataset.pathTemplate.replace('__ID__', encodeURIComponent($(this).find('select').val()));
        });
    });
</script>
@endpush
@endif
@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-operational-dashboard.css') }}?v={{ filemtime(public_path('assets/css/vendor-operational-dashboard.css')) }}">
@endpush
