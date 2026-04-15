@extends('layouts.app')

@section('content')
<div class="container pb-5">
    <div class="row justify-content-center mb-4">
        <div class="col-md-12 text-center">
            <h1 class="fw-bold"><u>Histori Pesan WA</u></h1>
        </div>
    </div>

    @include('swal')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{route('home')}}" class="text-decoration-none text-dark d-inline-block text-center me-4">
                <img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30">
                <div class="small fw-bold mt-1">Dashboard</div>
            </a>
        </div>
        <div>
            <form action="{{route('pengaturan.histori.delete-sended')}}" method="post" id="hapusForm">
                @csrf
                @method('delete')
                <button type="submit" class="btn btn-outline-danger rounded-pill px-4 fw-bold shadow-sm">
                    <i class="fa fa-trash me-2"></i> Bersihkan Pesan Terkirim
                </button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle" id="data" style="width: 100%">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center py-3" style="width: 5%">No</th>
                            <th class="py-3" style="width: 45%">Cuplikan Pesan</th>
                            <th class="text-center py-3" style="width: 20%">Tujuan (Group ID)</th>
                            <th class="text-center py-3" style="width: 15%">Status</th>
                            <th class="text-center py-3" style="width: 15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data diisi oleh Yajra DataTables (AJAX) --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL PESAN --}}
<div class="modal fade" id="modalDetailPesan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa fa-comment-alt me-2"></i> Isi Pesan Utuh</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                {{-- Menggunakan pre-wrap agar enter/newline dari pesan WA tetap terbaca rapi --}}
                <div class="p-3 bg-white border rounded text-dark" style="white-space: pre-wrap; font-family: monospace; font-size: 0.95rem;" id="isiPesanLengkap"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush

@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Yajra DataTables Server-Side
        let table = $('#data').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('pengaturan.histori-pesan') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'pesan', name: 'pesan' },
                { data: 'group_id', name: 'group_was.group_id', className: 'text-center' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ],
            language: {
                processing: "Memuat data...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ data keseluruhan)",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Kembali"
                }
            }
        });

        // SweetAlert untuk Hapus Semua Pesan Terkirim
        $('#hapusForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Bersihkan Pesan?',
                text: 'Semua histori pesan yang "Terkirim" akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545', // Merah danger
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Bersihkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        // SweetAlert untuk Kirim Ulang Pesan (Event Delegation karena Datatables)
        $('#data').on('click', '.btn-resend', function(e) {
            e.preventDefault();
            let form = $(this).closest('form');

            Swal.fire({
                title: 'Kirim Ulang Pesan?',
                text: 'Pesan akan dikirim ulang ke grup tujuan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    form.submit();
                }
            });
        });

        $('#data').on('click', '.btn-detail', function() {
            // Ambil isi pesan dari atribut data-pesan
            let pesan = $(this).data('pesan');

            // Masukkan ke dalam modal dan tampilkan
            $('#isiPesanLengkap').text(pesan);
            $('#modalDetailPesan').modal('show');
        });

        // Notifikasi otomatis tertutup
        $("#success-alert").fadeTo(5000, 500).slideUp(500);
    });

   
</script>
@endpush
