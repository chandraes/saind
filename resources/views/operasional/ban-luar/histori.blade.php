@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>BAN LUAR </u></h1>
            <h1>{{$posisi->nama}}</h1>
            {{-- <h1>{{$nama_bulan}} {{$tahun}}</h1> --}}
        </div>
    </div>
    @include('swal')
    <!-- Modal Body -->
    <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
    <div class="modal fade" id="passwordModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        Masukan Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="passwordForm">
                    @csrf
                    <div class="modal-body">

                        <input class="form-control" type="password" id="password" name="password" placeholder="Password"
                            required>
                        <input type="hidden" id="itemId" name="itemId">

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>
                        <button type="submit" class="btn btn-primary">Lanjutkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitleId">
                        Ubah Tanggal Ganti Ban
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" id="updateForm">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="created_at" class="form-label">Tanggal Ganti Ban</label>
                                    <input type="text" class="form-control" name="created_at" id="created_at" required readonly/>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="created_at" class="form-label">Password</label>
                                    <input class="form-control" type="password" id="password" name="password" placeholder="Password"
                                    required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Tutup
                        </button>
                        <button type="submit" class="btn btn-primary">Lanjutkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.index')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> REKAP</a></td>
                    <td><a href="{{route('statisik.index')}}"><img src="{{asset('images/statistik.svg')}}" alt="dokumen"
                                width="30"> STATISTIK</a></td>
                    <td>
                        <form action="{{route('statistik.ban-luar')}}" method="get">
                            <input type="hidden" name="vehicle_id" value="{{$vehicle->id}}">

                            <button class="btn" type="submit"> <img src="{{asset('images/back.svg')}}" alt="dokumen"
                                    width="30"> KEMBALI</button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>

    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">MEREK</th>
                    <th class="text-center align-middle">NO. SERI BAN</th>
                    <th class="text-center align-middle">KONDISI BAN</th>
                    <th class="text-center align-middle">TGL GANTI BAN</th>
                    <th class="text-center align-middle">ACTION</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script>
    $(document).ready(function(){
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            $('#itemId').val(id);
            // $('#passwordModal').modal('open');
            // set action to password form
            $('#passwordForm').attr('action', "{{route('statistik.ban-luar.histori-destroy', ['histori' => ':id'])}}".replace(':id', id));
            console.log("{{route('statistik.ban-luar.histori-destroy', ['histori' => ':id'])}}".replace(':id', id));
        });

        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            var createdAt = $(this).data('created-at');
            $('#updateForm').attr('action', "{{route('statistik.ban-luar.histori-update', ['histori' => ':id'])}}".replace(':id', id));
            $('#created_at').val(createdAt);
            var tanggalInput = document.getElementById("created_at");

            tanggalInput.flatpickr({
                enableTime: false,
                dateFormat: "d-m-Y",
            });
            console.log("Edit button clicked for ID: " + id + " with created_at: " + createdAt);
        });

        $('#rekapTable').DataTable({
            'processing': true,
            'serverSide': true,
            "searching": true,
            "ordering": false,
            'ajax': {
                'url': "{{route('statistik.ban-luar.histori-data')}}",
                'data': {
                    'vehicle': '{{$vehicle->id}}',
                    'posisi': '{{$posisi->id}}' // Added missing closing quote
                },
                'type': 'GET',
            },
            'columns':[
                {data: 'merk', name: 'merk', class:"text-center align-middle"},
                {data: 'no_seri', name: 'no_seri', class:"text-center align-middle"},
                {
                    data: 'kondisi',
                    name: 'kondisi',
                    class:"text-center align-middle",
                    "render": function (data, type, row, meta) {
                        return data + '%';
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    class:"text-center align-middle",
                    "render": function (data, type, row, meta) {
                        return moment(data).format('DD-MM-YYYY');
                    }
                },
                {
                data: null,
                name: 'ACT',
                class:"text-center align-middle",
                "render": function (data, type, row, meta) {
                    return '<button class="btn btn-primary edit-btn mx-2" data-id="' + row.id + '" data-created-at="' + moment(row.created_at).format('DD-MM-YYYY') + '" data-bs-toggle="modal" data-bs-target="#editModal">Edit</button>' +
                           '<button class="btn btn-danger delete-btn" data-id="' + row.id + '" data-bs-toggle="modal" data-bs-target="#passwordModal">Delete</button>';
                }
            },
            ]
        });

    });
</script>
@endpush
