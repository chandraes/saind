<div class="modal fade" id="tambahModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="tambahTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahTitle">

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('vendor-operational.per-vendor.ban-luar.store')}}" method="post" id="createForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="vehicle_id" value="{{$vehicle->id}}">
                    <input type="hidden" name="posisi_ban_id" id="posisi_ban_id">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="merek" class="form-label">Merek</label>
                                <input type="text" class="form-control" name="merk" id="merk" aria-describedby="helpId" required />
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="no_seri" class="form-label">No. Seri Ban</label>
                                <input type="text" class="form-control" name="no_seri" id="no_seri" aria-describedby="helpId" required />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="kondisi" class="form-label">Kondisi</label>
                                <div class="input-group mb-3">
                                    <input type="number" class="form-control" name="kondisi" id="nominal_transaksi" required>
                                    <span class="input-group-text" id="basic-addon1">%</span>
                                  </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Tutup
                    </button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
