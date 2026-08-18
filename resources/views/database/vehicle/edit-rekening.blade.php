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

                    <div class="mb-4">
                        <label class="form-label small fw-bold">APAKAH UJ DITAHAN?</label>
                        <select class="form-select border-info" name="uj_ditahan" id="uj_ditahan_{{$d->id}}" onchange="toggleUjEdit('{{$d->id}}')" required>
                            <option value="1" {{$d->uj_ditahan == 1 ? 'selected' : ''}}>Ya (Potong via Driver)</option>
                            <option value="0" {{$d->uj_ditahan == 0 ? 'selected' : ''}}>Tidak (Transfer Rekening)</option>
                        </select>
                    </div>

                    <!-- Input Driver -->
                    <div id="driver_section_{{$d->id}}" class="mb-3 p-3 bg-light border rounded" style="display: none;">
                        <label for="driver_id_{{$d->id}}" class="form-label small fw-bold text-danger">PILIH DRIVER</label>
                        <select class="form-select" name="driver_id" id="driver_id_{{$d->id}}">
                            <option value="">-- Pilih Driver --</option>
                            @foreach ($drivers as $dr)
                                <option value="{{ $dr->id }}" {{$d->driver_id == $dr->id ? 'selected' : ''}}>{{ $dr->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Rekening -->
                    <div id="banking_section_{{$d->id}}" style="display: none;">
                        <div class="p-3 bg-light border rounded">
                            <div class="mb-3">
                                <label for="transfer_ke_{{$d->id}}" class="form-label small fw-bold">TRANSFER KE (NAMA)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    <input type="text" class="form-control banking-input-{{$d->id}}" name="transfer_ke" id="transfer_ke_{{$d->id}}" value="{{$d->transfer_ke}}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="bank_{{$d->id}}" class="form-label small fw-bold">BANK</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-building"></i></span>
                                    <input type="text" class="form-control banking-input-{{$d->id}}" name="bank" id="bank_{{$d->id}}" value="{{$d->bank}}">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="no_rekening_{{$d->id}}" class="form-label small fw-bold">NOMOR REKENING</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-credit-card"></i></span>
                                    <input type="text" class="form-control banking-input-{{$d->id}}" name="no_rekening" id="no_rekening_{{$d->id}}" value="{{$d->no_rekening}}">
                                </div>
                            </div>
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
    function toggleUjEdit(id) {
        let val = $('#uj_ditahan_' + id).val();

        if (val == '1') {
            $('#banking_section_' + id).hide();
            $('.banking-input-' + id).removeAttr('required');

            $('#driver_section_' + id).show();
            $('#driver_id_' + id).attr('required', true);
        } else {
            $('#driver_section_' + id).hide();
            $('#driver_id_' + id).removeAttr('required');

            $('#banking_section_' + id).show();
            $('.banking-input-' + id).attr('required', true);
        }
    }

    // Eksekusi fungsi saat modal baru dimuat agar tampilan sesuai dengan isi database
    $(document).ready(function() {
        toggleUjEdit('{{$d->id}}');
    });
    
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
