@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Form Penggantian Ban Luar</h3>
            <p class="text-muted mb-0">Pilih unit kendaraan dan catat pemasangan atau rotasi ban.</p>
        </div>
        <div>
            <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i> Kembali ke Billing
            </a>
        </div>
    </div>

    @include('swal')

    <div class="row">
        <!-- FORM UTAMA -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fa fa-wrench me-2"></i> Input Data Ban Baru
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('billing.form-maintenance.ban-luar.store') }}" method="post" id="createForm">
                        @csrf

                        <!-- Pilihan Kendaraan -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Pilih Kendaraan (Unit) <span class="text-danger">*</span></label>
                            <select name="vehicle_id" id="vehicle_id" class="form-select select2" required>
                                <option value="" disabled selected>-- Cari & Pilih Unit Kendaraan --</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->nomor_lambung }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Blok Form Penggantian (Disembunyikan sampai Unit dipilih) -->
                        <div id="form-ganti-section" style="display: none;">
                            <hr class="mb-4">

                            <!-- Opsi Sumber Ban -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Sumber Ban <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sumber_ban" id="sumber_baru" value="baru" checked>
                                        <label class="form-check-label fw-bold" for="sumber_baru">Ban Baru</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="sumber_ban" id="sumber_serep" value="serep">
                                        <label class="form-check-label text-warning fw-bold" for="sumber_serep">Dari Ban Serep</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Posisi Ban yang Diganti <span class="text-danger">*</span></label>
                                <select name="posisi_ban_id" id="posisi_ban_id" class="form-select select2" required>
                                    <option value="" disabled selected>-- Pilih Posisi --</option>
                                    @foreach($posisiBans as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Group Input Ban Baru (Bisa Disembunyikan) -->
                            <div id="input-ban-baru">
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
                            </div>

                            <!-- Alert Informasi Rotasi (Ditampilkan jika pilih Serep) -->
                            <div id="alert-serep" class="alert alert-warning mb-4" style="display: none;">
                                <i class="fa fa-info-circle me-1"></i> Data Merek, Seri, dan Kondisi akan ditarik dari data Ban Serep (Posisi 11). Ban yang ada di posisi tujuan akan dialihkan menjadi Serep.
                            </div>

                            <button type="submit" id="btn-submit" class="btn btn-primary w-100 py-2 fw-bold">
                                <i class="fa fa-save me-1"></i> Simpan Pemasangan Ban
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- INFO PREVIEW KENDARAAN & STATUS BAN -->
        <div class="col-lg-8 mb-4">
            <!-- Tampilan Default (Sebelum Pilih Unit) -->
            <div id="empty-state" class="card border-0 shadow-sm h-100 bg-light d-flex justify-content-center align-items-center p-5 text-center" style="min-height: 400px;">
                <i class="fa fa-truck fa-4x text-muted mb-3 opacity-50"></i>
                <h5 class="text-muted">Pilih unit kendaraan terlebih dahulu</h5>
                <p class="text-muted small">Informasi vendor, driver, dan status seluruh ban akan muncul di sini.</p>
            </div>

            <!-- Tampilan Loading (Saat Proses AJAX) -->
            <div id="loading-state" class="card border-0 shadow-sm h-100 bg-light justify-content-center align-items-center p-5 text-center" style="display: none; min-height: 400px;">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h6 class="mt-3 text-muted fw-bold">Mengambil Data Kendaraan...</h6>
            </div>

            <!-- Tampilan Data (Setelah Pilih Unit & Berhasil AJAX) -->
            <div id="data-state" style="display: none;">
                <!-- Card Info Unit -->
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

                <!-- Tabel Status Ban -->
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
                                <tbody id="tbody-tires">
                                    <!-- Baris tabel di-generate via jQuery -->
                                </tbody>
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
    /* Styling tambahan agar border card Info Unit responsif */
    @media (min-width: 768px) {
        .border-end-md {
            border-right: 1px solid #dee2e6 !important;
        }
    }
    .fs-7 { font-size: 0.85rem; }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function(){
        // Inisialisasi Select2
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Trigger ketika Kendaraan Dipilih
        $('#vehicle_id').on('change', function() {
            var vehicleId = $(this).val();

            if (vehicleId) {
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

                        var tbody = '';
                        $.each(response.tires, function(index, tire) {
                            tbody += '<tr>';
                            tbody += '  <td class="fw-semibold text-primary">' + tire.posisi + '</td>';
                            tbody += '  <td class="text-center text-uppercase">' + tire.merk + '</td>';
                            tbody += '  <td class="text-center text-uppercase">' + tire.no_seri + '</td>';
                            tbody += '  <td class="text-center">' + tire.kondisi + '</td>';
                            tbody += '  <td class="text-center font-monospace fw-bold">' + tire.ritase + '</td>';
                            tbody += '</tr>';
                        });
                        $('#tbody-tires').html(tbody);

                        setTimeout(function() {
                            $('#loading-state').removeClass('d-flex').hide();
                            $('#data-state').fadeIn();
                            $('#form-ganti-section').slideDown();
                        }, 300);
                    },
                    error: function() {
                        $('#loading-state').removeClass('d-flex').hide();
                        $('#empty-state').addClass('d-flex').fadeIn();
                        Swal.fire('Error', 'Gagal mengambil data kendaraan.', 'error');
                    }
                });
            } else {
                $('#data-state').hide();
                $('#form-ganti-section').slideUp();
                $('#empty-state').addClass('d-flex').fadeIn();
            }
        });

        // Handle perubahan radio button sumber ban
        $('input[name="sumber_ban"]').change(function() {
            var val = $(this).val();
            if (val === 'serep') {
                $('#input-ban-baru').slideUp();
                $('#alert-serep').slideDown();
                // Hapus required agar form bisa di-submit meski kosong
                $('#merk, #no_seri, #kondisi').removeAttr('required');

                // Mencegah ban serep dipilih sebagai posisi tujuan rotasi serep
                if ($('#posisi_ban_id').val() == '11') {
                    $('#posisi_ban_id').val('').trigger('change');
                    Swal.fire('Perhatian', 'Tidak bisa merotasi ban serep ke posisi ban serep.', 'warning');
                }
            } else {
                $('#input-ban-baru').slideDown();
                $('#alert-serep').slideUp();
                // Kembalikan required
                $('#merk, #no_seri, #kondisi').attr('required', true);
            }
        });

        // Trigger validasi jika memilih posisi 11 saat mode Serep aktif
        $('#posisi_ban_id').change(function() {
            if ($(this).val() == '11' && $('input[name="sumber_ban"]:checked').val() === 'serep') {
                $(this).val('').trigger('change');
                Swal.fire('Perhatian', 'Posisi ini adalah Ban Serep. Pilih posisi ban yang beroperasi (misal: Kanan Depan).', 'warning');
            }
        });

        // Validasi & Konfirmasi Form
        $('#createForm').submit(function(e){
            e.preventDefault();

            var isSerep = $('input[name="sumber_ban"]:checked').val() === 'serep';
            var msg = isSerep ? "Pastikan Anda benar ingin melakukan rotasi ban serep ke posisi tersebut!" : "Pastikan Merek dan No. Seri Ban Baru yang diinput sudah benar!";

            Swal.fire({
                title: 'Konfirmasi Pemasangan',
                text: msg,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Periksa Lagi'
            }).then((result) => {
                if (result.isConfirmed) {
                    let btn = $('#btn-submit');
                    btn.html('<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...').attr('disabled', true);
                    this.submit();
                }
            });
        });

        $('input[name="sumber_ban"]:checked').trigger('change');

        // 2. Sinkronisasi load data AJAX jika browser mengingat pilihan Kendaraan sebelumnya
        if ($('#vehicle_id').val()) {
            $('#vehicle_id').trigger('change');
        }
    });
</script>
@endpush
