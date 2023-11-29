<div class="modal fade" id="uj{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg"
        role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Nota Tagihan
                    {{$d->kas_uang_jalan->vehicle->nomor_lambung}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Kode</label>
                            <input type="text" class="form-control" name="tanggal_muat"
                                id="tanggal_muat" placeholder="" value="UJ{{sprintf(" %02d",
                                $d->kas_uang_jalan->nomor_uang_jalan)}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Tanggal</label>
                            <input type="text" class="form-control" name="tanggal_uang_jalan"
                                id="tanggal_muat" placeholder=""
                                value="{{$d->kas_uang_jalan->tanggal}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="no_lambung" class="form-label">Nomor Lambung</label>
                            <input type="text" class="form-control" name="no_lambung"
                                id="no_lambung" placeholder=""
                                value="{{$d->kas_uang_jalan->vehicle->nomor_lambung}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="vendor" class="form-label">Vendor</label>
                            <input type="text" class="form-control" name="vendor" id="vendor"
                                placeholder="" value="{{$d->kas_uang_jalan->vendor->nickname}}"
                                readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tambang" class="form-label">Tambang</label>
                            <input type="text" class="form-control" name="tambang" id="tambang"
                                placeholder="" value="{{$d->kas_uang_jalan->customer->singkatan}}"
                                readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="rute" class="form-label">Rute</label>
                            <input type="text" class="form-control" name="rute" id="rute"
                                placeholder="" value="{{$d->kas_uang_jalan->rute->nama}}" readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="nota_muat" class="form-label">Nota Muat</label>
                            <input type="text" class="form-control" name="nota_muat" id="nota_muat"
                                placeholder="" value="{{$d->nota_muat}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tonase" class="form-label">Timbangan Muat</label>
                            <input type="text" class="form-control" name="tonase" id="tonase"
                                placeholder="" value="{{$d->tonase}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tonase" class="form-label">Tanggal Muat</label>
                            <input type="text" class="form-control" name="tonase" id="tonase"
                                placeholder="" value="{{$d->id_tanggal_muat}}" readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="nota_bongkar" class="form-label">Nota Bongkar</label>
                            <input type="text" class="form-control" name="nota_bongkar"
                                id="nota_bongkar" placeholder=""
                                value="{{$d->nota_bongkar ? $d->nota_bongkar : ''}}"
                                {{$d->nota_bongkar ? 'readonly' : ''}} readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="timbangan_bongkar" class="form-label">Timbangan
                                Bongkar</label>
                            <input type="text" class="form-control" name="timbangan_bongkar"
                                id="timbangan_bongkar" placeholder=""
                                value="{{$d->timbangan_bongkar ? $d->timbangan_bongkar : ''}}"
                                {{$d->timbangan_bongkar ? 'readonly' : ''}} readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tonase" class="form-label">Tanggal Bongkar</label>
                            <input type="text" class="form-control" name="tonase" id="tonase"
                                placeholder="" value="{{date('d M Y')}}" readonly>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
