<div class="modal fade" id="modalTambahDokumen{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Tambah Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="{{route('customer.document-store', [$d->id])}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3">
                            <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                            <input type="text"
                                class="form-control @if ($errors->has('nama_dokumen')) is-invalid @endif"
                                name="nama_dokumen" id="nama_dokumen" required aria-describedby="helpId"
                                placeholder="">
                            @if ($errors->has('nama_dokumen'))
                            <div class="invalid-feedback">
                                {{$errors->first('nama_dokumen')}}
                            </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">File</label>
                            <input type="file"
                                class="form-control @if ($errors->has('file')) is-invalid @endif"
                                name="file" id="file" required aria-describedby="helpId"
                                placeholder="">
                            @if ($errors->has('file'))
                            <div class="invalid-feedback">
                                {{$errors->first('file')}}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
