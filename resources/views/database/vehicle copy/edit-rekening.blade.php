<div class="modal fade" id="modalEditRekening{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleRekening{{$d->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-bold" id="modalTitleRekening{{$d->id}}">
                    <i class="fa fa-university me-2"></i> Edit Rekening: {{$d->nomor_lambung}}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form action="{{route('vehicle.update-rekening', $d->id)}}" method="post" id="formEditRekening{{$d->id}}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="transfer_ke_{{$d->id}}" class="form-label small fw-bold">TRANSFER KE (NAMA)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                            <input type="text" class="form-control" name="transfer_ke" id="transfer_ke_{{$d->id}}" value="{{$d->transfer_ke}}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="bank_{{$d->id}}" class="form-label small fw-bold">BANK</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-building"></i></span>
                            <input type="text" class="form-control" name="bank" id="bank_{{$d->id}}" value="{{$d->bank}}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="no_rekening_{{$d->id}}" class="form-label small fw-bold">NOMOR REKENING</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-credit-card"></i></span>
                            <input type="text" class="form-control" name="no_rekening" id="no_rekening_{{$d->id}}" value="{{$d->no_rekening}}" required>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formEditRekening{{$d->id}}" class="btn btn-info px-4 text-white fw-bold">
                    <i class="fa fa-save me-2"></i> Simpan Rekening
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    $('#formEditRekening{{$d->id}}').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Update Data Rekening?',
            text: "Pastikan nomor rekening dan nama bank sudah benar!",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0dcaf0', // Warna info bootstrap
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-check me-1"></i> Ya, simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#spinner').show();
                this.submit();
            }
        });
    });
</script>
