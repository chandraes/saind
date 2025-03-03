<div class="modal fade" id="modalEdit{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleEdit{{$d->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleEdit{{$d->id}}">EDIT VEHICLE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('vehicle.update', $d->id)}}" method="post" id="editForm{{$d->id}}">
            @csrf
            @method('PATCH')

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vendor_id" class="form-label">Vendor</label>
                        <select class="form-select" name="vendor_id" id="vendor_id">
                            <option value=""> -- Pilih Vendor -- </option>
                            @foreach ($vendors as $vendor)
                            <option value="{{$vendor->id}}" {{$d->vendor_id == $vendor->id ? 'selected' : ''}}>{{$vendor->nama}} {{$vendor->perusahaan}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nomor_lambung" class="form-label">Nomor Lambung</label>
                        <input type="text" class="form-control" name="nomor_lambung" id="nomor_lambung" readonly value="{{$d->nomor_lambung}}" disabled>
                    </div>
                </div>
                <hr>
                <div class="" id="row-input">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nopol" class="form-label">Nomor Polisi</label>
                            <input type="text" class="form-control" name="nopol" id="nopol" value="{{$d->nopol}}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nama_stnk" class="form-label">Nama STNK</label>
                            <input type="text" class="form-control" name="nama_stnk" id="nama_stnk" value="{{$d->nama_stnk}}">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="no_rangka" class="form-label">Nomor Rangka</label>
                            <input type="text" class="form-control" name="no_rangka" id="no_rangka" value="{{$d->no_rangka}}">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="no_mesin" class="form-label">Nomor Mesin</label>
                            <input type="text" class="form-control" name="no_mesin" id="no_mesin" value="{{$d->no_mesin}}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="tipe" class="form-label">Tipe</label>
                            <input type="text" class="form-control" name="tipe" id="tipe" value="{{$d->tipe}}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="no_index" class="form-label">Index</label>
                            <input type="number" class="form-control" name="no_index" id="no_index" value="{{$d->no_index}}">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="tahun" class="form-label">Tahun</label>
                            <input type="number" class="form-control" name="tahun" id="tahun" value="{{$d->tahun}}">
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-3 mb-3">
                            <label for="tanggal_pajak_stnk" class="form-label">Tanggal Pajak STNK</label>
                            <input type="text" class="form-control" name="tanggal_pajak_stnk" id="tanggal_pajak_stnk{{$d->id}}" value="{{$d->tanggal_pajak_stnk ? $d->id_tanggal_pajak_stnk : ''}}"" readonly required>
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label for="tanggal_kir" class="form-label">Tanggal KIR</label>
                            <input type="text" class="form-control" name="tanggal_kir" id="tanggal_kir{{$d->id}}" value="{{$d->tanggal_kir ? $d->id_tanggal_kir : ''}}"" readonly required>
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label for="tanggal_sim" class="form-label">Tanggal SIM</label>
                            <input type="text" class="form-control" name="tanggal_sim" id="tanggal_sim{{$d->id}}" value="{{$d->tanggal_sim ? $d->id_tanggal_sim : ''}}"" readonly required>
                        </div>
                        <div class="col-lg-3 mb-3">
                            <label for="tanggal_kimper" class="form-label">Tanggal KIMPER</label>
                            <input type="text" class="form-control" name="tanggal_kimper" id="tanggal_kimper{{$d->id}}" value="{{$d->tanggal_kimper ? $d->id_tanggal_kimper : ''}}" required>
                        </div>
                        <div class="col-lg-6 mb-3">
                            <label for="tanggal_kimper" class="form-label">Lock UJ saat KIMPER / SIM Expired</label>
                            <select class="form-select" name="lock_uj" id="lock_uj" required>
                                <option value="0" {{$d->lock_uj == 0 ? 'selected' : ''}}>Tidak</option>
                                <option value="1" {{$d->lock_uj == 1 ? 'selected' : ''}}>Ya</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <h4>Rekening Uang Jalan</h4>
                        <div class="col-4 mt-2">
                            <div class="mb-3">
                              <label for="transfer_ke" class="form-label">Nama Rekening</label>
                              <input type="text"
                                class="form-control" name="transfer_ke" id="transfer_ke" value="{{$d->transfer_ke}}" required>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <div class="mb-3">
                              <label for="bank" class="form-label">Bank</label>
                              <input type="text"
                                class="form-control" name="bank" id="bank" value="{{$d->bank}}" required>
                            </div>
                        </div>
                        <div class="col-4 mt-2">
                            <div class="mb-3">
                              <label for="no_rekening" class="form-label">Nomor Rekening</label>
                              <input type="text"
                                class="form-control" name="no_rekening" id="no_rekening" value="{{$d->no_rekening}}" required>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                       <div class="btn-group mb-3" role="group" data-bs-toggle="buttons">
                        <label class="btn btn-warning">
                            <input type="checkbox" class="me-2" name="gps" id="gps" {{$d->gps == 1 ? 'checked' : ''}} autocomplete="off"> GPS
                        </label>
                       </div>

                        <div class="col-md-6 mb-3">
                            <label for="no_kartu_gps" class="form-label">Nomor Kartu GPS</label>
                            <input type="text" class="form-control" name="no_kartu_gps" id="no_kartu_gps" value="{{$d->no_kartu_gps}}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="aktif" {{$d->status == 'aktif' ? 'selected' : ''}}>Aktif</option>
                                <option value="nonaktif" {{$d->status == 'nonaktif' ? 'selected' : ''}}>Nonaktif</option>
                            </select>
                        </div>
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
<script>
    flatpickr("#tanggal_pajak_stnk{{$d->id}}", {
            dateFormat: "d-m-Y",

        });
        flatpickr("#tanggal_kir{{$d->id}}", {
            dateFormat: "d-m-Y",

        });

        flatpickr("#tanggal_sim{{$d->id}}", {
            dateFormat: "d-m-Y",

        });

        flatpickr("#tanggal_kimper{{$d->id}}", {
            dateFormat: "d-m-Y",
            required: true
        });

        // cek tanggal editForm on sumbit
        document.getElementById('editForm{{$d->id}}').addEventListener('submit', function(e) {
            var tanggal_pajak_stnk = document.getElementById('tanggal_pajak_stnk{{$d->id}}').value;
            var tanggal_kir = document.getElementById('tanggal_kir{{$d->id}}').value;
            var tanggal_sim = document.getElementById('tanggal_sim{{$d->id}}').value;
            var tanggal_kimper = document.getElementById('tanggal_kimper{{$d->id}}').value;

            if (tanggal_pajak_stnk == '') {
                e.preventDefault();
                alert('Tanggal Pajak STNK tidak boleh kosong');
                document.getElementById('tanggal_pajak_stnk{{$d->id}}').focus();
                return;
            }
            if (tanggal_kir == '') {
                e.preventDefault();
                alert('Tanggal KIR tidak boleh kosong');
                document.getElementById('tanggal_kir{{$d->id}}').focus();
                return;
            }
            if (tanggal_sim == '') {
                e.preventDefault();
                alert('Tanggal SIM tidak boleh kosong');
                document.getElementById('tanggal_sim{{$d->id}}').focus();
                return;
            }
            if (tanggal_kimper == '') {
                e.preventDefault();
                alert('Tanggal KIMPER tidak boleh kosong');
                document.getElementById('tanggal_kimper{{$d->id}}').focus();
                return;
            }
        });
</script>
