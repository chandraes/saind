@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Biodata Karyawan</u></h1>
        </div>
    </div>
    @if (session('success'))
    <div class="row">
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>

            <strong>{{session('success')}}</strong>
        </div>
    </div>
    @endif
    @if (session('error'))
    <div class="row">
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>{{session('error')}}</strong>
        </div>
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
                    <td>
                       <!-- Modal trigger button -->
                       <a href="#" data-bs-toggle="modal" data-bs-target="#jabatan">
                        <img src="{{asset('images/jabatan.svg')}}" alt="dokumen"
                        width="30"> Tambah Jabatan
                       </a>

                       <!-- Modal Body -->
                       <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
                       <div class="modal fade" id="jabatan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="jabatanTitleId" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="jabatanTitleId">Jabatan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-responsive table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center align-middle">No</th>
                                                <th class="text-center align-middle">Nama Jabatan</th>
                                                <th class="text-center align-middle">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($jabatan as $j)
                                            <tr>
                                                <td class="text-center align-middle">{{$loop->iteration}}</td>
                                                <td class="text-center align-middle">
                                                    <form action="{{route('karyawan.jabatan-update', $j->id)}}" method="post" id="updateJabatan">
                                                        @csrf
                                                        @method('patch')
                                                        <input type="text" class="form-control" name="nama_jabatan" id="nama_jabatan-{{$j->id}}"
                                                            aria-describedby="helpId" placeholder="" value="{{$j->nama}}" readonly>
                                                        <div class="btn-group m-3" role="group" aria-label="Save or cancel" id="buttonJabatan-{{$j->id}}" hidden>
                                                            <button type="submit" class="btn btn-success">Simpan</button>
                                                            <a onclick="toggleNamaJabatan({{$j->id}})" type="button" class="btn btn-secondary">Batal</a>
                                                        </div>
                                                    </form>
                                                </td>
                                                <td class="text-center align-middle">
                                                    {{-- button to submit form #updateJabatan --}}

                                                    <a onclick="toggleNamaJabatan({{$j->id}})" class="btn btn-warning"><i class="fa fa-edit"></i></a>
                                                    {{-- form delete with confirmation --}}
                                                    <form action="{{route('karyawan.jabatan-destroy', $j->id)}}" method="post" class="d-inline">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah anda yakin untuk menghapus jabatan ini?')"><i class="fa fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <form action="{{route('karyawan.jabatan-store')}}" method="post">
                                        @csrf
                                        <div class="input-group mb-3 mt-3">
                                            <input type="text" class="form-control" name="nama_jabatan_tambah" id="nama_jabatan_tambah"
                                                aria-describedby="helpId" placeholder="Nama Jabatan" required>
                                            <button type="submit" class="btn btn-primary">Tambah</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                       </div>
                    </td>
                    <td><a href="{{route('karyawan.create')}}"><img
                                src="{{asset('images/karyawan.svg')}}" alt="add-document" width="30"> Tambah Karyawan</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid mt-5 table-responsive ">
    <table class="table table-hover table-bordered" id="karyawan-data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Nama</th>
                <th class="text-center align-middle">Nickname</th>
                <th class="text-center align-middle">Jabatan</th>
                <th class="text-center align-middle">Status</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($karyawans as $k)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$k->nama}}</td>
                <td class="text-center align-middle">{{$k->nickname}}</td>
                <td class="text-center align-middle">{{$k->jabatan->nama}}</td>
                <td class="text-center align-middle">
                    @if ($k->status == 'aktif')
                    <h4><span class="badge bg-success text-white">{{strtoupper($k->status)}}</span></h4>
                    @elseif($k->status == 'nonaktif')
                    <h4><span class="badge bg-danger text-white">{{strtoupper($k->status)}}</span></h4>
                    @endif
                </td>
                <td class="text-center align-middle">
                    {{-- show button --}}
                    <a href="{{route('karyawan.show', $k->id)}}" class="btn btn-primary m-2" target="_blank"><i class="fa fa-eye"></i></a>
                    {{-- edit button --}}
                    <a href="{{route('karyawan.edit', $k->id)}}" class="btn btn-warning m-2"><i class="fa fa-edit"></i></a>
                    {{-- delete button --}}
                    <form action="{{route('karyawan.destroy', $k->id)}}" method="post" class="d-inline">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger m-2" onclick="return confirm('Apakah anda yakin untuk menghapus karyawan ini?')"><i class="fa fa-trash"></i></button>
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
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.js"></script>
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
