<div class="modal fade" id="modalTambahVehicle" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleTambah" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalTitleTambah"><i class="fa fa-plus-circle me-2"></i> Tambah Vehicle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form action="{{route('vehicle.store')}}" method="post" id="storeForm">
                    @csrf

                    <h6 class="text-primary border-bottom pb-2 mb-3 fw-bold"><i class="fa fa-info-circle me-2"></i> Informasi Umum</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vendor_id" class="form-label small fw-bold">VENDOR</label>
                            <select class="form-select border-primary" name="vendor_id" id="vendor_id" onchange="toggleInputTambah()" required>
                                <option value=""> -- Pilih Vendor Terlebih Dahulu -- </option>
                                @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->nama}} </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nomor_lambung" class="form-label small fw-bold">NOMOR LAMBUNG</label>
                            <input type="text" class="form-control bg-light" id="nomor_lambung" value="{{$no_lambung === 1 ? 101 : $no_lambung}}" disabled readonly>
                        </div>
                    </div>

                    <!-- MENGGUNAKAN FIELDSET DISABLED AGAR SEMUA INPUT DI DALAMNYA TERKUNCI -->
                    <fieldset id="fieldset-tambah" disabled>

                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label for="nopol" class="form-label small fw-bold">NOMOR POLISI</label>
                                <input type="text" class="form-control" name="nopol" id="nopol" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama_stnk" class="form-label small fw-bold">NAMA STNK</label>
                                <input type="text" class="form-control" name="nama_stnk" id="nama_stnk" required>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 fw-bold"><i class="fa fa-cogs me-2"></i> Spesifikasi Teknis</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="no_rangka" class="form-label small fw-bold">NO RANGKA</label>
                                <input type="text" class="form-control" name="no_rangka" id="no_rangka" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="no_mesin" class="form-label small fw-bold">NO MESIN</label>
                                <input type="text" class="form-control" name="no_mesin" id="no_mesin" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tipe" class="form-label small fw-bold">TIPE</label>
                                <input type="text" class="form-control" name="tipe" id="tipe" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tahun" class="form-label small fw-bold">TAHUN</label>
                                <input type="number" class="form-control" name="tahun" id="tahun" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="no_index" class="form-label small fw-bold">NO INDEX</label>
                                <input type="number" step="any" class="form-control" name="no_index" id="no_index" required>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 fw-bold"><i class="fa fa-folder-open me-2"></i> Dokumentasi & Status</h6>
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label for="tanggal_pajak_stnk" class="form-label small fw-bold">TGL PAJAK STNK</label>
                                <input type="text" class="form-control bg-white" name="tanggal_pajak_stnk" id="tanggal_pajak_stnk" readonly required>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label for="tanggal_kir" class="form-label small fw-bold">TGL KIR</label>
                                <input type="text" class="form-control bg-white" name="tanggal_kir" id="tanggal_kir" readonly required>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label for="tanggal_sim" class="form-label small fw-bold">TGL SIM</label>
                                <input type="text" class="form-control bg-white" name="tanggal_sim" id="tanggal_sim" readonly required>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <label for="tanggal_kimper" class="form-label small fw-bold">TGL KIMPER</label>
                                <input type="text" class="form-control bg-white" name="tanggal_kimper" id="tanggal_kimper" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label small fw-bold">STATUS KENDARAAN</label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lock_uj" class="form-label small fw-bold text-danger">LOCK UJ SAAT KADALUARSA</label>
                                <select class="form-select border-danger" name="lock_uj" id="lock_uj" required>
                                    <option value="">-- Pilih Salah Satu --</option>
                                    <option value="0">0 - Terbuka</option>
                                    <option value="1">1 - Terkunci</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 fw-bold"><i class="fa fa-wallet me-2"></i> Pengaturan Uang Jalan (UJ)</h6>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">APAKAH UJ DITAHAN?</label>
                            <select class="form-select border-primary" name="uj_ditahan" id="uj_ditahan_create" onchange="toggleUjCreate()" required>
                                <option value="">-- Pilih Pengaturan UJ --</option>
                                <option value="1">Ya (Potong via Driver)</option>
                                <option value="0">Tidak (Transfer Rekening)</option>
                            </select>
                        </div>

                        <!-- Form Driver (Secara default disembunyikan menggunakan inline style none agar transisi js halus) -->
                        <div id="driver_section_create" class="mb-3 p-3 bg-light border rounded" style="display: none;">
                            <label for="driver_id_create" class="form-label small fw-bold text-danger">PILIH DRIVER</label>
                            <select class="form-select border-danger" name="driver_id" id="driver_id_create">
                                <option value="">-- Pilih Driver --</option>
                                @foreach ($drivers as $dr)
                                    <option value="{{ $dr->id }}">{{ $dr->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Form Rekening (Secara default disembunyikan menggunakan inline style none) -->
                        <div id="banking_section_create" class="p-3 bg-light border rounded mb-3" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="transfer_ke_create" class="form-label small fw-bold">TRANSFER KE (NAMA)</label>
                                    <input type="text" class="form-control banking-input-create" name="transfer_ke" id="transfer_ke_create">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="bank_create" class="form-label small fw-bold">BANK</label>
                                    <input type="text" class="form-control banking-input-create" name="bank" id="bank_create">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="no_rekening_create" class="form-label small fw-bold">NO REKENING</label>
                                    <input type="text" class="form-control banking-input-create" name="no_rekening" id="no_rekening_create">
                                </div>
                            </div>
                        </div>

                        <h6 class="text-primary border-bottom pb-2 mt-4 mb-3 fw-bold"><i class="fa fa-map-marker-alt me-2"></i> Sistem Pelacakan (GPS)</h6>
                        <div class="row align-items-center">
                            <div class="col-md-3 mb-3">
                                <div class="form-check form-switch fs-5 mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="gps" id="gps_create">
                                    <label class="form-check-label small fw-bold mt-1" for="gps_create">Aktifkan GPS</label>
                                </div>
                            </div>
                            <div class="col-md-9 mb-3">
                                <label for="no_kartu_gps" class="form-label small fw-bold">NOMOR KARTU GPS</label>
                                <input type="text" class="form-control" name="no_kartu_gps" id="no_kartu_gps" required>
                            </div>
                        </div>

                    </fieldset> <!-- Penutup fieldset -->
                </form>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="storeForm" class="btn btn-primary px-5 fw-bold"><i class="fa fa-save me-2"></i> Simpan Data</button>
            </div>

        </div>
    </div>
</div>

<script>
    // Inisialisasi Flatpickr
    flatpickr("#tanggal_pajak_stnk", { dateFormat: "d-m-Y" });
    flatpickr("#tanggal_kir", { dateFormat: "d-m-Y" });
    flatpickr("#tanggal_sim", { dateFormat: "d-m-Y" });
    flatpickr("#tanggal_kimper", { dateFormat: "d-m-Y" });

    // Fungsi untuk Disable / Enable seluruh form berdasarkan pilihan vendor
    function toggleInputTambah() {
        var vendorVal = $('#vendor_id').val();

        // Jika vendor kosong, kunci (disable) fieldset
        if (vendorVal === '') {
            $('#fieldset-tambah').prop('disabled', true);
        } else {
            // Jika vendor dipilih, buka (enable) fieldset
            $('#fieldset-tambah').prop('disabled', false);
        }
    }

    // Toggle logic untuk form Uang Jalan (Rekening / Driver)
    function toggleUjCreate() {
        let val = $('#uj_ditahan_create').val();

        if (val === '1') {
            $('#banking_section_create').hide();
            $('.banking-input-create').removeAttr('required').val('');

            $('#driver_section_create').show();
            $('#driver_id_create').attr('required', true);
        } else if (val === '0') {
            $('#driver_section_create').hide();
            $('#driver_id_create').removeAttr('required').val('');

            $('#banking_section_create').show();
            $('.banking-input-create').attr('required', true);
        } else {
            $('#driver_section_create').hide();
            $('#banking_section_create').hide();
            $('#driver_id_create').removeAttr('required');
            $('.banking-input-create').removeAttr('required');
        }
    }

    // Validasi submit form (memastikan kalender flatpickr terisi)
    document.getElementById('storeForm').addEventListener('submit', function(e) {
        var tgl_stnk = $('#tanggal_pajak_stnk').val();
        var tgl_kir = $('#tanggal_kir').val();
        var tgl_sim = $('#tanggal_sim').val();
        var tgl_kimper = $('#tanggal_kimper').val();

        if (tgl_stnk === '') { e.preventDefault(); alert('Tanggal Pajak STNK tidak boleh kosong'); $('#tanggal_pajak_stnk').focus(); return; }
        if (tgl_kir === '') { e.preventDefault(); alert('Tanggal KIR tidak boleh kosong'); $('#tanggal_kir').focus(); return; }
        if (tgl_sim === '') { e.preventDefault(); alert('Tanggal SIM tidak boleh kosong'); $('#tanggal_sim').focus(); return; }
        if (tgl_kimper === '') { e.preventDefault(); alert('Tanggal KIMPER tidak boleh kosong'); $('#tanggal_kimper').focus(); return; }
    });
</script>
