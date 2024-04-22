<div class="modal fade" id="editUg" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="editUgLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUgLabel">
                    Edit Upah Gendong
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vehicle_id" class="form-label">Vehicle</label>
                            <select class="form-select" name="vehicle_id" id="edit_vehicle_id">
                                <option value="">-- PILIH NOMOR LAMBUNG --</option>
                                @foreach ($editVehicles as $vehicle)
                                <option value="{{$vehicle->id}}">{{$vehicle->nomor_lambung}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="nominal" class="form-label">Nominal</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control @if ($errors->has('nominal'))
                            is-invalid
                        @endif" name="nominal" id="edit_nominal" required value="{{old('nominal')}}">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tonase_min" class="form-label">Minimal Tonase</label>
                            <div class="input-group mb-3">

                                <input type="text" class="form-control @if ($errors->has('tonase_min'))
                                is-invalid
                            @endif" name="tonase_min" id="edit_tonase_min" required value="{{old('tonase_min')}}">
                                <span class="input-group-text" id="basic-addon1">Ton</span>
                              </div>
                              <small id="helpId" class="form-text text-danger">Gunakan "." untuk bilangan desimal</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nama_driver" class="form-label">Nama Driver</label>
                            <input type="text" class="form-control" name="nama_driver" id="edit_nama_driver"
                                aria-describedby="helpId" placeholder="" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_masuk_driver" class="form-label">Tanggal Masuk Driver</label>
                            <input type="text" class="form-control" name="tanggal_masuk_driver" id="edit_tanggal_masuk_driver"
                                aria-describedby="helpId" placeholder="" required readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nama_pengurus" class="form-label">Nama Pengurus</label>
                            <input type="text" class="form-control" name="nama_pengurus" id="edit_nama_pengurus"
                                aria-describedby="helpId" placeholder="" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_masuk_pengurus" class="form-label">Tanggal Masuk Pengurus</label>
                            <input type="text" class="form-control" name="tanggal_masuk_pengurus" id="edit_tanggal_masuk_pengurus"
                                aria-describedby="helpId" placeholder="" required readonly>
                        </div>
                        <hr>
                        <h4>INFO REKENING</h4>
                        <hr>
                        <div class="col-4 mb-3">
                            <label for="bank" class="form-label">Nama Bank</label>
                            <input type="text" bank class="form-control" name="bank" id="edit_bank"
                                aria-describedby="helpId" placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="no_rek" class="form-label">Nomor Rekening</label>
                            <input type="text" class="form-control" name="no_rek" id="edit_no_rek"
                                aria-describedby="helpId" placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="nama_rek" class="form-label">Nama Rekening</label>
                            <input type="text" class="form-control" name="nama_rek" id="edit_nama_rek"
                                aria-describedby="helpId" placeholder="" required>
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
