<div class="modal fade" id="modalEdit{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleEdit{{$d->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="modalTitleEdit{{$d->id}}"><i class="fa fa-edit me-2"></i> Edit Vehicle: {{$d->nomor_lambung}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form action="{{route('vehicle.update', $d->id)}}" method="post" id="editForm{{$d->id}}">
                    @csrf
                    @method('PATCH')

                    <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold"><i class="fa fa-info-circle me-2"></i> Informasi Umum</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vendor_id_{{$d->id}}" class="form-label small fw-bold">VENDOR</label>
                            <select class="form-select" name="vendor_id" id="vendor_id_{{$d->id}}" required>
                                <option value=""> -- Pilih Vendor -- </option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ $d->vendor_id == $vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->nama }} {{ $vendor->perusahaan }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">NOMOR LAMBUNG</label>
                            <input type="text" class="form-control bg-light" value="{{$d->nomor_lambung}}" disabled readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nopol_{{$d->id}}" class="form-label small fw-bold">NOMOR POLISI</label>
                            <input type="text" class="form-control" name="nopol" id="nopol_{{$d->id}}" value="{{$d->nopol}}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nama_stnk_{{$d->id}}" class="form-label small fw-bold">NAMA STNK</label>
                            <input type="text" class="form-control" name="nama_stnk" id="nama_stnk_{{$d->id}}" value="{{$d->nama_stnk}}" required>
                        </div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 fw-bold"><i class="fa fa-cogs me-2"></i> Spesifikasi Teknis</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="no_rangka_{{$d->id}}" class="form-label small fw-bold">NO RANGKA</label>
                            <input type="text" class="form-control" name="no_rangka" id="no_rangka_{{$d->id}}" value="{{$d->no_rangka}}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="no_mesin_{{$d->id}}" class="form-label small fw-bold">NO MESIN</label>
                            <input type="text" class="form-control" name="no_mesin" id="no_mesin_{{$d->id}}" value="{{$d->no_mesin}}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="tipe_{{$d->id}}" class="form-label small fw-bold">TIPE</label>
                            <input type="text" class="form-control" name="tipe" id="tipe_{{$d->id}}" value="{{$d->tipe}}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tahun_{{$d->id}}" class="form-label small fw-bold">TAHUN</label>
                            <input type="number" class="form-control" name="tahun" id="tahun_{{$d->id}}" value="{{$d->tahun}}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="no_index_{{$d->id}}" class="form-label small fw-bold">NO INDEX</label>
                            <input type="number" step="any" class="form-control" name="no_index" id="no_index_{{$d->id}}" value="{{$d->no_index}}" required>
                        </div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 fw-bold"><i class="fa fa-folder-open me-2"></i> Dokumentasi & Status</h6>
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label for="tgl_stnk_{{$d->id}}" class="form-label small fw-bold">TGL PAJAK STNK</label>
                            <input type="text" class="form-control" name="tanggal_pajak_stnk" id="tgl_stnk_{{$d->id}}" value="{{$d->id_tanggal_pajak_stnk}}" required>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label for="tgl_kir_{{$d->id}}" class="form-label small fw-bold">TGL KIR</label>
                            <input type="text" class="form-control" name="tanggal_kir" id="tgl_kir_{{$d->id}}" value="{{$d->id_tanggal_kir}}" required>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label for="tgl_sim_{{$d->id}}" class="form-label small fw-bold">TGL SIM</label>
                            <input type="text" class="form-control" name="tanggal_sim" id="tgl_sim_{{$d->id}}" value="{{$d->id_tanggal_sim}}" required>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label for="tgl_kimper_{{$d->id}}" class="form-label small fw-bold">TGL KIMPER</label>
                            <input type="text" class="form-control" name="tanggal_kimper" id="tgl_kimper_{{$d->id}}" value="{{$d->id_tanggal_kimper}}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status_{{$d->id}}" class="form-label small fw-bold">STATUS KENDARAAN</label>
                            <select class="form-select" name="status" id="status_{{$d->id}}" required>
                                <option value="aktif" {{$d->status == 'aktif' ? 'selected' : ''}}>Aktif</option>
                                <option value="nonaktif" {{$d->status == 'nonaktif' ? 'selected' : ''}}>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lock_uj_{{$d->id}}" class="form-label small fw-bold text-danger">LOCK UJ SAAT KADALUARSA</label>
                            <select class="form-select border-danger" name="lock_uj" id="lock_uj_{{$d->id}}" required>
                                <option value="1" {{ $d->lock_uj == '1' ? 'selected' : '' }}>1 - Terkunci</option>
                                <option value="0" {{ $d->lock_uj == '0' ? 'selected' : '' }}>0 - Terbuka</option>
                            </select>
                        </div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 fw-bold"><i class="fa fa-wallet me-2"></i> Pengaturan Uang Jalan (UJ)</h6>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">APAKAH UJ DITAHAN?</label>
                        <select class="form-select border-primary" name="uj_ditahan" id="uj_ditahan_{{$d->id}}" onchange="toggleUjEdit('{{$d->id}}')" required>
                            <!-- Pastikan properti uj_ditahan dari DB ada nilainya dan ter-select -->
                            <option value="1" {{$d->uj_ditahan == 1 ? 'selected' : ''}}>Ya (Potong via Driver)</option>
                            <option value="0" {{$d->uj_ditahan == 0 ? 'selected' : ''}}>Tidak (Transfer Rekening)</option>
                        </select>
                    </div>

                    <!-- Input Driver (Akan aktif via JS jika UJ Ditahan = 1) -->
                    <div id="driver_section_{{$d->id}}" class="mb-3 p-3 bg-light border rounded" style="display: none;">
                        <label for="driver_id_{{$d->id}}" class="form-label small fw-bold text-danger">PILIH DRIVER</label>
                        <select class="form-select border-danger" name="driver_id" id="driver_id_{{$d->id}}">
                            <option value="">-- Pilih Driver --</option>
                            @foreach ($drivers as $dr)
                                <option value="{{ $dr->id }}" {{$d->driver_id == $dr->id ? 'selected' : ''}}>{{ $dr->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Rekening (Akan aktif via JS jika UJ Ditahan = 0) -->
                    <div id="banking_section_{{$d->id}}" class="p-3 bg-light border rounded mb-3" style="display: none;">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="transfer_ke_{{$d->id}}" class="form-label small fw-bold">TRANSFER KE</label>
                                <input type="text" class="form-control banking-input-{{$d->id}}" name="transfer_ke" id="transfer_ke_{{$d->id}}" value="{{$d->transfer_ke}}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="bank_{{$d->id}}" class="form-label small fw-bold">BANK</label>
                                <input type="text" class="form-control banking-input-{{$d->id}}" name="bank" id="bank_{{$d->id}}" value="{{$d->bank}}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="no_rekening_{{$d->id}}" class="form-label small fw-bold">NO REKENING</label>
                                <input type="text" class="form-control banking-input-{{$d->id}}" name="no_rekening" id="no_rekening_{{$d->id}}" value="{{$d->no_rekening}}">
                            </div>
                        </div>
                    </div>

                    <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 fw-bold"><i class="fa fa-map-marker-alt me-2"></i> Sistem Pelacakan (GPS)</h6>
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-3">
                            <div class="form-check form-switch fs-5 mt-3">
                                <input class="form-check-input" type="checkbox" role="switch" name="gps" id="gps_{{$d->id}}" {{$d->gps == 1 ? 'checked' : ''}}>
                                <label class="form-check-label small fw-bold mt-1" for="gps_{{$d->id}}">Aktifkan GPS</label>
                            </div>
                        </div>
                        <div class="col-md-9 mb-3">
                            <label for="no_kartu_gps_{{$d->id}}" class="form-label small fw-bold">NOMOR KARTU GPS</label>
                            <input type="text" class="form-control" name="no_kartu_gps" id="no_kartu_gps_{{$d->id}}" value="{{$d->no_kartu_gps}}">
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="editForm{{$d->id}}" class="btn btn-warning px-5 fw-bold"><i class="fa fa-save me-2"></i> Simpan Perubahan</button>
            </div>

        </div>
    </div>
</div>

<script>
    flatpickr("#tgl_stnk_{{$d->id}}", { dateFormat: "d-m-Y" });
    flatpickr("#tgl_kir_{{$d->id}}", { dateFormat: "d-m-Y" });
    flatpickr("#tgl_sim_{{$d->id}}", { dateFormat: "d-m-Y" });
    flatpickr("#tgl_kimper_{{$d->id}}", { dateFormat: "d-m-Y" });

    // Fungsi Toggle UJ untuk Form Edit
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

    // Eksekusi fungsi saat modal baru dimuat agar tampilan menyesuaikan data database saat ini
    $(document).ready(function() {
        toggleUjEdit('{{$d->id}}');
    });

    $('#editForm{{$d->id}}').submit(function(e){
        e.preventDefault();

        var tgl_stnk = $('#tgl_stnk_{{$d->id}}').val();
        var tgl_kir = $('#tgl_kir_{{$d->id}}').val();
        var tgl_sim = $('#tgl_sim_{{$d->id}}').val();
        var tgl_kimper = $('#tgl_kimper_{{$d->id}}').val();

        if (tgl_stnk === '') { alert('Tanggal Pajak STNK tidak boleh kosong'); $('#tgl_stnk_{{$d->id}}').focus(); return false; }
        if (tgl_kir === '') { alert('Tanggal KIR tidak boleh kosong'); $('#tgl_kir_{{$d->id}}').focus(); return false; }
        if (tgl_sim === '') { alert('Tanggal SIM tidak boleh kosong'); $('#tgl_sim_{{$d->id}}').focus(); return false; }
        if (tgl_kimper === '') { alert('Tanggal KIMPER tidak boleh kosong'); $('#tgl_kimper_{{$d->id}}').focus(); return false; }

        Swal.fire({
            title: 'Apakah data sudah benar?',
            text: "Pastikan data sudah benar sebelum disimpan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
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
