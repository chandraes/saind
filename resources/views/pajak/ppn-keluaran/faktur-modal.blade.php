<div class="modal fade" id="modalFaktur" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">
                    Faktur Pajak
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="fakturForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nota" class="form-label">Nota</label>
                                <input type="text" class="form-control" name="nota" id="nota" disabled />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="nominal" class="form-label">Nominal</label>
                                <input type="text" class="form-control" name="nominal" id="nominal" disabled />
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="no_faktur" class="form-label h3">Nomor Faktur</label>
                                <input type="text" class="form-control h3" name="no_faktur" id="no_faktur" required style="font-size: 2rem; padding: 10px;"/>
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
@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>
     var no_faktur = new Cleave('#no_faktur', {
        numericOnly: true,
        delimiters: ['.', '-', '.'],
        blocks: [3, 3, 2, 8],
    });

    $('#fakturForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
