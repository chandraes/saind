@extends('layouts.app')

@section('content')
<div class="container route-page py-4">
    <nav class="route-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('home') }}">Dashboard</a><span aria-hidden="true">/</span><a href="{{ route('database') }}">Database</a><span aria-hidden="true">/</span><span aria-current="page">Rute</span>
    </nav>
    <header class="route-page-header">
        <div>
            <h1>Manajemen rute</h1>
            <p>Kelola rute hauling, referensi nota, dan ketentuan uang jalan.</p>
        </div>
        <div class="route-page-actions">
            <a href="{{ route('database') }}" class="btn btn-outline-secondary"><i class="fa fa-database" aria-hidden="true"></i> Database</a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary"><i class="fa fa-home" aria-hidden="true"></i> Dashboard</a>
            <button type="button" class="btn btn-primary route-primary-action" data-bs-toggle="modal" data-bs-target="#modalId">
                <i class="fa fa-plus" aria-hidden="true"></i> Tambah rute
            </button>
        </div>
    </header>
    @include('swal')

    <!-- Panggil Modal di luar tabel -->
    @include('database.rute.create')
    @include('database.rute.edit')

    <section class="route-table-panel" aria-label="Daftar rute">
        <div class="route-table-toolbar">
            <div><h2>Daftar rute</h2><p>Gulir tabel untuk melihat rute lainnya.</p></div>
            <div class="route-search">
                <label class="visually-hidden" for="route-search">Cari rute</label>
                <i class="fa fa-search" aria-hidden="true"></i>
                <input id="route-search" type="search" class="form-control" placeholder="Cari rute…" autocomplete="off" aria-controls="rute-table">
            </div>
        </div>
        <div class="route-table-body">
                <table class="table table-bordered table-hover mb-0" id="rute-table" style="width: 100%">
                    <thead class="route-table-heading table-success">
                        <tr>
                            <th class="text-center align-middle" width="5%">No</th>
                            <th class="align-middle">Rute</th>
                            <th class="align-middle">Alias nota</th>
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
    </section>
    <div class="modal fade" id="note-alias-modal" tabindex="-1" aria-labelledby="note-alias-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="note-alias-modal-title">Alias rute pada nota</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="note-alias-feedback" class="alert d-none" role="status" aria-live="polite"></div>
                    <div id="note-alias-content"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
