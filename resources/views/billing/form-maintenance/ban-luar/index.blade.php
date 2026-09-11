@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Page & Navigasi Keranjang -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Form Penggantian Ban Luar</h3>
            <p class="text-muted mb-0">Pilih unit kendaraan dan catat posisi pemasangan ban.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('billing.form-maintenance.ban-luar.confirm') }}" class="btn btn-warning fw-bold position-relative">
                <i class="fa fa-shopping-cart me-1"></i> Lihat Keranjang & Konfirmasi
                <span id="badge-cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="{{ $cartCount > 0 ? '' : 'display: none;' }}">
                    {{ $cartCount }}
                </span>
            </a>
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    @include('swal')

    <div class="row">
        <!-- FORM INPUT BAN -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fa fa-wrench me-2"></i> Input Data Ban
                </div>
                <div class="card-body p-4">
                    <form id="addToCartForm">
                        @csrf

                        <!-- Pilihan Kendaraan -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pilih Kendaraan (Unit) <span class="text-danger">*</span></label>
                            <select name="vehicle_id" id="vehicle_id" class="form-select select2" required>
                                <option value="" disabled selected>-- Cari & Pilih Unit --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" {{ $lockedVehicleId == $v->id ? 'selected' : '' }}>
                                        {{ $v->nomor_lambung }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="vehicle_id" id="hidden_vehicle_id" value="{{ $lockedVehicleId }}">
                        </div>

                        <!-- Alert Jika Kendaraan Terkunci -->
                        <div id="alert-vehicle-locked" class="alert alert-warning py-2 px-3 small mb-3" style="{{ $lockedVehicleId ? '' : 'display: none;' }}">
                            <i class="fa fa-lock me-1"></i> Unit terkunci karena ada item di keranjang.
                            <a href="javascript:void(0)" id="btn-unlock-cart" class="text-danger fw-bold ms-1">Kosongkan Keranjang</a> untuk mengganti unit.
                        </div>

                        <!-- Form Penggantian -->
                        <div id="form-ganti-section" style="display: none;">
                            <hr class="mb-4">
                            <input type="hidden" name="sumber_ban" value="baru">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Posisi Ban yang Diganti <span class="text-danger">*</span></label>
                                <select name="posisi_ban_id" id="posisi_ban_id" class="form-select select2" required>
                                    <option value="" disabled selected>-- Pilih Posisi --</option>
                                    @foreach($posisiBans as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Merek Ban <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-uppercase" name="merk" id="merk" required placeholder="Contoh: BRIDGESTONE" autocomplete="off" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">No. Seri Ban <span class="text-danger">*</span></label>
                                <input type="text" class="form-control text-uppercase" name="no_seri" id="no_seri" required placeholder="Contoh: BS-12345" autocomplete="off" />
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Kondisi Awal (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control text-center fw-bold" name="kondisi" id="kondisi" value="100" min="1" max="100" required>
                            </div>

                            <button type="submit" id="btn-add-cart" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="fa fa-cart-plus me-1"></i> Tambahkan ke Keranjang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- INFO STATUS BAN KENDARAAN SAAT INI -->
        <div class="col-lg-8 mb-4">
            <div id="empty-state" class="card border-0 shadow-sm h-100 bg-light d-flex justify-content-center align-items-center p-5 text-center" style="min-height: 400px;">
                <i class="fa fa-truck fa-4x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted">Pilih unit kendaraan terlebih dahulu</h5>
                <p class="text-muted small">Informasi status ban unit akan muncul di sini.</p>
            </div>

            <div id="loading-state" class="card border-0 shadow-sm h-100 bg-light justify-content-center align-items-center p-5 text-center" style="display: none; min-height: 400px;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
                <h6 class="mt-3 text-muted fw-bold">Mengambil Data Kendaraan...</h6>
            </div>

            <div id="data-state" style="display: none;">
                <!-- Info Unit -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row text-center text-md-start">
                            <div class="col-md-3 border-end-md mb-2 mb-md-0">
                                <span class="text-muted fs-7 d-block">Nomor Lambung</span>
                                <span class="fw-bold fs-5 text-primary" id="lbl-lambung">-</span>
                            </div>
                            <div class="col-md-3 border-end-md mb-2 mb-md-0">
                                <span class="text-muted fs-7 d-block">Vendor</span>
                                <span class="fw-bold fs-6" id="lbl-vendor">-</span>
                            </div>
                            <div class="col-md-3 border-end-md mb-2 mb-md-0">
                                <span class="text-muted fs-7 d-block">Pengurus</span>
                                <span class="fw-bold fs-6" id="lbl-pengurus">-</span>
                            </div>
                            <div class="col-md-3">
                                <span class="text-muted fs-7 d-block">Driver</span>
                                <span class="fw-bold fs-6" id="lbl-driver">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Ban -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white fw-bold">
                        <i class="fa fa-list me-2"></i> Status Ban Saat Ini
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>POSISI BAN</th>
                                        <th class="text-center">MEREK</th>
                                        <th class="text-center">NO. SERI</th>
                                        <th class="text-center">KONDISI</th>
                                        <th class="text-center">RITASE</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-tires"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<style>
    @media (min-width: 768px) { .border-end-md { border-right: 1px solid #dee2e6 !important; } }
    .fs-7 { font-size: 0.85rem; }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function(){
        $('.select2').select2({ theme: 'bootstrap-5', width: '100%' });

        var isLocked = "{{ $lockedVehicleId ? '1' : '0' }}";

        if (isLocked === '1') {
            lockVehicleSelect(true);
        }

        function lockVehicleSelect(locked) {
            if (locked) {
                $('#vehicle_id').prop('disabled', true);
                $('#alert-vehicle-locked').slideDown();
            } else {
                $('#vehicle_id').prop('disabled', false);
                $('#alert-vehicle-locked').slideUp();
                $('#hidden_vehicle_id').val('');
            }
            $('#vehicle_id').select2({ theme: 'bootstrap-5', width: '100%' });
        }

        function loadVehicleData(vehicleId) {
            $.ajax({
                url: "{{ route('billing.form-maintenance.ban-luar.get-vehicle-info') }}",
                type: "GET",
                data: { vehicle_id: vehicleId },
                beforeSend: function() {
                    $('#empty-state').removeClass('d-flex').hide();
                    $('#data-state').hide();
                    $('#form-ganti-section').slideUp();
                    $('#loading-state').addClass('d-flex').fadeIn();
                },
                success: function(response) {
                    $('#lbl-lambung').text(response.vehicle.nomor_lambung);
                    $('#lbl-vendor').text(response.vehicle.vendor);
                    $('#lbl-pengurus').text(response.vehicle.pengurus);
                    $('#lbl-driver').text(response.vehicle.driver);

                    // 1. Render Status Ban Unit
                    var tbodyTires = '';
                    $.each(response.tires, function(index, tire) {
                        tbodyTires += '<tr>';
                        tbodyTires += '  <td class="fw-semibold text-primary">' + tire.posisi + '</td>';
                        tbodyTires += '  <td class="text-center text-uppercase">' + tire.merk + '</td>';
                        tbodyTires += '  <td class="text-center text-uppercase">' + tire.no_seri + '</td>';
                        tbodyTires += '  <td class="text-center">' + tire.kondisi + '</td>';
                        tbodyTires += '  <td class="text-center font-monospace fw-bold">' + tire.ritase + '</td>';
                        tbodyTires += '</tr>';
                    });
                    $('#tbody-tires').html(tbodyTires);

                    // 2. Disable Posisi Ban yang Sudah Ada di Keranjang
                    $('#posisi_ban_id option').each(function() {
                        var val = parseInt($(this).val());
                        if (response.used_posisi_ids.includes(val)) {
                            $(this).prop('disabled', true);
                        } else {
                            $(this).prop('disabled', false);
                        }
                    });
                    $('#posisi_ban_id').select2({ theme: 'bootstrap-5', width: '100%' });

                    // 3. Update Badge Jumlah Keranjang & Penguncian Vehicle
                    updateCartBadge(response.cart_count);
                    if (response.cart_count > 0) {
                        lockVehicleSelect(true);
                    }

                    setTimeout(function() {
                        $('#loading-state').removeClass('d-flex').hide();
                        $('#data-state').fadeIn();
                        $('#form-ganti-section').slideDown();
                    }, 200);
                }
            });
        }

        function updateCartBadge(count) {
            if (count > 0) {
                $('#badge-cart-count').text(count).show();
            } else {
                $('#badge-cart-count').hide();
            }
        }

        $('#vehicle_id').on('change', function() {
            var vehicleId = $(this).val();
            $('#hidden_vehicle_id').val(vehicleId);
            if (vehicleId) {
                loadVehicleData(vehicleId);
            }
        });

        // Add to Cart Form Submit + SWAL CONFIRMATION
        $('#addToCartForm').submit(function(e) {
            e.preventDefault();

            var posisiNama = $('#posisi_ban_id option:selected').text();
            var merk = $('#merk').val();
            var noSeri = $('#no_seri').val();

            Swal.fire({
                title: 'Tambahkan Ban ke Keranjang?',
                html: `
                    <div class="text-start">
                        <p class="mb-1"><b>Posisi Ban:</b> ${posisiNama}</p>
                        <p class="mb-1"><b>Merek:</b> ${merk.toUpperCase()}</p>
                        <p class="mb-0"><b>No. Seri:</b> ${noSeri.toUpperCase()}</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tambahkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    var btn = $('#btn-add-cart');
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...');

                    $.ajax({
                        url: "{{ route('billing.form-maintenance.ban-luar.cart.add') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            vehicle_id: $('#hidden_vehicle_id').val(),
                            posisi_ban_id: $('#posisi_ban_id').val(),
                            sumber_ban: $('input[name="sumber_ban"]').val(),
                            merk: $('#merk').val(),
                            no_seri: $('#no_seri').val(),
                            kondisi: $('#kondisi').val(),
                        },
                        success: function(response) {
                            btn.prop('disabled', false).html('<i class="fa fa-cart-plus me-1"></i> Tambahkan ke Keranjang');

                            $('#posisi_ban_id').val('').trigger('change');
                            $('#merk').val('');
                            $('#no_seri').val('');
                            $('#kondisi').val('100');

                            loadVehicleData($('#hidden_vehicle_id').val());

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message,
                                timer: 1200,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            btn.prop('disabled', false).html('<i class="fa fa-cart-plus me-1"></i> Tambahkan ke Keranjang');
                            var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menambahkan ban.';
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        });

        // Clear Cart & Unlock Vehicle Event
        $('#btn-unlock-cart').click(function() {
            var vehicleId = $('#hidden_vehicle_id').val();
            var clearRoute = "{{ route('billing.form-maintenance.ban-luar.cart.clear', ':id') }}";

            Swal.fire({
                title: 'Kosongkan Keranjang?',
                text: 'Item keranjang akan dihapus dan kunci unit kendaraan akan dibuka.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Kosongkan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: clearRoute.replace(':id', vehicleId),
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function() {
                            lockVehicleSelect(false);
                            updateCartBadge(0);
                            loadVehicleData(vehicleId);
                            Swal.fire('Terhapus', 'Keranjang telah dikosongkan.', 'success');
                        }
                    });
                }
            });
        });

        if ($('#vehicle_id').val()) {
            $('#vehicle_id').trigger('change');
        }
    });
</script>
@endpush
