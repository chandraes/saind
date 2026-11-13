@php($sections = $sections ?? \App\Support\VendorDashboardMenu::sections(auth()->user()))
@php($operasional = auth()->user()->isOperasional())

<div class="vendor-dashboard" data-vendor-dashboard>
    <div class="vendor-dashboard-intro">
        <div>
            <span class="vendor-dashboard-eyebrow">{{ $dashboardLabel ?? ($operasional ? 'OPERASIONAL' : (auth()->user()->canViewVendorFinance() ? 'AKUN VENDOR' : 'OPERASIONAL VENDOR')) }}</span>
            <p class="mb-0 text-muted">@isset($dashboardDescription){{ $dashboardDescription }}@else{{ $operasional ? 'Pantau dokumen perjalanan dan statistik armada seluruh vendor.' : (auth()->user()->canViewVendorFinance() ? 'Pantau dokumen, keuangan, dan statistik armada vendor Anda.' : 'Pantau dokumen perjalanan dan statistik armada vendor Anda.') }}@endisset</p>
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
@if ($operasional)
{{-- Seperti admin: Nota Bayar dibuka per vendor. --}}
<div class="modal fade" id="{{ \App\Support\VendorDashboardMenu::VENDOR_PICKER }}" tabindex="-1" aria-labelledby="{{ \App\Support\VendorDashboardMenu::VENDOR_PICKER }}-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="{{ \App\Support\VendorDashboardMenu::VENDOR_PICKER }}-title"><i class="fa fa-truck me-2" aria-hidden="true"></i>Pilih Vendor</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <form action="{{ route('vendor-operational.nota-bayar') }}" method="get">
            <div class="modal-body">
                <label for="operasional-vendor-select" class="form-label fw-semibold">Nama Vendor <span class="text-danger">*</span></label>
                <select class="form-select" id="operasional-vendor-select" name="vendor_id" required>
                    <option value="" selected disabled>-- Pilih Vendor --</option>
                    @foreach (\App\Models\Vendor::query()->orderBy('nama')->get(['id', 'nama', 'nickname']) as $option)
                    <option value="{{ $option->id }}">{{ $option->nama }}@if ($option->nickname) ({{ $option->nickname }})@endif</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary px-4">Lanjutkan <i class="fa fa-arrow-right ms-1" aria-hidden="true"></i></button>
            </div>
        </form>
    </div></div>
</div>
@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script>
    $(function () {
        $('#operasional-vendor-select').select2({theme: 'bootstrap-5', width: '100%', placeholder: '-- Pilih Vendor --',
            dropdownParent: $('#{{ \App\Support\VendorDashboardMenu::VENDOR_PICKER }}')});
    });
</script>
@endpush
@endif
@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/vendor-operational-dashboard.css') }}?v={{ filemtime(public_path('assets/css/vendor-operational-dashboard.css')) }}">
@endpush
