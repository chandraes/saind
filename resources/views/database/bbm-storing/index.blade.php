@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BBM Storing</u></h1>
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
    @if (session('error'))
    <div class="row">
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>
                {{session('error')}}
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
            <table class="table" id="data-table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="#"><img src="{{asset('images/bbm.svg')}}" alt="add-rute"
                                width="30"> Tambah Storing</a>
                    </td>

                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container-fluid mt-5 table-responsive">
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">KM</th>
                <th class="text-center align-middle">Vendor</th>
                <th class="text-center align-middle">Mekanik</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

@endsection
@push('css')
<link href="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.css" rel="stylesheet">
{{--
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" /> --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
{{--
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
@endpush
@push('js')


<script src="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.js"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>

<script>
    // success-alert close after 5 second
    $("#success-alert").fadeTo(5000, 500).slideUp(500, function(){
        $("#success-alert").slideUp(500);
    });

    // datatable
    $(document).ready(function() {
        $('#data').DataTable();

    } );

</script>
@endpush
