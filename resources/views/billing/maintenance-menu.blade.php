@php
    $modalId = $authorization ? 'otorisasiMaintenance' : 'formMaintenanceNew';
    $menuItems = [
        ['name' => 'Ban Luar', 'icon' => 'db-ban.svg', 'route' => $authorization ? 'billing.otorisasi-maintenance' : 'billing.form-maintenance.ban-luar', 'count' => $countBanPending, 'description' => 'Penggantian dan pemasangan ban kendaraan.'],
        ['name' => 'Aki', 'icon' => 'aki.svg', 'route' => $authorization ? 'billing.otorisasi-maintenance.aki' : 'billing.form-maintenance.aki', 'count' => $countAkiPending, 'description' => 'Penggantian aki dan kondisi awal unit.'],
        ['name' => 'Filter & Oli Mesin', 'icon' => 'filter-oli-mesin.svg', 'route' => $authorization ? 'billing.otorisasi-maintenance.filter-oli' : 'billing.form-maintenance.filter-oli', 'count' => $countFilterOliPending, 'description' => 'Perawatan filter dan penggantian oli mesin.'],
    ];
@endphp
<div class="modal fade maintenance-menu" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <div class="text-start"><span class="small text-primary fw-semibold">PERAWATAN KENDARAAN</span><h2 class="modal-title fs-4 fw-bold mt-1" id="{{ $modalId }}Title">{{ $authorization ? 'Otorisasi Maintenance' : 'Form Maintenance' }}</h2></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body px-4 pb-4 text-start">
                <p class="text-muted small mb-4">{{ $authorization ? 'Pilih jenis perawatan untuk meninjau invoice yang belum diproses.' : 'Pilih jenis perawatan untuk mencatat penggantian komponen.' }}</p>
                <div class="d-grid gap-3">
                    @foreach ($menuItems as $menu)
                        <a href="{{ route($menu['route']) }}" class="maintenance-menu-link text-decoration-none d-flex align-items-center gap-3 p-3">
                            <span class="maintenance-menu-icon"><img src="{{ asset('images/'.$menu['icon']) }}" width="38" height="38" alt=""></span>
                            <span class="flex-grow-1">
                                <span class="d-flex align-items-center justify-content-between gap-2 mb-1"><span class="fw-bold text-dark">{{ $menu['name'] }}</span>@if ($authorization && $menu['count'] > 0)<span class="badge rounded-pill bg-danger">({{ $menu['count'] }})<span class="visually-hidden"> invoice belum diproses</span></span>@endif</span>
                                <span class="small text-muted d-block">{{ $authorization ? ($menu['count'] > 0 ? 'Menunggu persetujuan Anda' : 'Tidak ada invoice menunggu') : $menu['description'] }}</span>
                            </span>
                            <i class="fa fa-angle-right text-muted" aria-hidden="true"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@pushOnce('css')
<link rel="stylesheet" href="{{ asset('assets/css/billing-maintenance-menu.css') }}">
@endPushOnce
