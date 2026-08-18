<div class="modal fade" id="modalEdit" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalEditTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalEditTitle"><i class="fa fa-edit me-2"></i>Ubah Rute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editForm">
                @csrf
                @method('PATCH')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="edit_nama" class="form-label fw-bold text-muted">Nama Rute</label>
                            <input type="text" class="form-control" name="nama" id="edit_nama" required>
                        </div>
                        <div class="col-md-4">
                            <label for="edit_jarak" class="form-label fw-bold text-muted">Jarak</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="jarak" id="edit_jarak" required step="any">
                                <span class="input-group-text">Km</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_uang_jalan" class="form-label fw-bold text-muted">Uang Jalan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="text" class="form-control fw-bold" name="uang_jalan" id="edit_uang_jalan" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_uj_ditahan" class="form-label fw-bold text-muted">Uang Jalan Ditahan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="text" class="form-control fw-bold" name="uj_ditahan" id="edit_uj_ditahan" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-times me-1"></i>Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold"><i class="fa fa-save me-1"></i>Ubah Rute</button>
                </div>
            </form>
        </div>
    </div>
</div>
