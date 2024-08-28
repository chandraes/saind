@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Biodata Direksi</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row d-flex justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="{{route('direksi.create')}}"><img
                                src="{{asset('images/direksi.svg')}}" alt="add-document" width="30"> Tambah Direksi</a>
                    </td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <form action="{{route('direksi.index')}}" method="get">
                <div class="row d-flex">
                    <div class="col-md-6 mb-3">
                        {{-- <label for="status" class="form-label">Status</label> --}}
                        <select class="form-select" name="status" id="status" required>
                            <option value="aktif" @if ($status=='aktif' ) selected @endif>Aktif</option>
                            <option value="nonaktif" @if ($status=='nonaktif' ) selected @endif>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </div>

            </form>
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
            @foreach ($data as $k)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$k->nama}}</td>
                <td class="text-center align-middle">{{$k->nickname}}</td>
                <td class="text-center align-middle">{{$k->jabatan}}</td>
                <td class="text-center align-middle">
                    @if ($k->status == 'aktif')
                    <h4><span class="badge bg-success text-white">{{strtoupper($k->status)}}</span></h4>
                    @elseif($k->status == 'nonaktif')
                    <h4><span class="badge bg-danger text-white">{{strtoupper($k->status)}}</span></h4>
                    @endif
                </td>
                <td class="text-center align-middle">
                    {{-- show button --}}
                    <a href="{{route('direksi.show', $k->id)}}" class="btn btn-primary m-2" target="_blank"><i class="fa fa-eye"></i></a>
                    {{-- edit button --}}
                    <a href="{{route('direksi.edit', $k->id)}}" class="btn btn-warning m-2"><i class="fa fa-edit"></i></a>
                    {{-- delete button --}}
                    <form action="{{route('direksi.destroy', $k->id)}}" method="post" class="d-inline" id="deleteForm-{{$k->id}}">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger m-2"><i class="fa fa-trash"></i></button>
                    </form>
                    <script>
                        $('#deleteForm-{{$k->id}}').submit(function(e){
                            e.preventDefault();
                            Swal.fire({
                                title: 'Apakah anda yakin?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Ya, simpan!'
                                }).then((result) => {
                                if (result.isConfirmed) {
                                    this.submit();
                                }
                            })
                        });
                    </script>
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
