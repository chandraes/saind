@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-primary m-0">
                <i class="fa fa-truck me-2 text-secondary"></i>Kas Uang Jalan (Keluar)
            </h2>
            <p class="text-muted mt-1">Selesaikan form di bawah ini untuk memproses pengeluaran uang jalan.</p>
        </div>
    </div>

    @include('swal')

    <form action="{{route('kas-uang-jalan.keluar.store')}}" method="post" id="masukForm">
        @csrf
        <input type="hidden" id="old_rute_id" value="{{ old('rute_id') }}">
        <input type="hidden" id="min_tonase_config" value="{{ $limitValue }}">
        <input type="hidden" id="vendor_limit_status" value="0">
        <!-- Input hidden untuk status UJ Ditahan -->
        <input type="hidden" id="status_uj_ditahan" value="0">

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-3 fw-bold">
                <i class="fa fa-route me-2 text-primary"></i> Detail Perjalanan & Kendaraan
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="tanggal" class="form-label">Tanggal</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control" name="tanggal" id="tanggal" value="{{date('d M Y')}}" disabled>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="kode" class="form-label">Kode</label>
                        <div class="input-group">
                            <span class="input-group-text bg-secondary text-white fw-bold">UJ</span>
                            <input type="text" class="form-control" name="kode" value="{{sprintf("%02d", $nomor)}}" disabled>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="vehicle_id" class="form-label">Nomor Lambung <span class="text-danger">*</span></label>
                        <select class="form-select @error('vehicle_id') is-invalid @enderror" name="vehicle_id" id="vehicle_id" required>
                            <option value="" selected disabled>-- Pilih Nomor Lambung --</option>
                            @foreach ($vehicle as $v)
                            <option value="{{$v->id}}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>{{$v->nomor_lambung}}</option>
                            @endforeach
                        </select>
                        @error('vehicle_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-3" id="vendor" hidden>
                        <label for="vendor_id" class="form-label">Vendor</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-building"></i></span>
                            <input type="text" class="form-control" name="vendor_id" id="vendor_id" disabled>
                        </div>
                        <input type="hidden" name="p_vendor" id="p_vendor" readonly>
                    </div>

                    <div class="col-md-4">
                        <label for="customer_id" class="form-label">Tambang <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" name="customer_id" id="customer_id" required>
                            <option value="" selected disabled>-- Pilih Tambang --</option>
                            @foreach ($customer as $v)
                            <option value="{{$v->id}}" {{ old('customer_id') == $v->id ? 'selected' : '' }}>{{$v->singkatan}}</option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="rute_id" class="form-label">Rute <span class="text-danger">*</span></label>
                        <select class="form-select @error('rute_id') is-invalid @enderror" name="rute_id" id="rute_id" required>
                            <option value="">-- Pilih Rute --</option>
                        </select>
                        @error('rute_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Uang Jalan Sisa / Default -->
                    <div class="col-md-4" id="div_hk_uang_jalan">
                        <label for="hk_uang_jalan" class="form-label" id="label_hk_uang_jalan">Uang Jalan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold text-success">Rp</span>
                            <input type="text" class="form-control fw-bold text-success @error('nominal_transaksi') is-invalid @enderror" name="nominal_transaksi" id="hk_uang_jalan" required
                                @if(auth()->user()->role != 'admin') readonly @endif data-thousands="." value="{{ old('nominal_transaksi') }}">
                            @error('nominal_transaksi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Total Uang Jalan (Kotor) - Sembunyi secara default -->
                    <div class="col-md-4 mt-3" id="div_uang_jalan_kotor" style="display: none;">
                        <label for="uang_jalan_kotor" class="form-label">Total Uang Jalan (Kotor)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control bg-light" id="uang_jalan_kotor" readonly>
                        </div>
                    </div>

                    <!-- Potongan UJ Ditahan - Sembunyi secara default -->
                    <div class="col-md-4 mt-3" id="div_potongan_uj" style="display: none;">
                        <label for="potongan_uj" class="form-label">Potongan UJ Ditahan</label>
                        <div class="input-group">
                            <span class="input-group-text text-danger">- Rp</span>
                            <input type="text" class="form-control text-danger bg-light" name="potongan_uj" id="potongan_uj" readonly value="0">
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-3 fw-bold">
                <i class="fa fa-credit-card me-2 text-success"></i> Informasi Transfer Uang Jalan
            </div>
            <div class="card-body bg-light bg-opacity-50">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="transfer_ke" class="form-label">Nama Rekening</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                            <input type="text" class="form-control @error('transfer_ke') is-invalid @enderror" name="transfer_ke" id="transfer_ke" readonly required value="{{ old('transfer_ke') }}">
                            @error('transfer_ke') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="bank" class="form-label">Bank</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-university"></i></span>
                            <input type="text" class="form-control @error('bank') is-invalid @enderror" name="bank" id="bank" readonly required value="{{ old('bank') }}">
                            @error('bank') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="no_rekening" class="form-label">Nomor Rekening</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-credit-card"></i></span>
                            <input type="text" class="form-control @error('no_rekening') is-invalid @enderror" name="no_rekening" id="no_rekening" readonly required value="{{ old('no_rekening') }}">
                            @error('no_rekening') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-light py-3 fw-bold">
                <i class="fa fa-weight me-2 text-warning"></i> Data Muat
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nota_muat" class="form-label">Nota Muat <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-file"></i></span>
                            <input type="text" class="form-control @error('nota_muat') is-invalid @enderror" name="nota_muat" id="nota_muat" required value="{{ old('nota_muat') }}">
                            @error('nota_muat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_muat" class="form-label">Tanggal Muat <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control @error('tanggal_muat') is-invalid @enderror" name="tanggal_muat" id="tanggal_muat" required value="{{ old('tanggal_muat') }}">
                            @error('tanggal_muat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="col-md-12 mt-4" id="gt_muat_disable" hidden>
                        <label for="tonase_disable" class="form-label">Tonase <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control @error('tonase') is-invalid @enderror" name="tonase" id="tonase_disable" value="{{ old('tonase') }}">
                            <span class="input-group-text">TON</span>
                        </div>
                        <small class="form-text text-danger"><i class="fa fa-info-circle"></i> Gunakan titik (.) untuk pemisah desimal</small>
                        @error('tonase') <div class="d-block invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12 mt-4" id="gt_muat_enable">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="gross_muat" class="form-label">GROSS <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('gross_muat') is-invalid @enderror" name="gross_muat" id="gross_muat" required oninput="calNetto()" value="{{ old('gross_muat') }}">
                                    <span class="input-group-text">TON</span>
                                </div>
                                <small class="form-text text-danger"><i class="fa fa-info-circle"></i> Pemisah desimal (.)</small>
                                @error('gross_muat') <div class="d-block invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="tarra_muat" class="form-label">TARRA <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('tarra_muat') is-invalid @enderror" name="tarra_muat" id="tarra_muat" required oninput="calNetto()" value="{{ old('tarra_muat') }}">
                                    <span class="input-group-text">TON</span>
                                </div>
                                <small class="form-text text-danger"><i class="fa fa-info-circle"></i> Pemisah desimal (.)</small>
                                @error('tarra_muat') <div class="d-block invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="tonase_enable" class="form-label">NETTO <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light @error('tonase') is-invalid @enderror" name="tonase" id="tonase_enable" required readonly value="{{ old('tonase') }}">
                                    <span class="input-group-text text-success fw-bold">TON</span>
                                </div>
                                <small class="form-text text-muted"><i class="fa fa-calculator"></i> Dihitung otomatis</small>
                                @error('tonase') <div class="d-block invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-5">
            <div class="card-body d-flex justify-content-end gap-2 py-3">
                @if (auth()->user()->role == 'asisten-user')
                    <a href="{{route('home')}}" class="btn btn-secondary px-4"><i class="fa fa-times me-2"></i>Batal</a>
                @else
                    <a href="{{route('billing.index')}}" class="btn btn-secondary px-4"><i class="fa fa-times me-2"></i>Batal</a>
                @endif
                <button class="btn btn-success px-4" type="submit"><i class="fa fa-save me-2"></i>Simpan Data</button>
            </div>
        </div>

    </form>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endpush

@push('js')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    // Pisahkan fungsi AJAX agar bisa dipanggil berurutan (mencegah balapan/race condition)
    function loadVehicle(id, callback) {
        if (!id) return;
        $('#vendor').removeAttr('hidden');
        $.ajax({
            url: "{{route('kas-uang-jalan.get-vendor')}}",
            method: "GET",
            data: { id: id },
            success: function (data) {
                $('#vendor_id').val(data.nama_vendor);
                $('#p_vendor').val(data.id_vendor);
                $('#vendor_limit_status').val(data.limit_tonase);

                // --- LOGIKA TOGGLE TAMPILAN UJ DITAHAN ---
                // Pastikan variabel uj_ditahan dari backend terbaca
                let statusUjDitahan = data.uj_ditahan || 0;
                $('#status_uj_ditahan').val(statusUjDitahan);

                if (statusUjDitahan == 1) {
                    $('#div_uang_jalan_kotor').fadeIn();
                    $('#div_potongan_uj').fadeIn();
                    $('#label_hk_uang_jalan').html('Sisa Transfer (Netto) <span class="text-danger">*</span>');
                } else {
                    $('#div_uang_jalan_kotor').hide();
                    $('#div_potongan_uj').hide();
                    $('#label_hk_uang_jalan').html('Uang Jalan <span class="text-danger">*</span>');
                    $('#uang_jalan_kotor, #potongan_uj').val('');
                }
                // ------------------------------------------

                if (data.transfer_ke == null || data.bank == null || data.no_rekening == null) {
                    // Matikan sweet alert otomatis jika sedang load dari old() agar tidak spam
                    if(!callback) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Rekening Uang Jalan Vehicle ini belum diisi, silahkan isi terlebih dahulu di Database Vehicle!!',
                        });
                    }
                    $('#transfer_ke, #bank, #no_rekening').val('');
                } else {
                    $('#transfer_ke').val(data.transfer_ke);
                    $('#bank').val(data.bank);
                    $('#no_rekening').val(data.no_rekening);
                }

                if (callback) callback(); // Jalankan perintah selanjutnya jika ada
            }
        });
    }

    function loadCustomer(id, callback) {
        if (!id) return;

        var vehicleId = $('#vehicle_id').val();
        if (!vehicleId) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Silahkan pilih Nomor Lambung terlebih dahulu sebelum memilih Tambang!',
            });
            $('#customer_id').val('').trigger('change.select2');
            return;
        }

        $.ajax({
            url: "{{route('kas-uang-jalan.get-rute')}}",
            method: "GET",
            data: { id: id, vehicle_id: vehicleId },
            success: function (data) {
                // Tambahkan data-uang-jalan pada opsi kosong
                $('#rute_id').empty().append('<option value="" data-nominal="0" data-uang-jalan="0">-- Pilih Rute --</option>');

                $.each(data.rute, function (index, value) {
                    let nominalUjDitahan = value.uj_ditahan || 0;
                    let uangJalanKotor = value.hk_uang_jalan || 0; // Ambil nilai uang_jalan dari tabel rutes

                    // Sisipkan kedua nilai tersebut ke dalam tag option
                    $('#rute_id').append('<option value="' + value.id + '" data-nominal="' + nominalUjDitahan + '" data-uang-jalan="' + uangJalanKotor + '">' + value.nama + '</option>');
                });

                // (Logika Tampilan Disable/Enable Tonase tetap sama)
                let gt_muat = data.gt_muat;
                if (gt_muat == 0) {
                    $('#gt_muat_disable').removeAttr('hidden');
                    $('#gt_muat_enable').attr('hidden', 'hidden');

                    $('#tonase_disable').removeAttr('disabled').attr('required', 'required');
                    $('#tonase_enable, #gross_muat, #tarra_muat').attr('disabled', 'disabled').removeAttr('required');
                } else {
                    $('#gt_muat_enable').removeAttr('hidden');
                    $('#gt_muat_disable').attr('hidden', 'hidden');

                    $('#tonase_enable, #gross_muat, #tarra_muat').removeAttr('disabled').attr('required', 'required');
                    $('#tonase_disable').attr('disabled', 'disabled').removeAttr('required');
                }

                if (callback) callback();
            }
        });
    }

    $(document).ready(function () {
        // Format Nominal
        var nominal = new Cleave('#hk_uang_jalan', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        // Datepicker
        $( "#tanggal_muat" ).datepicker({
            dateFormat: "dd-mm-yy",
            minDate: {{$konfigurasi}} == 1 ? -2 : null,
            maxDate: 0
        }).attr('readonly', 'readonly');

        $('#vehicle_id, #customer_id, #rute_id').select2({
            theme: 'bootstrap-5'
        });

        // ==========================================
        // PENANGANAN SAAT HALAMAN DI-LOAD
        // ==========================================
        var initVehicle = $('#vehicle_id').val();
        var initCustomer = $('#customer_id').val();
        var oldRute = $('#old_rute_id').val();

        if (initVehicle && initCustomer) {
            loadVehicle(initVehicle, function() {
                loadCustomer(initCustomer, function() {
                    if (oldRute) {
                        $('#rute_id').val(oldRute).trigger('change');
                        $('#old_rute_id').val('');
                    }
                });
            });
        } else if (initVehicle) {
            loadVehicle(initVehicle);
        } else if (initCustomer) {
            loadCustomer(initCustomer, function() {
                if (oldRute) {
                    $('#rute_id').val(oldRute).trigger('change');
                    $('#old_rute_id').val('');
                }
            });
        }

        // ==========================================
        // EVENT ON CHANGE
        // ==========================================
        $('#vehicle_id').on('change', function () {
            loadVehicle($(this).val(), function() {
                if ($('#rute_id').val()) {
                    $('#rute_id').trigger('change');
                }
            });
        });

        $('#customer_id').on('change', function () {
            loadCustomer($(this).val(), function() {
                // Reset form uang jalan
                $('#uang_jalan_kotor, #potongan_uj, #hk_uang_jalan').val('');
            });
        });

      $('#rute_id').on('change', function () {
            var rute_id = $(this).val();
            var vendor_id = $('#p_vendor').val();

            var statusUjDitahan = $('#status_uj_ditahan').val();

            // Jika rute dikosongkan, reset semua nilai
            if (!rute_id) {
                $('#uang_jalan_kotor, #potongan_uj, #hk_uang_jalan').val('');
                return;
            }

            if (statusUjDitahan == 1) {
                // JIKA UJ DITAHAN: Ambil nilai langsung dari atribut opsi rute (tanpa AJAX VendorUangJalan)
                var nominalRute = $(this).find(':selected').data('nominal') || 0;
                var uangJalanKotor = $(this).find(':selected').data('uang-jalan') || 0;

                if (nominalRute <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal!',
                        text: 'Nominal Kesepakan UJ ditahan pada rute ini belum di isi. silahkan hubungi admin!!',
                    });

                    $(this).val('').trigger('change.select2');
                    $('#uang_jalan_kotor, #potongan_uj, #hk_uang_jalan').val('');
                    return false;
                }

                // Hitung netto dari rutes.uang_jalan - rutes.uj_ditahan
                var nettoUangJalan = uangJalanKotor + nominalRute;
                if (nettoUangJalan < 0) nettoUangJalan = 0;

                $('#uang_jalan_kotor').val(parseFloat(nettoUangJalan).toLocaleString('id-ID'));
                $('#potongan_uj').val(parseFloat(nominalRute).toLocaleString('id-ID'));
                $('#hk_uang_jalan').val(uangJalanKotor.toLocaleString('id-ID'));

            } else {
                // JIKA TIDAK DITAHAN: Ambil uang jalan dari VendorUangJalan seperti biasa
                if (rute_id && vendor_id) {
                    $.ajax({
                        url: "{{route('kas-uang-jalan.get-uang-jalan')}}",
                        method: "GET",
                        data: {
                            rute_id: rute_id,
                            vendor_id: vendor_id,
                        },
                        success: function (data) {
                            var baseUangJalan = parseFloat(data.hk_uang_jalan) || 0;
                            $('#hk_uang_jalan').val(baseUangJalan.toLocaleString('id-ID'));
                        }
                    });
                }
            }
        });

        // ==========================================
        // VALIDASI SUBMIT
        // ==========================================
        $('#masukForm').submit(function(e){
            e.preventDefault();

            const isLimitActive = $('#vendor_limit_status').val() == "1";
            const minRequired = parseFloat($('#min_tonase_config').val());
            const currentNetto = parseFloat($('#tonase_enable').val() || $('#tonase_disable').val() || 0);
            const userRole = "{{ auth()->user()->role }}";

            if (userRole !== 'admin' && isLimitActive && currentNetto < minRequired) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tonase Kurang!',
                    text: 'Vendor ini mewajibkan minimum tonase sebesar ' + minRequired + ' Ton. (Input saat ini: ' + currentNetto + ' Ton)',
                });
                return false;
            }

            Swal.fire({
                title: 'Apakah anda yakin data sudah benar?',
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
            });
        });
    });

    // Kalkulasi Netto
    function calNetto() {
       var gross = parseFloat(document.getElementById('gross_muat').value || 0);
       var tarra = parseFloat(document.getElementById('tarra_muat').value || 0);
       var netto = (gross - tarra).toFixed(2);
       document.getElementById('tonase_enable').value = netto;
    }
</script>
@endpush
