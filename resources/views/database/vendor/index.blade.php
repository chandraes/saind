@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Vendor</u></h1>
        </div>
    </div>
   @include('swal')
   {{-- if has any error --}}

    <div class="row float-end">
        <div class="col-md-12">
            <strong>
                <span id="clock"></span>
            </strong>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="{{route('vendor.create')}}"><img
                                src="{{asset('images/vendor.svg')}}" alt="add-document" width="30"> Tambah Vendor</a>
                    </td>
                    <td><a href="{{route('uj.vendor.preview-vendor')}}" target="_blank"><img
                        src="{{asset('images/document.svg')}}" alt="add-document" width="30"> Print Vendor</a>
                    </td>
                    {{-- <td><a href="{{route('dokumen.sph_doc')}}" target="_blank"><img
                                src="{{asset('images/document-add.svg')}}" alt="add-document" width="30"> Tambah SPH</a>
                    </td> --}}
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid table-responsive">
    <table class="table table-bordered table-hover w-100" id="data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Nama CP</th>
                <th class="text-center align-middle">Nama Perusahaan</th>
                <th class="text-center align-middle">Nickname</th>
                <th class="text-center align-middle">Pembayaran</th>
                <th class="text-center align-middle">SO</th>
                <th class="text-center align-middle">PPN</th>
                <th class="text-center align-middle">Pph</th>
                <th class="text-center align-middle">Plafon Cash</th>
                <th class="text-center align-middle">Plafon Storing</th>
                <th class="text-center align-middle">Uang Jalan</th>
                <th class="text-center align-middle">Limit Tonase Muat</th>
                <th class="text-center align-middle">Status</th>
                <th class="text-center align-middle">Sponsor</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script>
    // success-alert close after 5 second
    $("#success-alert").fadeTo(5000, 500).slideUp(500, function(){
        $("#success-alert").slideUp(500);
    });

    $(document).ready(function() {
        $('#data').DataTable({
            processing: true,
            serverSide: true,
            saveState: true,
            ajax: "{{ route('vendor.index') }}", // Sesuaikan dengan nama route Anda
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'align-middle text-center' },
                { data: 'nama', name: 'nama', className: 'align-middle' },
                { data: 'perusahaan', name: 'perusahaan', className: 'align-middle' },
                { data: 'nickname', name: 'nickname', className: 'align-middle' },
                { data: 'pembayaran', name: 'pembayaran', className: 'align-middle text-center' },
                { data: 'support_operational', name: 'support_operational', className: 'align-middle text-center' },
                { data: 'ppn', name: 'ppn', className: 'align-middle text-center' },
                { data: 'pph', name: 'pph', className: 'align-middle text-center' },
                { data: 'plafon_titipan', name: 'plafon_titipan', className: 'align-middle text-center' },
                { data: 'plafon_lain', name: 'plafon_lain', className: 'align-middle text-center' },
                { data: 'uang_jalan', name: 'uang_jalan', orderable: false, searchable: false, className: 'align-middle text-center' },
                { data: 'limit_tonase', name: 'limit_tonase', orderable: false, searchable: false, className: 'align-middle text-center' },
                { data: 'status', name: 'status', className: 'align-middle text-center' },
                { data: 'sponsor_nama', name: 'sponsor_id', className: 'align-middle text-center' }, // relasi sponsor
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'align-middle text-center' }
            ]
        });

       $('#data').on('click', '.toggle-limit-tonase', function(e) {
            e.preventDefault();

            let checkbox = $(this);
            let url = checkbox.data('url');

            Swal.fire({
                title: "Konfirmasi",
                text: "Anda yakin ingin mengubah status Limit Tonase Muat untuk vendor ini?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#0d6efd",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Ya, Ubah!",
                cancelButtonText: "Batal",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'PATCH'
                        },
                        success: function(response) {
                            if(response.success) {
                                // REFRESH DATATABLE DI SINI
                                // Parameter 'null, false' memastikan pengguna tidak dilempar kembali ke halaman 1
                                $('#data').DataTable().ajax.reload(null, false);

                                Swal.fire({
                                    title: "Berhasil!",
                                    text: response.message,
                                    icon: "success",
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function() {
                            Swal.fire(
                                "Oops!",
                                "Terjadi kesalahan pada server saat memperbarui data.",
                                "error"
                            );
                        }
                    });
                }
            });
        });
    } );
</script>
@endpush
