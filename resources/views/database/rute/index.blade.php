@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Rute</u></h1>
        </div>
    </div>
    @if (session('success'))
    <div class="row">
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>
                {{session('success')}}
            </strong>
        </div>
    </div>
    @endif
    <div class="row float-end">
        <div class="col-md-12">
            <strong>
                <span id="clock"></span>
            </strong>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-4">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="" data-bs-toggle="modal" data-bs-target="#modalId"><img
                                src="{{asset('images/rute.svg')}}" alt="add-rute" width="30"> Tambah Rute</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('database.rute.create')
<div class="container mt-5 table-responsive">
    <table class="table table-bordered table-hover table-responsive" id="data">
        <thead class="table-success">
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Rute</th>
                <th class="text-center">Jarak (Km)</th>
                <th class="text-center">Uang Jalan</th>
                <th class="text-center">Dibuat oleh</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center">{{$loop->iteration}}</td>
                <td class="text-center">{{$d->nama}}</td>
                <td class="text-center">{{$d->jarak}}</td>
                <td class="text-center">Rp. {{number_format($d->uang_jalan, 0, ',', '.')}}</td>
                <td class="text-center">{{$d->user->name}}</td>
                <td>
                    <div class="d-flex justify-content-center">
                        <a href="" data-bs-toggle="modal" data-bs-target="#modal{{$d->id}}" class="btn btn-warning btn-sm me-2">Ubah</a>
                        <form action="{{route('rute.destroy', $d->id)}}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            @include('database.rute.edit')
            @endforeach
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

    $('#uang_jalan').maskMoney({
        thousands: '.',
        decimal: ',',
        precision: 0
    });



    $(document).ready(function() {
        $('#data').DataTable();
    } );
</script>
@endpush
