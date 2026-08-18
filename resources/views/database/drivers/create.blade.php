<!-- Modal Form Driver -->
<div class="modal fade" id="driverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="driverForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="driver_id" name="id">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">Tambah Driver</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Driver <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama" id="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. SIM <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="no_sim" id="no_sim" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Masa Berlaku SIM <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="masa_berlaku_sim" id="masa_berlaku_sim" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="no_hp" id="no_hp" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bank <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="bank" id="bank" placeholder="BCA / Mandiri / BRI" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">No. Rekening <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="no_rek" id="no_rek" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nama Pemilik Rekening <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_rek" id="nama_rek" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="alamat" id="alamat" rows="2" required></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status Driver <span class="text-danger">*</span></label>
                            <select class="form-select" name="status" id="status" onchange="toggleKeteranganModal()">
                                <option value="aktif" selected>Aktif</option>
                                <option value="non_aktif">Non-Aktif</option>
                            </select>
                        </div>
                       <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Foto SIM <span class="text-danger" id="foto_sim_asterisk">*</span></label>
                            <input type="file" class="form-control" name="foto_sim" id="foto_sim" accept="image/*" required>
                            <small class="text-muted d-none" id="foto_sim_help">Kosongkan jika tidak ingin mengubah foto SIM.</small>
                        </div>
                    </div>

                    <!-- Input Keterangan: Otomatis Sembunyi, Hanya Tampil jika Status = Non-Aktif -->
                    <div class="mb-3" id="container_keterangan" style="display: none;">
                        <label class="form-label">Keterangan / Alasan Non-Aktif <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="keterangan" id="keterangan" rows="2" placeholder="Wajib diisi alasan penonaktifan driver"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
