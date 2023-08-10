@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>KONTRAK</u></h1>
        </div>
    </div>
    {{-- if has message, trigger sweetalert --}}
    @if (session('success'))
    <div class="row">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

            <strong>{{session('success')}}</strong>
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
                    <td><a href="{{route('dokumen')}}"><img src="{{asset('images/document.svg')}}" alt="dokumen"
                                width="30"> Dokumen</a></td>
                    <td><a href="" data-bs-toggle="modal" data-bs-target="#modalId"><img
                                src="{{asset('images/document-add.svg')}}" alt="add-document" width="30"> Tambah Kontrak</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Modal Body -->
<!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
<div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-l" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Tambah Kontrak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('kontrak.store')}}" method="post">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="vendor_id" class="form-label">Vendor</label>
                        <select class="form-select" name="vendor_id" id="vendor_id">
                            @foreach ($vendors as $v)
                            <option value="{{$v->id}}">{{$v->nama}} ({{$v->perusahaan}})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="nama_singkatan" class="form-label">Nama Singkatan</label>
                        <input type="text" class="form-control" name="nama_singkatan" id="nama_singkatan" aria-describedby="helpId"
                            placeholder="">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>

                </div>
            </form>
        </div>
    </div>
</div>


<div class="container-fluid mt-5">
    <table id="sph" class="table table-responsive table-bordered table-hover">
        <thead class="table-success">
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Vendor</th>
                <th class="text-center">Nama Singkatan</th>
                <th class="text-center">No. Kontrak</th>
                <th class="text-center">Tanggal</th>
                <th class="text-center">Dibuat Oleh</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kontraks as $k)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="align-middle">{{$k->vendor->nama}} ({{$k->vendor->perusahaan}})</td>
                <td class="text-center align-middle">{{$k->nama_singkatan}}</td>
                <td class="text-center align-middle">{{$k->nomor}}</td>
                @php
                    $date = date_create($k->tanggal);
                    $date = date_format($date, 'd-M-Y');
                @endphp
                <td class="text-center align-middle">{{$date}}</td>
                <td class="text-center align-middle">{{$k->createdBy['name']}}</td>
                <td class="text-center align-middle">
                    @if ($k->dokumen_asli)
                    @else
                    <a href="" class="btn btn-primary me-2">Upload Kontrak</a>
                    @endif
                    <a href="{{route('kontrak.doc', $k->id)}}" target="_blank" class="btn btn-success me-2">PDF</a>
                    <a href="{{route('kontrak.edit', $k->id)}}" class="btn btn-warning me-2">Edit</a>
                    <form action="{{route('kontrak.destroy', $k->id)}}" method="post" class="d-inline me-2">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger me-2"
                            onclick="return confirm('Yakin ingin menghapus data?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('css')
<link href="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.css" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.js"></script>
<script>
    var clockElement = document.getElementById('clock');
    function clock() {
        clockElement.textContent = new Date().toLocaleString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric',
            hour: 'numeric',
            minute: 'numeric',
            second: 'numeric',
            hour12: false
        });
    }
    setInterval(clock, 1000);

    $(document).ready(function() {
        $('#sph').DataTable();
    } );
</script>
@endpush
