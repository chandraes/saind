<div class="modal fade" id="kirimWaModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="titleKirimWa" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titleKirimWa">
                    Kirim Mutasi ke WhatsApp
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="waForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <input type="text" class="form-control" id="nama_wa" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="tujuan" class="form-label">Nomor HP</label>
                                <input type="text" class="form-control" name="tujuan" id="tujuan" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batalkan
                    </button>
                    <button type="submit" class="btn btn-primary">Kirim <i class="fa fa-send"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
