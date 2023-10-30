@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Vehicle</u></h1>
        </div>
    </div>
   @include('swal')
   {{-- if has any error --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Whoops!</strong> Ada kesalahan dalam input data:
        <ul>
            @foreach ($errors->all() as $error)
            <li><strong>{{$error}}</strong></li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalTambahVehicle"><img
                                src="{{asset('images/dumptruckempty.svg')}}" alt="add-document" width="30"> Tambah
                            Vehicle</a>
                    </td>
                    <td><a href="{{route('print-preview-vehicle')}}" target="_blank"><img
                        src="{{asset('images/document.svg')}}" alt="add-document" width="30"> Print Vehicle</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

@include('database.vehicle.create')

<div class="container-fluid mt-5 table-responsive ">
<table class="table table-bordered table-hover" id="data-table">
    <thead class="table-success">
        <tr>
            <th class="text-center align-middle">No</th>
            <th class="text-center align-middle">Nomor Lambung</th>
            <th class="text-center align-middle">Vendor</th>
            <th class="text-center align-middle">Nopol</th>
            <th class="text-center align-middle">Nama STNK</th>
            <th class="text-center align-middle">No Rangka</th>
            <th class="text-center align-middle">No Mesin</th>
            <th class="text-center align-middle">Tipe</th>
            <th class="text-center align-middle">Index</th>
            <th class="text-center align-middle">Tahun</th>
            <th class="text-center align-middle">No. GPS</th>
            <th class="text-center align-middle">Status</th>
            <th class="text-center align-middle">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $d)
        <tr>
            <td class="text-center align-middle">{{$loop->iteration}}</td>
            <td class="text-center align-middle">
                <a href="#" class="@if ($d->no_index < 30 || $d->tahun < 2016) text-danger @endif" data-bs-toggle="modal" data-bs-target="#modalShow{{$d->id}}">
                    <h5 class="">{{$d->nomor_lambung}}</h5>
                </a>
            </td>
            <td class="align-middle">{{$d->vendor->nama}} {{$d->vendor->perusahaan}}</td>
            <td class="text-center align-middle">{{$d->nopol}}</td>
            <td class="text-center align-middle">{{$d->nama_stnk}}</td>
            <td class="text-center align-middle">{{$d->no_rangka}}</td>
            <td class="text-center align-middle">{{$d->no_mesin}}</td>
            <td class="text-center align-middle">{{$d->tipe}}</td>
            <td class="text-center align-middle @if ($d->no_index < 30)
                text-danger
            @endif">{{$d->no_index}}</td>
            <td class="text-center align-middle @if ($d->tahun < 2016) text-danger @endif">{{$d->tahun}}</td>
            <td class="text-center align-middle">{{$d->no_kartu_gps}}</td>
            <td class="text-center align-middle">
                @if ($d->status == 'aktif')
                <h5><span class="badge bg-success">Aktif</span></h5>
                @elseif($d->status == 'nonaktif')
                <h5><span class="badge bg-danger">Nonaktif</span></h5>
                @elseif($d->status == 'proses')
                <h5><span class="badge bg-warning">Sedang Jalan</span></h5>
                @endif
            </td>
            <td class="text-center align-middle">
                <form action="{{route('vehicle.destroy', $d->id)}}" method="post">
                    @csrf
                    @method('DELETE')
                    <a href="#" data-bs-toggle="modal" data-bs-target="#modalEdit{{$d->id}}" class="btn btn-warning m-2">Edit</a>
                    <button type="submit" class="btn btn-danger m-2"
                        onclick="return confirm('Apakah anda yakin ingin menghapus data ini?')">Hapus</button>
                </form>
            </td>
        </tr>
        @include('database.vehicle.edit')

        @include('database.vehicle.show')

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
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    // hide alert after 5 seconds
    setTimeout(function() {
        $('#alert').fadeOut('slow');
    }, 5000);

    $(document).ready(function() {
        $('#data-table').DataTable();

    } );

    function toggleInputTambah() {
        var value = document.getElementById('vendor_id').value;
        if (value == '') {
            document.getElementById('row-input').hidden = true;
        } else {
            document.getElementById('row-input').hidden = false;
        }
    }
</script>
@endpush
