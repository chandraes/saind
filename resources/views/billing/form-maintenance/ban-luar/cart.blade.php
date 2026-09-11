@extends('layouts.app')

@section('content')
<div class="container px-4 py-4">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Konfirmasi Invoice Penggantian Ban</h3>
            <p class="text-muted mb-0">Tinjau kembali rincian ban sebelum menerbitkan invoice transaksi.</p>
        </div>
        <div>
            <a href="{{ route('billing.form-maintenance.ban-luar') }}" class="btn btn-outline-primary fw-bold">
                <i class="fa fa-plus me-1"></i> Tambah Ban Lagi
            </a>
        </div>
    </div>

    @include('swal')

    <div class="row">
        <!-- RINGKASAN UNIT KENDARAAN -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-4">
                    <div class="row text-center text-md-start align-items-center">
                        <div class="col-md-3 border-end-md mb-3 mb-md-0">
                            <span class="text-white-50 fs-7 d-block">Nomor Lambung</span>
                            <span class="fw-bold fs-4">{{ $vehicle->nomor_lambung }}</span>
                        </div>
                        <div class="col-md-3 border-end-md mb-3 mb-md-0">
                            <span class="text-white-50 fs-7 d-block">Vendor</span>
                            <span class="fw-bold fs-6">{{ $vehicle->nama_vendor ?? '-' }}</span>
                        </div>
                        <div class="col-md-3 border-end-md mb-3 mb-md-0">
                            <span class="text-white-50 fs-7 d-block">Pengurus</span>
                            <span class="fw-bold fs-6">{{ $vehicle->pengurus ?? '-' }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-white-50 fs-7 d-block">Driver</span>
                            <span class="fw-bold fs-6">{{ $vehicle->nama_driver ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RINCIAN KERANJANG & CHECKOUT FORM -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="fa fa-shopping-cart text-warning me-2"></i> Rincian Item Ban Dalam Keranjang
                    </h5>
                    <button type="button" id="btn-clear-cart" class="btn btn-sm btn-outline-danger">
                        <i class="fa fa-trash me-1"></i> Kosongkan Keranjang
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>NO</th>
                                    <th>POSISI BAN</th>
                                    <th>MEREK BAN</th>
                                    <th>NO. SERI BAN</th>
                                    <th>KONDISI AWAL</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cartItems as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td class="fw-bold text-primary">{{ $item->posisiBan->nama }}</td>
                                        <td class="fw-bold text-uppercase">{{ $item->merk }}</td>
                                        <td class="text-uppercase">{{ $item->no_seri }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark fs-7">{{ $item->kondisi }}%</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-item" data-id="{{ $item->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FORM CHECKOUT METODE PEMBAYARAN -->
                <div class="card-footer bg-light p-4">
                    <form action="{{ route('billing.form-maintenance.ban-luar.checkout') }}" method="POST" id="checkoutForm">
                        @csrf
                        <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                        <h6 class="fw-bold text-dark mb-3">Informasi Pembayaran Invoice</h6>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Metode Pembayaran <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="pembayaran" id="bayar_sendiri" value="{{ \App\Models\BanGantiInvoice::PEMBAYARAN_DIBAYAR_SENDIRI }}" checked>
                                    <label class="form-check-label fw-bold" for="bayar_sendiri">Dibayar Sendiri</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="pembayaran" id="bayar_kas" value="{{ \App\Models\BanGantiInvoice::PEMBAYARAN_KAS_BESAR }}">
                                    <label class="form-check-label text-primary fw-bold" for="bayar_kas">Kas Besar</label>
                                </div>
                            </div>
                        </div>

                        <!-- GRUP INPUTAN KAS BESAR (Disembunyikan secara default) -->
                        <div id="group-kas-besar-details" style="display: none;" class="bg-white p-4 rounded border mb-4 shadow-sm">
                            <h6 class="fw-bold mb-3 pb-2 border-bottom text-primary"><i class="fa fa-university me-2"></i>Detail Transfer Kas Besar</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Total Nominal (Rp) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control fw-bold form-control-lg text-primary" name="total_nominal" id="total_nominal" placeholder="Contoh: 1.500.000" autocomplete="off">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">Bank Tujuan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg text-uppercase" name="nama_bank" id="nama_bank" placeholder="Contoh: BCA, MANDIRI" autocomplete="off" maxlength="10">
                                </div>
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold">Nomor Rekening <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nomor_rekening" id="nomor_rekening" placeholder="Contoh: 1234567890" autocomplete="off" >
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Transfer Ke (Atas Nama) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control text-uppercase" name="nama_rekening" id="nama_rekening" placeholder="Contoh: PT MAJU BERSAMA" autocomplete="off" maxlength="15">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('billing.form-maintenance.ban-luar') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Kembali Tambah Item
                            </a>
                            <button type="submit" class="btn btn-success btn-lg fw-bold px-4">
                                <i class="fa fa-check-circle me-1"></i> Lanjutkan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>

<script>
    $(document).ready(function(){
        // Inisialisasi Cleave.js Format Currency / Number
        var totalNominalCleave = new Cleave('#total_nominal', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: '.',
            numeralDecimalMark: ','
        });

        // Toggle input form Kas Besar
        $('input[name="pembayaran"]').change(function() {
            if ($(this).val() === 'kas_besar') {
                $('#group-kas-besar-details').slideDown();
                // Wajibkan pengisian form kas besar
                $('#total_nominal, #nama_bank, #nomor_rekening, #nama_rekening').attr('required', true);
            } else {
                $('#group-kas-besar-details').slideUp();
                // Hapus required dan kosongkan nilai form
                $('#total_nominal, #nama_bank, #nomor_rekening, #nama_rekening').removeAttr('required').val('');
                totalNominalCleave.setRawValue('');
            }
        });

        // Checkout Form Submit + SWAL CONFIRMATION
        $('#checkoutForm').submit(function(e) {
            e.preventDefault();

            var form = this;
            var pembayaranVal = $('input[name="pembayaran"]:checked').val();
            var pembayaranLabel = $('input[name="pembayaran"]:checked').next('label').text().trim();
            var rawNominal = totalNominalCleave.getRawValue();

            // Validasi manual Nominal (jika bypass required HTML5)
            if (pembayaranVal === 'kas_besar') {
                if (!rawNominal || parseFloat(rawNominal) <= 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nominal Wajib Diisi',
                        text: 'Silahkan masukkan total nominal Kas Besar terlebih dahulu!'
                    });
                    return false;
                }
            }

            // Susun Konten Modal Swal
            var htmlContent = `
                <div class="text-start">
                    <p class="mb-3"><b>Metode Pembayaran:</b> <span class="badge bg-primary fs-6">${pembayaranLabel}</span></p>
            `;

            if (pembayaranVal === 'kas_besar') {
                var formattedNominal = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(rawNominal);

                var bank = $('#nama_bank').val().toUpperCase();
                var rek = $('#nomor_rekening').val();
                var an = $('#nama_rekening').val().toUpperCase();

                htmlContent += `
                    <div class="bg-light p-3 rounded border">
                        <p class="mb-1"><b>Total Nominal:</b> <span class="fw-bold text-success fs-5">${formattedNominal}</span></p>
                        <hr class="my-2">
                        <p class="mb-1 fs-7"><b>Bank Tujuan:</b> ${bank} - ${rek}</p>
                        <p class="mb-0 fs-7"><b>Atas Nama:</b> ${an}</p>
                    </div>
                `;
            }

            htmlContent += `</div>`;

            Swal.fire({
                title: 'Konfirmasi',
                html: htmlContent,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kembalikan ke angka murni sebelum submit
                    $('#total_nominal').val(rawNominal);
                    form.submit();
                }
            });
        });

        // Hapus item keranjang
        $('.btn-delete-item').click(function() {
            var id = $(this).data('id');
            var deleteRoute = "{{ route('billing.form-maintenance.ban-luar.cart.delete', ':id') }}";

            Swal.fire({
                title: 'Hapus Item Ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteRoute.replace(':id', id),
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function() {
                            location.reload();
                        }
                    });
                }
            });
        });

        // Kosongkan keranjang
        $('#btn-clear-cart').click(function() {
            var vehicleId = "{{ $vehicle->id }}";
            var clearRoute = "{{ route('billing.form-maintenance.ban-luar.cart.clear', ':id') }}";

            Swal.fire({
                title: 'Kosongkan Keranjang?',
                text: 'Semua item keranjang akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus Semua'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: clearRoute.replace(':id', vehicleId),
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function() {
                            window.location.href = "{{ route('billing.form-maintenance.ban-luar') }}";
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
