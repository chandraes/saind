<div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitleId"><i class="fa fa-plus-circle me-2"></i>Tambah Rute</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('rute.store')}}" method="post" id="createForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="nama" class="form-label fw-bold text-muted">Nama Rute</label>
                            <input type="text" class="form-control" name="nama" id="nama" required placeholder="Contoh: Pangkalpinang - Sungailiat">
                        </div>
                        <div class="col-md-4">
                            <label for="jarak" class="form-label fw-bold text-muted">Jarak</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="jarak" id="jarak" required step="any" placeholder="0">
                                <span class="input-group-text">Km</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="uang_jalan" class="form-label fw-bold text-muted">Uang Jalan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="text" class="form-control fw-bold" name="uang_jalan" id="uang_jalan" required placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="uj_ditahan" class="form-label fw-bold text-muted">Uang Jalan Ditahan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="text" class="form-control fw-bold" name="uj_ditahan" id="uj_ditahan" value="0" required placeholder="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-times me-1"></i>Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="fa fa-save me-1"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
