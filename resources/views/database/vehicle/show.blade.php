<div class="modal fade" id="modalShow{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Vehicle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vendor_id" class="form-label">Vendor</label>
                        <select class="form-select" name="vendor_id" id="vendor_id" readonly disabled>
                            <option value=""> -- Pilih Vendor -- </option>
                            @foreach ($vendors as $vendor)
                            <option value="{{$vendor->id}}" {{$d->vendor_id == $vendor->id ? 'selected' : ''}}>{{$vendor->nama}} {{$vendor->perusahaan}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nomor_lambung" class="form-label">Nomor Lambung</label>
                        <input type="text" class="form-control" name="nomor_lambung" id="nomor_lambung" readonly disabled value="{{$d->nomor_lambung}}">
                    </div>
                </div>
                <hr>
                <div class="" id="row-input">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nopol" class="form-label">Nomor Polisi</label>
                            <input type="text" class="form-control" name="nopol" id="nopol" readonly disabled value="{{$d->nopol}}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nama_stnk" class="form-label">Nama STNK</label>
                            <input type="text" class="form-control" name="nama_stnk" id="nama_stnk" readonly disabled value="{{$d->nama_stnk}}">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="no_rangka" class="form-label">Nomor Rangka</label>
                            <input type="text" class="form-control" name="no_rangka" id="no_rangka" readonly disabled value="{{$d->no_rangka}}">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="no_mesin" class="form-label">Nomor Mesin</label>
                            <input type="text" class="form-control" name="no_mesin" id="no_mesin" readonly disabled value="{{$d->no_mesin}}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tipe" class="form-label">Tipe</label>
                            <input type="text" class="form-control" name="tipe" id="tipe" readonly disabled value="{{$d->tipe}}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="no_index" class="form-label">Index</label>
                            <input type="text" class="form-control" name="no_index" id="no_index" readonly disabled value="{{$d->no_index}}">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <input type="number" class="form-control" name="tahun" id="tahun" readonly disabled value="{{$d->tahun}}">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <h4>Rekening Uang Jalan</h4>
                        <div class="col-4 mt-2">
                            <div class="mb-3">
                              <label for="transfer_ke" class="form-label">Nama Rekening</label>
                              <input type="text"
                                class="form-control" name="transfer_ke" id="transfer_ke" value="{{$d->transfer_ke}}" readonly disabled>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <div class="mb-3">
                              <label for="bank" class="form-label">Bank</label>
                              <input type="text"
                                class="form-control" name="bank" id="bank" value="{{$d->bank}}" readonly disabled>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <div class="mb-3">
                              <label for="no_rekening" class="form-label">Nomor Rekening</label>
                              <input type="text"
                                class="form-control" name="no_rekening" id="no_rekening" value="{{$d->no_rekening}}" readonly disabled>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="btn-group mb-3" role="group" data-bs-toggle="buttons">
                            <label class="btn btn-warning">
                                <input type="checkbox" class="me-2" name="gps" id="gps" {{$d->gps == 1 ? 'checked' : ''}} disabled autocomplete="off"> GPS
                            </label>
                           </div>
                        <div class="col-md-6 mb-3">
                            <label for="no_kartu_gps" class="form-label">Nomor Kartu GPS</label>
                            <input type="text" class="form-control" name="no_kartu_gps" id="no_kartu_gps" readonly disabled value="{{$d->no_kartu_gps}}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status" readonly disabled>
                                <option value="aktif" {{$d->status == 'aktif' ? 'selected' : ''}}>Aktif</option>
                                <option value="nonaktif" {{$d->status == 'nonaktif' ? 'selected' : ''}}>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalEdit{{$d->id}}">
                    Edit
                  </button>
            </div>
        </div>
    </div>
</div>
