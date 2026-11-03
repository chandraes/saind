<div class="modal fade" id="ujDitahanMenu" tabindex="-1" aria-labelledby="ujDitahanMenuTitle" aria-describedby="ujDitahanMenuDescription" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <h5 class="modal-title fw-bold" id="ujDitahanMenuTitle">UJ Ditahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <p class="text-muted mb-4" id="ujDitahanMenuDescription">{{ $menuArea === 'billing' ? 'Pilih jenis dana untuk melihat saldo dan mengelola pencairan.' : 'Pilih jenis dana untuk melihat rekap periode dan riwayat mutasi.' }}</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="{{ route($menuArea.'.uj-ditahan') }}" class="btn btn-outline-success w-100 h-100 text-start rounded-3 p-4">
                            <span class="d-block mb-3" aria-hidden="true"><i class="fa fa-cogs fa-2x"></i></span>
                            <span class="d-block fs-5 fw-bold mb-2">Sparepart <span class="float-end" aria-hidden="true">&rarr;</span></span>
                            <span class="d-block small">Dana perawatan aki, filter, dan oli mesin.</span>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route($menuArea.'.uj-ditahan-ban') }}" class="btn btn-outline-primary w-100 h-100 text-start rounded-3 p-4">
                            <span class="d-block mb-3" aria-hidden="true"><i class="fa fa-truck fa-2x"></i></span>
                            <span class="d-block fs-5 fw-bold mb-2">Ban <span class="float-end" aria-hidden="true">&rarr;</span></span>
                            <span class="d-block small">Dana khusus penggantian ban luar kendaraan.</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
