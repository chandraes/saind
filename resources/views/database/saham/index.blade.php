@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Biodata Pemegang Saham</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#persenAwal"><img
                                    src="{{asset('images/persen.svg')}}" alt="add-document" width="30"> Tambah Persen Awal</a>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#createSaham"><img
                                src="{{asset('images/saham.svg')}}" alt="add-document" width="30"> Tambah Pemegang Saham</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('database.saham.persen-awal')
@include('database.saham.create')
<div class="container mt-5 table-responsive ">
    <table class="table table-hover table-bordered" id="karyawan-data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Persen Awal</th>
                <th class="text-center align-middle">Nama</th>
                <th class="text-center align-middle">Persentase</th>
                <th class="text-center align-middle">Nama Rekening</th>
                <th class="text-center align-middle">Bank</th>
                <th class="text-center align-middle">Nomor Rekening</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $k)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$k->persentase_awal->nama}} ({{$k->persentase_awal->persentase}}%)</td>
                <td class="text-center align-middle">{{$k->nama}}</td>
                <td class="text-center align-middle">{{$k->persentase}}%</td>
                <td class="text-center align-middle">{{$k->nama_rekening}}</td>
                <td class="text-center align-middle">{{$k->bank}}</td>
                <td class="text-center align-middle">{{$k->nomor_rekening}}</td>
                <td class="text-center align-middle">
                    <a href="#" data-bs-toggle="modal" data-bs-target="#editSaham-{{$k->id}}" class="btn btn-warning m-2"><i class="fa fa-edit"></i></a>
                    @include('database.saham.edit')

                    <form action="{{route('pemegang-saham.destroy', $k->id)}}" method="post" class="d-inline" id="deleteForm-{{$k->id}}">
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
    $('#karyawan-data').DataTable({
    });

    function toggleNamaJabatan(id) {

        if ($('#nama-'+id).attr('readonly') && $('#persentase-'+id).attr('readonly')) {
            // remove readonly
            $('#nama-'+id).removeAttr('readonly');
            $('#persentase-'+id).removeAttr('readonly');
            // show button
            $('#buttonJabatan-'+id).removeAttr('hidden');
        } else {
            // add readonly
            $('#nama-'+id).attr('readonly', true);
            $('#persentase-'+id).attr('readonly', true);
            // hide button
            $('#buttonJabatan-'+id).attr('hidden', true);
        }
        }

</script>
@endpush
