@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">
     <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-uppercase" style="letter-spacing: 2px;">
                <i class="fas fa-truck-moving text-primary me-2"></i> Manajemen Driver
            </h2>
            <hr class="w-25 mx-auto text-muted">
        </div>
    </div>
    @include('swal')

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-cloud-lightning me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('database') }}" class="btn btn-outline-info">
                        <i class="fa fa-database me-2"></i> Database
                    </a>
                    <button class="btn btn-outline-success" onclick="addDriver()"><i class="fa fa-plus"></i> Tambah Driver</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">


        </div>
        <div class="card-body">

            <!-- Area Filter Status (Default: Aktif) -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="fa fa-filter me-1"></i> Filter Status:</label>
                    <select id="filter_status" class="form-select">
                        <option value="aktif" selected>Aktif</option>
                        <option value="non_aktif">Non-Aktif</option>
                    </select>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped w-100" id="driverTable">
                    <thead class="table-success">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>No. SIM</th>
                            <th>Berlaku SIM</th>
                            <th>No. HP</th>
                            <th>Bank / Rekening</th>
                            <th>Foto SIM</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@include('database.drivers.create')

@endsection

@push('css')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
<!-- SweetAlert2 CSS (jika belum terload di layout) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush

@push('js')
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let table;

    $(document).ready(function() {
        // Inisialisasi DataTable
        table = $('#driverTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            ajax: {
                url: "{{ route('database.driver') }}",
                data: function (d) {
                    d.status = $('#filter_status').val();
                }
            },
            scrollY: "550px",
            scrollX: true,
            scrollCollapse: true,
            scroller: true,
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'nama', name: 'nama'},
                {data: 'no_sim', name: 'no_sim'},
                {data: 'masa_berlaku_sim', name: 'masa_berlaku_sim'},
                {data: 'no_hp', name: 'no_hp'},
                {
                    data: null, orderable:false,
                    render: function(data) {
                        return data.bank + ' - ' + data.no_rek + ' (' + data.nama_rek + ')';
                    }
                },
                {data: 'foto_sim', name: 'foto_sim', orderable: false, searchable: false},
                {data: 'status', name: 'status'},
                {data: 'keterangan', name: 'keterangan', defaultContent: '-'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            // Sembunyikan kolom 'Keterangan' (index 8) secara default saat memuat pertama kali (karena filter default = aktif)
            initComplete: function() {
                toggleKeteranganColumn();
            }
        });

        // Trigger filter & visibilitas kolom saat dropdown status berubah
        $('#filter_status').change(function() {
            toggleKeteranganColumn();
            table.draw();
        });

        // Toggle Visibilitas Kolom Keterangan di DataTable
        function toggleKeteranganColumn() {
            let status = $('#filter_status').val();
            // Kolom keterangan hanya muncul ketika filter status adalah 'non_aktif'
            let isNonAktif = (status === 'non_aktif');
            table.column(8).visible(isNonAktif);
        }

        // Submit Form (Tambah / Update) via AJAX
        // Submit Form (Tambah / Update) dengan Konfirmasi SweetAlert2
        $('#driverForm').on('submit', function(e) {
            e.preventDefault();

            let form = this;
            let id = $('#driver_id').val();
            let isEdit = !!id;
            let actionText = isEdit ? 'memperbarui' : 'menyimpan';
            let confirmBtnText = isEdit ? '<i class="fa fa-save me-1"></i> Ya, Perbarui!' : '<i class="fa fa-save me-1"></i> Ya, Simpan!';

            // 1. Tampilkan Swal Konfirmasi
            Swal.fire({
                title: 'Konfirmasi Data Driver',
                text: `Apakah Anda yakin ingin ${actionText} data driver ini? Pastikan seluruh data sudah benar.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    let url = isEdit ? `/database/driver/update/${id}` : "{{ route('database.driver.store') }}";
                    let formData = new FormData(form);

                    if (isEdit) {
                        formData.append('_method', 'PATCH');
                    }

                    // 2. Tampilkan Indikator Loading saat AJAX berjalan
                    Swal.fire({
                        title: 'Memproses Data...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // 3. Kirim Data via AJAX
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            $('#driverModal').modal('hide');
                            table.draw();

                            // Swal Sukses
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || `Data driver berhasil di${isEdit ? 'perbarui' : 'simpan'}.`,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            let errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                            let errorHtml = '<ul class="text-start mb-0">';

                            if (errors) {
                                $.each(errors, function(key, value) {
                                    errorHtml += '<li>' + value[0] + '</li>';
                                });
                            } else {
                                errorHtml += '<li>Terjadi kesalahan pada server.</li>';
                            }
                            errorHtml += '</ul>';

                            // Swal Error / Validasi Gagal
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Menyimpan!',
                                html: errorHtml
                            });
                        }
                    });
                }
            });
        });
    });

    // Toggle Tampilan Input Keterangan di Modal Form
    function toggleKeteranganModal() {
        let status = $('#status').val();
        if (status === 'non_aktif') {
            $('#container_keterangan').slideDown();
            $('#keterangan').prop('required', true);
        } else {
            $('#container_keterangan').slideUp();
            $('#keterangan').prop('required', false).val('');
        }
    }

    function addDriver() {
        $('#driverForm')[0].reset();
        $('#driver_id').val('');
        $('#modalTitle').text('Tambah Driver');

        // Aktifkan required untuk foto SIM
        $('#foto_sim').prop('required', true);
        $('#foto_sim_asterisk').removeClass('d-none');
        $('#foto_sim_help').addClass('d-none');

        toggleKeteranganModal();
        $('#driverModal').modal('show');
    }

    function editDriver(id) {
        $.get(`/database/driver/${id}/edit`, function(data) {
            $('#driver_id').val(data.id);
            $('#nama').val(data.nama);
            $('#no_sim').val(data.no_sim);
            $('#masa_berlaku_sim').val(data.masa_berlaku_sim ? data.masa_berlaku_sim.split('T')[0] : '');
            $('#no_hp').val(data.no_hp);
            $('#bank').val(data.bank);
            $('#no_rek').val(data.no_rek);
            $('#nama_rek').val(data.nama_rek);
            $('#alamat').val(data.alamat);
            $('#status').val(data.status);
            $('#keterangan').val(data.keterangan);

            // Matikan required foto SIM saat edit agar file lama tidak terhapus jika tidak diupload ulang
            $('#foto_sim').prop('required', false);
            $('#foto_sim_asterisk').addClass('d-none');
            $('#foto_sim_help').removeClass('d-none');

            $('#modalTitle').text('Edit Driver');
            toggleKeteranganModal();
            $('#driverModal').modal('show');
        }).fail(function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal mengambil data driver!'
            });
        });
    }

    function deleteDriver(id) {
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            text: "Data driver ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-trash"></i> Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/database/driver/destroy/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        table.draw();
                        Swal.fire({
                            icon: 'success',
                            title: 'Terhapus!',
                            text: response.message || 'Data driver berhasil dihapus.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat menghapus data.'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
