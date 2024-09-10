<div class="modal fade" id="modalEdit" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="legalitasTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="legalitasTitleId">
                    Edit Legalitas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" enctype="multipart/form-data" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="legalitas_kategori_id" class="form-label">Kategori</label>
                                <select class="form-select" name="legalitas_kategori_id" id="edit_legalitas_kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategori as $k)
                                    <option value="{{$k->id}}">{{$k->nama}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Legalitas</label>
                                <input type="text" class="form-control" name="nama" id="edit_nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="file" class="form-label">File <span class="text-danger">(Kosongkan jika tidak ingin mengganti file!) (Max 10Mb!)</span></label>
                                <input type="file" class="form-control" name="file" id="edit_file">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
