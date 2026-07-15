<div class="modal fade" id="createInvestor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="investorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-md" role="document">
        <div class="modal-content shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="investorTitle">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Cost Operational
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('database.cost-operational.store')}}" method="post" id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="nama" class="form-label fw-bold">Nama Kategori</label>
                            <input type="text" class="form-control" name="nama" id="nama" placeholder="Masukkan nama operasional..." required>
                        </div>
                        <div class="col-md-6">
                            <label for="periode" class="form-label fw-bold">Periode Batasan</label>
                            <select class="form-select" name="periode" id="periode" required>
                                <option value="mingguan" selected>Mingguan</option>
                                <option value="bulanan">Bulanan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="jumlah_limit" class="form-label fw-bold">Maksimal Penggunaan</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="jumlah_limit" id="jumlah_limit" value="1" min="1" required>
                                <span class="input-group-text">Kali</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Tutup
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
