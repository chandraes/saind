<div class="modal fade" id="editInvestor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="editInvestorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-md" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editInvestorTitle">
                    <i class="fas fa-edit me-2"></i>Edit Cost Operational
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="edit_nama" class="form-label fw-bold">Nama Kategori</label>
                            <input type="text" class="form-control" name="nama" id="edit_nama" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_periode" class="form-label fw-bold">Periode Batasan</label>
                            <select class="form-select" name="periode" id="edit_periode" required>
                                <option value="mingguan">Mingguan</option>
                                <option value="bulanan">Bulanan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_jumlah_limit" class="form-label fw-bold">Maksimal Penggunaan</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="jumlah_limit" id="edit_jumlah_limit" min="1" required>
                                <span class="input-group-text">Kali</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Tutup
                    </button>
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
