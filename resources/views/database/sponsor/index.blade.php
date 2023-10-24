@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row ">
        <div class="col-md-12 text-center">
            <h1><u>SPONSOR</u></h1>
        </div>
    </div>
   @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td>
                        @include('database.sponsor.create')
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
                <th class="text-center align-middle">Kode Sponsor</th>
                <th class="text-center align-middle">Nama</th>
                <th class="text-center align-middle">Nomor WA</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="align-middle">
                        <div class="text-center">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#show-{{$d->id}}"><h4>{{$d->kode.sprintf("%02d",$d->nomor_kode_sponsor)}}</h4></a>
                        </div>
                        @include('database.sponsor.show')
                        @include('database.sponsor.edit')
                    </td>
                    <td class="text-center align-middle">{{$d->nama}}</td>
                    <td class="text-center align-middle">{{$d->nomor_wa}}</td>
                    <td class="text-center align-middle">
                       {{-- button delete with sweetalert confirmation --}}
                        <form action="{{route('sponsor.destroy',$d->id)}}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Delete</button>
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

    $(document).ready(function() {
        $('#karyawan-data').DataTable();

    } );

</script>
@endpush
