<div class="modal fade" id="createModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="createTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTitle">
                    Tambah Barang Maintenance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('database.barang-maintenance.store')}}" method="post" id="masukForm">
                @csrf

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Barang</label>
                            <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId"
                                placeholder="" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="harga_jual" class="form-label">Harga Jual</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="harga_jual" id="harga_jual"
                                    data-thousands=".">
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
