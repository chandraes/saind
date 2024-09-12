<div class="modal fade" id="modalCreate" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="legalitasTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="legalitasTitleId">
                    Tambah Mutasi Rekening
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" enctype="multipart/form-data" id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <input type="hidden" name="tahun" id="tahun">
                            <input type="hidden" name="bulan" id="bulan">
                            <div class="mb-3">

                                <input type="text" class="form-control" name="nama" id="nama" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="file" class="form-label">File <span class="text-danger">(Max 5Mb!)</span></label>
                                <input type="file" class="form-control" name="file" id="file" required>
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
@push('css')
<link href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
    <script>
        function checkApaExpired() {
        var checkBox = document.getElementById("apa_expired");
        var tgl_ex = document.getElementById("tgl_ex");
        var tanggalExpired = document.getElementById("tanggal_expired");

        if (checkBox.checked) {
            tgl_ex.style.display = "block";
            tanggalExpired.flatpickr({
                enableTime: false,
                dateFormat: "d-m-Y",
            });
            // make tanggal_expired required
            tanggalExpired.required = true;
        } else {
            tgl_ex.style.display = "none";
            tanggalExpired.value = '';
            // make tanggal_expired not required
            tanggalExpired.required = false;
        }
    }
    </script>
@endpush
