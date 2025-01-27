<div class="modal fade" id="modalTambahVehicle" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="modalTitleTambah" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleTambah">Tambah Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('vehicle.store')}}" method="post" id="storeForm">
                @csrf

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vendor_id" class="form-label">Vendor</label>
                            <select class="form-select" name="vendor_id" id="vendor_id" onchange="toggleInputTambah()" required>
                                <option value=""> -- Pilih Vendor -- </option>
                                @foreach ($vendors as $vendor)
                                <option value="{{$vendor->id}}">{{$vendor->nama}} {{$vendor->perusahaan}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nomor_lambung" class="form-label">Nomor Lambung</label>
                            <input type="text" class="form-control" id="nomor_lambung"
                                value="{{$no_lambung === 1 ? 101 : $no_lambung}}" disabled>
                        </div>
                    </div>
                    <hr>
                    <div class="" id="row-input" hidden>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nopol" class="form-label">Nomor Polisi</label>
                                <input type="text" class="form-control" name="nopol" id="nopol" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama_stnk" class="form-label">Nama STNK</label>
                                <input type="text" class="form-control" name="nama_stnk" id="nama_stnk" required>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="no_rangka" class="form-label">Nomor Rangka</label>
                                <input type="text" class="form-control" name="no_rangka" id="no_rangka" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="no_mesin" class="form-label">Nomor Mesin</label>
                                <input type="text" class="form-control" name="no_mesin" id="no_mesin" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="tipe" class="form-label">Tipe</label>
                                <input type="text" class="form-control" name="tipe" id="tipe" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="no_index" class="form-label">Index</label>
                                <input type="number" class="form-control" name="no_index" id="no_index" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="tahun" class="form-label">Tahun</label>
                                <input type="number" class="form-control" name="tahun" id="tahun" required>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-lg-3 mb-3">
                                <label for="tanggal_pajak_stnk" class="form-label">Tanggal Pajak STNK</label>
                                <input type="text" class="form-control" name="tanggal_pajak_stnk" id="tanggal_pajak_stnk" readonly required>
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label for="tanggal_kir" class="form-label">Tanggal KIR</label>
                                <input type="text" class="form-control" name="tanggal_kir" id="tanggal_kir" readonly required>
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label for="tanggal_sim" class="form-label">Tanggal SIM</label>
                                <input type="text" class="form-control" name="tanggal_sim" id="tanggal_sim" readonly required>
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label for="tanggal_kimper" class="form-label">Tanggal KIMPER</label>
                                <input type="text" class="form-control" name="tanggal_kimper" id="tanggal_kimper" required>
                            </div>
                            <div class="col-lg-6 mb-3">
                                <label for="tanggal_kimper" class="form-label">Lock UJ saat KIMPER / SIM Expired</label>
                                <select class="form-select" name="lock_uj" id="lock_uj" required>
                                    <option value="">-- Pilih Salah Satu --</option>
                                    <option value="0">Tidak</option>
                                    <option value="1">Ya</option>
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
                                    class="form-control" name="transfer_ke" id="transfer_ke" required>
                                </div>
                            </div>
                            <div class="col-4 mt-2">
                                <div class="mb-3">
                                  <label for="bank" class="form-label">Bank</label>
                                  <input type="text"
                                    class="form-control" name="bank" id="bank" required>
                                </div>
                            </div>
                            <div class="col-4 mt-2">
                                <div class="mb-3">
                                  <label for="no_rekening" class="form-label">Nomor Rekening</label>
                                  <input type="text"
                                    class="form-control" name="no_rekening" id="no_rekening" required>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="btn-group mb-3" role="group" data-bs-toggle="buttons">
                                <label class="btn btn-warning">
                                    <input type="checkbox" class="me-2" name="gps" id="gps" autocomplete="off"> GPS
                                </label>
                               </div>
                            <div class="col-md-6 mb-3">
                                <label for="no_kartu_gps" class="form-label">Nomor Kartu GPS</label>
                                <input type="text" class="form-control" name="no_kartu_gps" id="no_kartu_gps" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
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
    flatpickr("#tanggal_pajak_stnk", {
            dateFormat: "d-m-Y",

        });
        flatpickr("#tanggal_kir", {
            dateFormat: "d-m-Y",

        });

        flatpickr("#tanggal_sim", {
            dateFormat: "d-m-Y",

        });

        flatpickr("#tanggal_kimper", {
            dateFormat: "d-m-Y",

        });

        document.getElementById('storeForm').addEventListener('submit', function(e) {
            var tanggal_pajak_stnk = document.getElementById('tanggal_pajak_stnk').value;
            var tanggal_kir = document.getElementById('tanggal_kir').value;
            var tanggal_sim = document.getElementById('tanggal_sim').value;
            var tanggal_kimper = document.getElementById('tanggal_kimper').value;

            if (tanggal_pajak_stnk == '') {
                e.preventDefault();
                alert('Tanggal Pajak STNK tidak boleh kosong');
                document.getElementById('tanggal_pajak_stnk').focus();
                return;
            }
            if (tanggal_kir == '') {
                e.preventDefault();
                alert('Tanggal KIR tidak boleh kosong');
                document.getElementById('tanggal_kir').focus();
                return;
            }
            if (tanggal_sim == '') {
                e.preventDefault();
                alert('Tanggal SIM tidak boleh kosong');
                document.getElementById('tanggal_sim').focus();
                return;
            }
            if (tanggal_kimper == '') {
                e.preventDefault();
                alert('Tanggal KIMPER tidak boleh kosong');
                document.getElementById('tanggal_kimper').focus();
                return;
            }
        });
</script>
