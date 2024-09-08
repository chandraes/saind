@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>LEGALITAS</u></h1>
        </div>
    </div>
    @include('swal')
    @include('legalitas.kategori')
    @include('legalitas.create')
    <div class="row d-flex justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#jabatan">
                            <img src="{{asset('images/jabatan.svg')}}" alt="dokumen" width="30"> Tambah Kategori
                        </a>
                    </td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalCreate"><img src="{{asset('images/legalitas.svg')}}"
                                alt="add-document" width="30"> Tambah Legalitas</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>


<div class="container mt-5 table-responsive ">
    <table class="table table-hover table-bordered" id="karyawan-data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Kategori</th>
                <th class="text-center align-middle">Nama Dokumen</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $k)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$k->kategori ? $k->kategori->nama : '-'}}</td>
                <td class="text-start align-middle">{{$k->nama}}</td>

                <td class="text-center align-middle">
                    {{-- show button --}}
                    {{-- edit button --}}
                    <a href="{{route('karyawan.edit', $k->id)}}" class="btn btn-warning m-2"><i
                            class="fa fa-edit"></i></a>
                    {{-- delete button --}}
                    <form action="{{route('karyawan.destroy', $k->id)}}" method="post" class="d-inline">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger m-2"
                            onclick="return confirm('Apakah anda yakin untuk menghapus karyawan ini?')"><i
                                class="fa fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script>
    // hide alert after 5 seconds
    setTimeout(function() {
        $('#alert').fadeOut('slow');
    }, 5000);

    $(document).ready(function() {
        $('#karyawan-data').DataTable();

    } );

    function toggleNamaJabatan(id) {

        // check if input is readonly
        if ($('#nama_jabatan-'+id).attr('readonly')) {
            // remove readonly
            $('#nama_jabatan-'+id).removeAttr('readonly');
            // show button
            $('#buttonJabatan-'+id).removeAttr('hidden');
        } else {
            // add readonly
            $('#nama_jabatan-'+id).attr('readonly', true);
            // hide button
            $('#buttonJabatan-'+id).attr('hidden', true);
        }
    }
</script>
@endpush