.route-page { --route-ink: #243247; --route-muted: #617085; --route-line: #e3e8ef; --route-surface: #f6f8fb; color: var(--route-ink); }
.route-breadcrumb { display: flex; gap: .65rem; align-items: center; font-size: .85rem; color: var(--route-muted); margin-bottom: 1.5rem; }
.route-breadcrumb a { color: var(--route-muted); text-decoration: none; }
.route-breadcrumb a:hover { color: var(--bs-primary); text-decoration: underline; }
.route-page-header { display: flex; justify-content: space-between; align-items: center; gap: 1.5rem; margin-bottom: 1.75rem; }
.route-page-header h1 { font-size: clamp(1.55rem, 3vw, 1.9rem); letter-spacing: -.035em; font-weight: 700; margin: 0 0 .45rem; }
.route-page-header p, .route-table-toolbar p { color: var(--route-muted); margin: 0; font-size: .9rem; }
.route-page-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
.route-page-actions > .btn { min-height: 44px; display: inline-flex; align-items: center; gap: .5rem; }
.route-primary-action { min-height: 44px; display: inline-flex; align-items: center; gap: .6rem; padding: .65rem 1rem; white-space: nowrap; border-radius: .5rem; }
.route-table-panel { border: 1px solid var(--route-line); border-radius: .8rem; background: #fff; box-shadow: 0 3px 16px rgba(36, 50, 71, .035); overflow: hidden; }
.route-table-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.4rem; border-bottom: 1px solid var(--route-line); }
.route-table-toolbar h2 { font-size: 1rem; font-weight: 700; margin: 0 0 .3rem; }
.route-search { position: relative; width: min(100%, 280px); }
.route-search > i { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); color: var(--route-muted); pointer-events: none; }
.route-search input { min-height: 44px; padding-left: 2.5rem; border-color: var(--route-line); border-radius: .5rem; }
.route-page .route-table-body .table { --bs-table-border-color: #bcc7d3; border-color: #bcc7d3; }
.route-page .route-table-scroll { max-height: min(60vh, 620px); overflow: auto; }
.route-page #rute-table thead th { position: sticky; top: 0; z-index: 2; background-color: var(--bs-table-bg, #d1e7dd); color: #0f5132; font-size: .78rem; font-weight: 700; padding: .95rem 1rem; white-space: nowrap; border-bottom: 1px solid #bcc7d3 !important; }
.route-page #rute-table thead th:is(.sorting, .sorting_asc, .sorting_desc) { padding-right: 28px; }
.route-page #rute-table tbody td { padding: 12px 16px; vertical-align: middle; border-color: #bcc7d3; white-space: nowrap; font-size: .9rem; }
.route-page #rute-table tbody td:nth-child(2) { max-width: 260px; overflow: hidden; text-overflow: ellipsis; font-weight: 600; }
.route-page #rute-table tbody td:nth-child(5), .route-page #rute-table tbody td:nth-child(6) { font-variant-numeric: tabular-nums; color: var(--route-ink) !important; font-weight: 600 !important; }
.route-page .route-table-footer { padding: .9rem 1.4rem; border-top: 1px solid var(--route-line); color: var(--route-muted); font-size: .82rem; }
.route-page .dataTables_info { padding: 0; float: none; }
.route-page .dataTables_processing { background: #fff; border: 1px solid var(--route-line); border-radius: .5rem; box-shadow: 0 2px 12px rgba(36, 50, 71, .08); }
.note-alias-cell { width: 260px; display: flex; flex-direction: column; align-items: flex-start; gap: 10px; padding: .25rem 0; }
.note-alias-preview { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; max-width: 260px; }
.note-alias-label { display: inline-block; max-width: 260px; white-space: normal; overflow-wrap: anywhere; line-height: 1.4; padding: .3rem .5rem; border: 1px solid var(--route-line); border-radius: .35rem; background: var(--route-surface); font-size: .8rem; color: #46566b; }
.note-alias-trigger { min-height: 30px; min-width: 0; font-size: .75rem; line-height: 1.3; padding: .3rem .5rem; border-radius: .35rem; }
.route-row-actions .btn, #note-alias-modal .btn-sm { min-height: 44px; min-width: 44px; }
.route-row-actions { display: flex; align-items: center; justify-content: center; gap: .5rem; }
.note-alias-context { padding: 1rem; border-left: 3px solid var(--bs-primary); background: var(--bs-light); border-radius: .35rem; }
.note-alias-value { overflow-wrap: anywhere; min-width: 0; }
@media (pointer: coarse) { .note-alias-trigger { min-height: 44px; padding: .5rem .65rem; } }
@media (max-width: 767px) { .route-page-header, .route-table-toolbar { align-items: stretch; flex-direction: column; } .route-primary-action { align-self: flex-start; } .route-search { width: 100%; } .route-table-toolbar { padding: 1rem; } }
</style>
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
<!-- SweetAlert2 JS -->
<script src="{{ asset('assets/js/rute-note-alias.js') }}?v={{ filemtime(public_path('assets/js/rute-note-alias.js')) }}"></script>
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
            serverSide: false,
            paging: false,
            deferRender: true,
            dom: 'r<"route-table-scroll"t><"route-table-footer"i>',
            language: {info: '_TOTAL_ rute', infoFiltered: '(dari _MAX_ rute)', infoEmpty: 'Belum ada rute', emptyTable: 'Belum ada rute terdaftar.', zeroRecords: 'Tidak ada rute yang sesuai pencarian.', processing: 'Memuat rute…'},
            ajax: {url: "{{ route('rute.index') }}", data: {length: -1}},
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'nama', name: 'nama'},
                {data: 'note_aliases', name: 'note_aliases', orderable: false, searchable: false},
                {data: 'jarak', name: 'jarak', className: 'text-center'},
               // KEMBALIKAN 'data' menjadi 'uang_jalan' dan 'uj_ditahan'
                {data: 'uang_jalan', name: 'uang_jalan', className: 'text-end text-success fw-bold'},
                {data: 'uj_ditahan', name: 'uj_ditahan', className: 'text-end text-danger fw-bold'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
            ],

        });

        if (document.fonts?.ready) {
            document.fonts.ready.then(() => table.columns.adjust());
        }
        let routeSearchTimer;
        document.getElementById('route-search').addEventListener('input', function () {
            clearTimeout(routeSearchTimer);
            const value = this.value;
            routeSearchTimer = setTimeout(() => table.search(value).draw(), 250);
        });
        $('#rute-table').on('click', '.route-edit', function () {
            editRute(this.dataset.id, this.dataset.name, this.dataset.distance, this.dataset.amount, this.dataset.held);
        });
        window.setupRouteNoteAliases(table);

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
