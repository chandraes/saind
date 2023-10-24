<div class="modal fade" id="editBarang-{{$b->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="editBarangTitle-{{$b->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBarangTitle-{{$b->id}}">Edit Barang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('barang.update', $b->id)}}" method="post">
                @csrf
                @method('PATCH')

            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Barang</label>
                            <input type="text"
                              class="form-control" name="nama" id="nama" aria-describedby="helpId" placeholder="" value="{{$b->nama}}">
                          </div>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="harga_jual" class="form-label">Harga Jual Satuan</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Rp</span>
                            <input type="text" class="form-control @if ($errors->has('harga_jual'))
                            is-invalid
                        @endif" name="harga_jual" id="harga_jual-{{$b->id}}" required data-thousands="." value="{{$b->harga_jual}}">
                          </div>
                        @if ($errors->has('harga_jual'))
                        <div class="invalid-feedback">
                            {{$errors->first('harga_jual')}}
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
