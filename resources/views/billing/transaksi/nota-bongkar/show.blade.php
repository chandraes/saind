<div class="modal fade" id="uj{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg"
        role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Nota Bongkar
                    {{$d->kas_uang_jalan->vehicle->nomor_lambung}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="{{route('transaksi.nota-bongkar.update', $d->id)}}" method="post"
                id="masukForm{{$d->id}}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Kode</label>
                            <input type="text" class="form-control" name="tanggal_muat"
                                id="tanggal_muat" placeholder="" value="UJ{{sprintf("%02d",
                                $d->kas_uang_jalan->nomor_uang_jalan)}}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tanggal_muat" class="form-label">Tanggal</label>
                            <input type="text" class="form-control" name="tanggal_uang_jalan"
                                id="tanggal_muat" placeholder="" value="{{$d->kas_uang_jalan->tanggal}}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="no_lambung" class="form-label">Nomor Lambung</label>
                            <input type="text" class="form-control" name="no_lambung"
                                id="no_lambung" placeholder=""
                                value="{{$d->kas_uang_jalan->vehicle->nomor_lambung}}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="vendor" class="form-label">Vendor</label>
                            <input type="text" class="form-control" name="vendor" id="vendor"
                                placeholder="" value="{{$d->kas_uang_jalan->vendor->nickname}}"
                                readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tambang" class="form-label">Tambang</label>
                            <input type="text" class="form-control" name="tambang" id="tambang"
                                placeholder="" value="{{$d->kas_uang_jalan->customer->singkatan}}"
                                readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="rute" class="form-label">Rute</label>
                            <input type="text" class="form-control" name="rute" id="rute"
                                placeholder="" value="{{$d->kas_uang_jalan->rute->nama}}" readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nota_muat" class="form-label">Nota Muat</label>
                            <input type="text" class="form-control" name="nota_muat" id="nota_muat"
                                placeholder="" value="{{$d->nota_muat}}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tonase" class="form-label">Tonase Muat</label>
                            <input type="text" class="form-control" name="tonase" id="tonase"
                                placeholder="" value="{{$d->tonase}}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tonase" class="form-label">Tanggal Muat</label>
                            <input type="text" class="form-control" name="tonase" id="tonase"
                                placeholder="" value="{{$d->id_tanggal_muat}}" readonly>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nota_bongkar" class="form-label">Nota Bongkar</label>
                            <input type="text" class="form-control" name="nota_bongkar" id="nota_bongkar"
                                placeholder="" value="" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tanggal_bongkar" class="form-label">Tanggal Bongkar</label>
                            <input type="text" class="form-control" name="tanggal_bongkar" id="tanggal_bongkar-{{$d->id}}"
                                placeholder="" required>
                        </div>
                        @if ($d->kas_uang_jalan->customer->gt_bongkar == 0)
                        <div class="col-md-4 mb-3">
                            <label for="timbangan_bongkar" class="form-label">Tonase Bongkar</label>
                            <input type="text" class="form-control" name="timbangan_bongkar" id="timbangan_bongkar"
                                placeholder="" value="" required>
                                <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
                        </div>
                        @else
                        <div class="col-md-4 mb-3">
                            <label for="gross_bongkar" class="form-label">GROSS</label>
                            <input type="text" class="form-control" name="gross_bongkar" id="gross_bongkar-{{$d->id}}" placeholder="" required oninput="calNetto{{$d->id}}()">
                            <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
                            <br>
                            <br>
                            <label for="tarra_bongkar" class="form-label">TARRA</label>
                            <input type="text" class="form-control" name="tarra_bongkar" id="tarra_bongkar-{{$d->id}}" placeholder="" required  oninput="calNetto{{$d->id}}()">
                            <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
                            <br><br>
                            <label for="timbangan_bongkar" class="form-label">Tonase Bongkar</label>
                            <input type="text" class="form-control" name="timbangan_bongkar" id="timbangan_bongkar-{{$d->id}}"
                                placeholder="" value="" required readonly>
                                <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
                        </div>
                        @endif


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
<script>
    function calNetto{{$d->id}}() {
       var gross = parseFloat(document.getElementById('gross_bongkar-{{$d->id}}').value);
       var tarra = parseFloat(document.getElementById('tarra_bongkar-{{$d->id}}').value);
       var netto = gross - tarra;
       document.getElementById('timbangan_bongkar-{{$d->id}}').value = netto;
   }

</script>
