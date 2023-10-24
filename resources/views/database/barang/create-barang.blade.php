<div class="modal fade" id="create-barang" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="barang-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="barang-title">Tambah Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('barang.store')}}" method="post" id="masukBarangForm">
                @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="kategori_barang_id" class="form-label">Kategori Barang</label>
                            <select class="form-select" name="kategori_barang_id" id="kategori_barang_id" required>
                                <option value="">-- Pilih Kategori Barang --</option>
                                @foreach ($kategori as $i)
                                <option value="{{$i->id}}">{{$i->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                          <label for="nama" class="form-label">Nama Barang</label>
                          <input type="text"
                            class="form-control" name="nama" id="nama" aria-describedby="helpId" placeholder="">
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary">Tambah</button>
            </div>
        </form>
        </div>
    </div>
</div>
