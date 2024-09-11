<div class="modal fade" id="modalCreate" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="legalitasTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="legalitasTitleId">
                    Tambah Legalitas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('legalitas.store')}}" method="post" enctype="multipart/form-data" id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="legalitas_kategori_id" class="form-label">Kategori</label>
                                <select class="form-select" name="legalitas_kategori_id" id="legalitas_kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($kategori as $k)
                                    <option value="{{$k->id}}">{{$k->nama}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Legalitas</label>
                                <input type="text" class="form-control" name="nama" id="nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="file" class="form-label">File <span class="text-danger">(Max 5Mb!)</span></label>
                                <input type="file" class="form-control" name="file" id="file" required>
                            </div>
                            <div class="mb-3">
                                <label class="btn btn-primary active">
                                    <input type="checkbox" class="me-2" name="apa_expired" id="apa_expired" autocomplete="off" onclick="checkApaExpired()" />
                                    Apakah dokumen memiliki masa berlaku?
                                </label>
                            </div>
                            <div class="mb-3" id="tgl_ex" style="display: none;">
                                <label for="tanggal_expired" class="form-label">Tanggal Expired</label>
                                <input type="text" readonly class="form-control" name="tanggal_expired" id="tanggal_expired">
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
