<div class="modal fade maintenance-menu" id="formMaintenanceNew" tabindex="-1" aria-labelledby="rekapMaintenanceTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content border-0 rounded-4 shadow">
        <div class="modal-header border-0 px-4 pt-4 pb-2"><div class="text-start"><span class="small text-primary fw-semibold">PERAWATAN KENDARAAN</span><h2 class="modal-title fs-4 fw-bold mt-1" id="rekapMaintenanceTitle">Rekap Maintenance</h2></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body px-4 pb-4 text-start"><p class="small text-muted mb-4">Pilih jenis perawatan untuk menelusuri invoice dan riwayat penggantian.</p><div class="d-grid gap-3">
            @foreach ([['Ban Luar', 'db-ban.svg', 'rekap.maintenance.ban-luar', 'Rekap penggantian dan pemasangan ban.'], ['Aki', 'aki.svg', 'rekap.maintenance.aki', 'Rekap penggantian aki kendaraan.'], ['Filter & Oli Mesin', 'filter-oli-mesin.svg', 'rekap.maintenance.filter-oli', 'Rekap penggantian filter dan oli mesin.']] as [$name, $icon, $route, $description])
                <a href="{{ route($route) }}" class="maintenance-menu-link text-decoration-none d-flex align-items-center gap-3 p-3"><span class="maintenance-menu-icon"><img src="{{ asset('images/'.$icon) }}" width="38" height="38" alt=""></span><span class="flex-grow-1"><span class="fw-bold text-dark d-block mb-1">{{ $name }}</span><span class="small text-muted d-block">{{ $description }}</span></span><i class="fa fa-angle-right text-muted" aria-hidden="true"></i></a>
            @endforeach
        </div></div>
    </div></div>
</div>
@pushOnce('css')
<link rel="stylesheet" href="{{ asset('assets/css/billing-maintenance-menu.css') }}">
@endPushOnce
