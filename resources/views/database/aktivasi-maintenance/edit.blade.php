<div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="createTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTitle">
                    Edit Aktivasi Maintenance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_id" class="form-label">Nomor Lambung</label>
                                <select class="form-select" name="vehicle_id" id="edit_vehicle_id">
                                    <option value="">-- Pilih Nomor Lambung --</option>
                                    @foreach ($editVehicles as $vehicle)
                                    <option value="{{$vehicle->id}}">{{$vehicle->nomor_lambung}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar calendar"></i></span>
                                    <input type="text" class="form-control calendar" name="tanggal_mulai" id="edit_tanggal_mulai" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batalkan
                    </button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
