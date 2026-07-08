@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-uppercase" style="letter-spacing: 2px;">
                <i class="fas fa-truck-moving text-primary me-2"></i> Manajemen Vehicle
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
                        <i class="fa fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('database') }}" class="btn btn-outline-info">
                        <i class="fa fa-database me-2"></i> Database
                    </a>
                    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahVehicle">
                        <i class="fa fa-plus-circle me-2"></i> Tambah Vehicle
                    </button>
                    <a href="{{ route('print-preview-vehicle') }}" target="_blank" class="btn btn-danger shadow-sm">
                        <i class="fa fa-file-pdf me-2"></i> Print Vehicle
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('database.vehicle.create')

    <div id="modalContainer"></div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold"><i class="fa fa-list text-success me-2"></i> Daftar Kendaraan</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0" id="data-table" >
                            <thead class="table-success text-center">
                                <tr>
                                    <th>NO</th>
                                    <th>NOLAM</th>
                                    <th>VENDOR</th>
                                    <th>PERUSAHAAN</th>
                                    <th>NOPOL</th>
                                    <th>NAMA STNK</th>
                                    <th>NO RANGKA</th>
                                    <th>NO MESIN</th>
                                    <th>TIPE</th>
                                    <th>INDEX</th>
                                    <th>TAHUN</th>
                                    <th>GPS</th>
                                    <th>STATUS</th>
                                    <th>AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.bootstrap5.min.css" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">

<style>
    /* Kostumisasi UI Tabel */
    #data-table thead th {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    #data-table tbody td {
        font-size: 0.9rem;
        white-space: nowrap; /* Mencegah teks turun ke bawah dan merusak scroller */
    }
    .dataTables_wrapper .dataTables_scrollBody {
        border-bottom: none !important;
    }
    /* Mempercantik wrapper DataTables */
    .dataTables_wrapper {
        padding: 1.5rem;
    }
</style>
@endpush

@push('js')
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<script>
    setTimeout(function() {
        $('#alert').fadeOut('slow');
    }, 5000);

    $(document).ready(function() {
        $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true, // Wajib aktif untuk Scroller
            ajax: "{{ route('vehicle.index') }}",
            // Konfigurasi Scroller
            "scrollY": "550px",
            "scrollX": true,
            scrollCollapse: true,
            scroller: true, // Plugin scroller diaktifkan
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center align-middle', orderable: false, searchable: false },
                { data: 'nomor_lambung', name: 'nomor_lambung', className: 'text-center align-middle fw-bold' },
                { data: 'vendor_nama', name: 'vendor.nama', className: 'text-center align-middle text-wrap' },
                { data: 'vendor_perusahaan', name: 'vendor.perusahaan', className: 'text-center align-middle text-wrap' },
                { data: 'nopol', name: 'nopol', className: 'text-center align-middle' },
                { data: 'nama_stnk', name: 'nama_stnk', className: 'text-center align-middle text-wrap' },
                { data: 'no_rangka', name: 'no_rangka', className: 'text-center align-middle' },
                { data: 'no_mesin', name: 'no_mesin', className: 'text-center align-middle' },
                { data: 'tipe', name: 'tipe', className: 'text-center align-middle' },
                { data: 'no_index', name: 'no_index', className: 'text-center align-middle fw-bold' },
                { data: 'tahun', name: 'tahun', className: 'text-center align-middle fw-bold' },
                { data: 'gps', name: 'gps', className: 'text-center align-middle' },
                { data: 'status', name: 'status', className: 'text-center align-middle' },
                { data: 'action', name: 'action', className: 'text-center align-middle', orderable: false, searchable: false }
            ]
        });
    });

    // --------------------------------------------------------
        // AJAX UNTUK MODAL SHOW (Melihat Detail Kendaraan)
        // --------------------------------------------------------
        $(document).on('click', '.btn-show-vehicle', function(e) {
            e.preventDefault();
            var vehicleId = $(this).data('id');
            // Generate URL menggunakan helper route Laravel
            var url = "{{ route('vehicle.show', ':id') }}".replace(':id', vehicleId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Masukkan HTML response ke dalam modalContainer
                    $('#modalContainer').html(response);
                    // Tampilkan modal
                    $('#modalShow' + vehicleId).modal('show');
                },
                error: function() {
                    alert('Gagal mengambil data kendaraan.');
                }
            });
        });

        // --------------------------------------------------------
        // AJAX UNTUK MODAL EDIT
        // --------------------------------------------------------
        $(document).on('click', '.btn-edit-vehicle', function(e) {
            e.preventDefault();
            var vehicleId = $(this).data('id');
            var url = "{{ route('vehicle.edit', ':id') }}".replace(':id', vehicleId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Masukkan HTML response ke dalam modalContainer
                    $('#modalContainer').html(response);
                    // Tampilkan modal Edit
                    $('#modalEdit' + vehicleId).modal('show');
                },
                error: function() {
                    alert('Gagal mengambil form edit kendaraan.');
                }
            });
        });

        // --------------------------------------------------------
        // AJAX UNTUK MODAL EDIT REKENING KHUSUS
        // --------------------------------------------------------
        $(document).on('click', '.btn-edit-rekening', function(e) {
            e.preventDefault();
            var vehicleId = $(this).data('id');
            // Pastikan rute ini sesuai dengan yang kita buat di web.php
            var url = "{{ route('vehicle.edit-rekening', ':id') }}".replace(':id', vehicleId);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Masukkan HTML response ke dalam modalContainer
                    $('#modalContainer').html(response);
                    // Tampilkan modal Rekening
                    $('#modalEditRekening' + vehicleId).modal('show');
                },
                error: function() {
                    alert('Gagal mengambil form edit rekening.');
                }
            });
        });

    function toggleInputTambah() {
        var value = document.getElementById('vendor_id').value;
        if (value == '') {
            document.getElementById('row-tambah').style.display = 'none';
        } else {
            document.getElementById('row-tambah').style.display = 'flex';
        }
    }
</script>
@endpush
