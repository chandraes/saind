<div class="modal fade" id="createInvestor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="investorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="investorTitle">Tambah Biodata Kreditor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('database.kreditor.store')}}" method="post" id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-md-2 mb-3">
                            <label for="persen" class="form-label">Persentase</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="persen" id="persen" required>
                                <span class="input-group-text" id="basic-addon1">%</span>
                              </div>
                        </div>
                        {{-- checkbox apa_pph --}}
                        <div class="col-md-2 mb-3">
                            <label for="apa_pph" class="form-label">Apa PPH</label>
                            <select class="form-select" name="apa_pph" id="apa_pph" required>
                                <option selected value="1">Ya</option>
                                <option value="0">Tidak</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="npwp" class="form-label">NPWP</label>
                            <input type="text" class="form-control" name="npwp" id="npwp" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                    </div>
                    <hr>
                    <h3>Info Rekening</h3>
                    <br>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="nama_rek" class="form-label">Nama Rekening</label>
                            <input type="text" class="form-control" name="nama_rek" id="nama_rek" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="no_rek" class="form-label">No Rekening</label>
                            <input type="text" class="form-control" name="no_rek" id="no_rek" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="bank" class="form-label">Bank</label>
                            <input type="text" class="form-control" name="bank" id="bank" aria-describedby="helpId"
                                placeholder="" required>
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
@push('js')
    <script>
          var npwp = new Cleave('#npwp', {
                delimiters: ['.', '.', '.', '-','.','.'],
                blocks: [2, 3, 3, 1, 3, 3],
            });
    </script>
@endpush
