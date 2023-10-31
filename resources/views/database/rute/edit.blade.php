<div class="modal fade" id="modal{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Ubah Rute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('rute.update', ['rute' => $d->id])}}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Rute</label>
                                <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId" required
                                    placeholder="" value="{{$d->nama}}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="jarak" class="form-label">Jarak (Km)</label>
                                <input type="number" class="form-control" name="jarak" id="jarak" required step="any"
                                    aria-describedby="helpId" placeholder="" value="{{$d->jarak}}">
                            </div>
                        </div>
                        <div class="col-md-5">
                            {{-- form group input number with prefix Rp. show input in currency format --}}
                            <div class="mb-3">
                                <label for="uang_jalan" class="form-label">Uang Jalan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="text" class="form-control" name="uang_jalan" id="uang_jalan-{{$d->id}}" required
                                        aria-describedby="helpId" placeholder="" value="{{number_format($d->uang_jalan,0,',','.')}}"  data-thousands=".">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Ubah</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $('#uang_jalan-{{$d->id}}').maskMoney({
        thousands: '.',
        decimal: ',',
        precision: 0
    });

    $('#uang_jalan-{{$d->id}}').maskMoney('mask', {{$d->uang_jalan}});

</script>
