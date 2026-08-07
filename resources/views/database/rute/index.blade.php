@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center mb-4">
        <div class="col-md-12 text-center">
            <h2 class="text-uppercase tracking-wider fw-bold">Manajemen Rute</h2>
        </div>
    </div>

  @include('swal')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="btn-group shadow-sm">
            <a href="{{route('home')}}" class="btn btn-outline-secondary"><i class="fa fa-tachometer-alt me-1"></i> Dashboard</a>
            <a href="{{route('database')}}" class="btn btn-outline-secondary"><i class="fa fa-database me-1"></i> Database</a>
        </div>
        <div>
            <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalId">
                <i class="fa fa-plus me-1"></i> Tambah Rute
            </button>
        </div>
    </div>

    <!-- Panggil Modal di luar tabel -->
    @include('database.rute.create')
    @include('database.rute.edit')

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100 mb-0" id="rute-table">
                    <thead class="table-success bg-gradient">
                        <tr>
                            <th class="text-center align-middle" width="5%">No</th>
                            <th class="align-middle">Rute</th>
                            <th class="text-center align-middle">Jarak (Km)</th>
                            <th class="text-end align-middle">Uang Jalan</th>
                            <th class="text-end align-middle">UJ Ditahan</th>
                            <th class="text-center align-middle" width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Yajra DataTables akan mengisi ini -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('js')
<script src="{{asset('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
<!-- Jika menggunakan SweetAlert2 di project Anda, sesuaikan script di bawah -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script> <!-- Pastikan ini file DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<!-- SweetAlert2 JS -->
<script>
   // 1. Simpan instance Cleave Create ke dalam variabel agar bisa diakses untuk fungsi reset
    window.cleaveCreateUangJalan = new Cleave('#uang_jalan', { numeral: true, numeralThousandsGroupStyle: 'thousand', numeralDecimalMark: ',', delimiter: '.' });
    window.cleaveCreateUjDitahan = new Cleave('#uj_ditahan', { numeral: true, numeralThousandsGroupStyle: 'thousand', numeralDecimalMark: ',', delimiter: '.' });

    // 2. Fungsi untuk mengecek dan mereset nilai Uang Jalan Ditahan
    function checkBatasUjDitahan(tipe) {
        let inputUj = tipe === 'create' ? '#uang_jalan' : '#edit_uang_jalan';
        let inputUjDitahan = tipe === 'create' ? '#uj_ditahan' : '#edit_uj_ditahan';

        // Ambil instance Cleave yang sesuai untuk di-reset nilainya
        let cleaveUjDitahan = tipe === 'create' ? window.cleaveCreateUjDitahan : window.cleaveEditUjDitahan;

        // Ambil nilai, hapus titik, lalu ubah ke integer (jika kosong, jadikan 0)
        let uj = parseInt($(inputUj).val().replace(/\./g, '')) || 0;
        let ujDitahan = parseInt($(inputUjDitahan).val().replace(/\./g, '')) || 0;

        // Kondisi jika nilai ditahan melebihi uang jalan
        if (ujDitahan > uj) {
            // Tampilkan SweetAlert Peringatan
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan!',
                text: 'Uang Jalan Ditahan tidak boleh melebihi Uang Jalan!',
                confirmButtonColor: '#f39c12',
            });

            // Kembalikan nilai UJ Ditahan menjadi 0 dengan format Cleave
            if (cleaveUjDitahan) {
                cleaveUjDitahan.setRawValue('0');
            } else {
                $(inputUjDitahan).val('0'); // Fallback jika instance tidak ditemukan
            }
        }
    }

    // Fungsi membuka modal edit (Dipanggil dari tombol di Yajra DataTable)
    function editRute(id, nama, jarak, uang_jalan, uj_ditahan) {
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_jarak').value = jarak;

        // Reset dan set Cleave Edit Uang Jalan
        document.getElementById('edit_uang_jalan').value = uang_jalan;
        if(window.cleaveEditUangJalan) window.cleaveEditUangJalan.destroy();
        window.cleaveEditUangJalan = new Cleave('#edit_uang_jalan', { numeral: true, numeralThousandsGroupStyle: 'thousand', numeralDecimalMark: ',', delimiter: '.' });

        // Reset dan set Cleave Edit UJ Ditahan
        document.getElementById('edit_uj_ditahan').value = uj_ditahan;
        if(window.cleaveEditUjDitahan) window.cleaveEditUjDitahan.destroy();
        window.cleaveEditUjDitahan = new Cleave('#edit_uj_ditahan', { numeral: true, numeralThousandsGroupStyle: 'thousand', numeralDecimalMark: ',', delimiter: '.' });

        // Ubah Action Form
        document.getElementById('editForm').action = '/rute/' + id;

        $('#modalEdit').modal('show');
    }

    $(document).ready(function() {

        $('#uang_jalan, #uj_ditahan').on('keyup change', function() {
            checkBatasUjDitahan('create');
        });

        // Event listener saat pengguna mengetik/merubah nilai pada form Edit
        $('#edit_uang_jalan, #edit_uj_ditahan').on('keyup change', function() {
            checkBatasUjDitahan('edit');
        });
        // Setup Yajra DataTables
        var table = $('#rute-table').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            scrollY: "550px",
            scrollX: true,
            scrollCollapse: true,
            scroller: true,
            ajax: "{{ route('rute.index') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'nama', name: 'nama'},
                {data: 'jarak', name: 'jarak', className: 'text-center'},
               // KEMBALIKAN 'data' menjadi 'uang_jalan' dan 'uj_ditahan'
                {data: 'uang_jalan', name: 'uang_jalan', className: 'text-end text-success fw-bold'},
                {data: 'uj_ditahan', name: 'uj_ditahan', className: 'text-end text-danger fw-bold'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
            ],

        });

        // --------------------------------------------------------
        // KONFIRMASI SUBMIT FORM TAMBAH (CREATE)
        // --------------------------------------------------------
        $('#createForm').on('submit', function(e) {
            e.preventDefault(); // Hentikan submit otomatis
            let form = this;

            Swal.fire({
                title: 'Simpan Data Rute?',
                text: "Pastikan jarak dan nominal uang jalan sudah benar!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd', // Warna biru primer
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-save"></i> Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Lanjutkan submit jika konfirmasi 'Ya'
                }
            });
        });

        // --------------------------------------------------------
        // KONFIRMASI SUBMIT FORM UBAH (EDIT)
        // --------------------------------------------------------
        $('#editForm').on('submit', function(e) {
            e.preventDefault(); // Hentikan submit otomatis
            let form = this;

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data rute sebelumnya akan ditimpa dengan data baru!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107', // Warna kuning warning
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-edit"></i> Ya, Ubah!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Lanjutkan submit jika konfirmasi 'Ya'
                }
            });
        });

        // SweetAlert untuk Konfirmasi Hapus
        $('#rute-table').on('submit', '.deleteForm', function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Yakin hapus data rute?',
                text: "Data yang terhapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });
    });
</script>
@endpush
