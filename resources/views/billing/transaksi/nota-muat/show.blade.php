<div class="modal fade" id="uj{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg"
        role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Nota Muat
                    {{$d->kas_uang_jalan->vehicle->nomor_lambung}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="{{route('transaksi.nota-muat.update', $d->id)}}" method="post"
                id="masukForm{{$d->id}}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Kode</label>
                            <input type="text" class="form-control" name="tanggal_muat"
                                id="tanggal_muat" placeholder="" value="UJ{{sprintf(" %02d",
                                $d->kas_uang_jalan->nomor_uang_jalan)}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Tanggal Uang Jalan</label>
                            <input type="text" class="form-control" name="tanggal_muat"
                                id="tanggal_muat" placeholder="" value="{{$d->kas_uang_jalan->tanggal}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Nomor Lambung</label>
                            <input type="text" class="form-control" name="tanggal_muat"
                                id="tanggal_muat" placeholder=""
                                value="{{$d->kas_uang_jalan->vehicle->nomor_lambung}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Vendor</label>
                            <input type="text" class="form-control" name="tanggal_muat"
                                id="tanggal_muat" placeholder=""
                                value="{{$d->kas_uang_jalan->vendor->nickname}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Tambang</label>
                            <input type="text" class="form-control" name="tanggal_muat"
                                id="tanggal_muat" placeholder=""
                                value="{{$d->kas_uang_jalan->customer->singkatan}}" readonly>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Rute</label>
                            <input type="text" class="form-control" name="tanggal_muat"
                                id="tanggal_muat" placeholder=""
                                value="{{$d->kas_uang_jalan->rute->nama}}" readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="nota_muat" class="form-label">Nota Muat</label>
                            <input type="text" class="form-control" name="nota_muat" id="nota_muat"
                                placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tonase" class="form-label">Tonase </label>
                            <input type="text" class="form-control" name="tonase" id="tonase"
                                placeholder="" required>
                                <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Tanggal Muat</label>
                            <input type="text" class="form-control" name="tanggal_muat" id="tanggal_muat-{{$d->id}}" required >
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>

        </div>
    </div>
</div>
