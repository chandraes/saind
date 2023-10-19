<!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
<div class="modal fade" id="create-storing" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Tambah BBM Storing</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('bbm-storing.store')}}" method="post" id="masukForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-4">
                            <div class="mb-3">
                                <label for="km" class="form-label">KM</label>
                                <input type="text" class="form-control" name="km" id="km" aria-describedby="helpId"
                                    placeholder="" required>
                            </div>
                        </div>
                        <div class="col-4">
                            <label for="biaya_vendor" class="form-label">Biaya Vendor</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control @if ($errors->has('biaya_vendor'))
                            is-invalid
                            @endif" name="biaya_vendor" id="biaya_vendor" required data-thousands=".">
                            </div>
                            @if ($errors->has('biaya_vendor'))
                            <div class="invalid-feedback">
                                {{$errors->first('biaya_vendor')}}
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="biaya_mekanik" class="form-label">Biaya Mekanik</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control @if ($errors->has('biaya_mekanik'))
                                is-invalid
                            @endif" name="biaya_mekanik" id="biaya_mekanik" required data-thousands=".">
                              </div>
                            @if ($errors->has('biaya_mekanik'))
                            <div class="invalid-feedback">
                                {{$errors->first('biaya_mekanik')}}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
