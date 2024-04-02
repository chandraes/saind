@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Aktivasi Maintenance Vehicle</u></h1>
        </div>
    </div>
    @include('swal')
    @include('database.aktivasi-maintenance.create')
    @include('database.aktivasi-maintenance.edit')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#createModal"">
                            <img src=" {{asset('images/aktivasi-maintenance.svg')}}" alt="dokumen" width="30"> Aktivasi Maintenance Vehicle</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container mt-5 table-responsive ">
    <table class="table table-bordered" id="dataTable">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Nomor Lambung</th>
                <th class="text-center align-middle">Tanggal Mulai</th>
                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle" style="width: 10px">{{$loop->iteration}}</td>
                <td class="text-center align-middle">
                    {{$d->vehicle->nomor_lambung}}
                </td>
                <td class="text-center align-middle">
                    {{$d->id_tanggal_mulai}}
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <a href="#" class="btn btn-warning mx-2" data-bs-toggle="modal" data-bs-target="#editModal" title="Edit Data" onclick="editFun({{$d}}, {{$d->id}})"><i class="fa fa-edit"></i></a>
                    <form action="{{route('database.aktivasi-maintenance.destroy', $d->id)}}" method="post" class="delete-form" data-id="{{$d->id}}" id="deleteForm{{$d->id}}">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger mx-2" title="Hapus Data"><i class="fa fa-trash"></i></button>
                    </form>
                    </div>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script>
   function editFun(data, id) {
            $('#edit_vehicle_id').val(data.vehicle_id).trigger('change');
            document.getElementById('edit_tanggal_mulai').value = data.id_tanggal_mulai;
            // Populate other fields...
            document.getElementById('editForm').action = '/database/aktivasi-maintenance/update/' + id;
        }

    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

        $('#vehicle_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih NOLAM',
            dropdownParent: $('#createModal')
        });


        $('#edit_vehicle_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih NOLAM',
            dropdownParent: $('#editModal')
        });

        flatpickr(".calendar", {
            dateFormat: "d-m-Y",
        });

    } );

    $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Data yang anda masukan sudah benar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        $('#editForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah Data yang anda masukan sudah benar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        $('.delete-form').submit(function(e){
            e.preventDefault();
            var formId = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#deleteForm${formId}`).unbind('submit').submit();
                    $('#spinner').show();
                }
            });
        });
</script>
@endpush
