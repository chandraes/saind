@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>KATEGORI COST OPERATIONAL</u></h1>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table" id="data-table">
                <tr>
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#createInvestor"><img
                                src="{{asset('images/cost-operational.svg')}}" width="30"> Tambah Kategori</a>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('swal')
@include('database.cost-operational.create')
@include('database.cost-operational.edit')
<div class="container mt-5 table-responsive">
    <table class="table table-bordered table-hover shadow-sm" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle" style="width: 5%">NO</th>
                <th class="text-center align-middle">NAMA KATEGORI</th>
                <th class="text-center align-middle" style="width: 15%">PERIODE</th>
                <th class="text-center align-middle" style="width: 15%">LIMIT PENGGUNAAN</th>
                <th class="text-center align-middle" style="width: 20%">AKSI</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="align-middle fw-bold text-dark">{{$d->nama}}</td>
                <td class="text-center align-middle text-capitalize">
                    @if($d->periode == 'mingguan')
                        <span class="badge bg-info text-dark"><i class="fa fa-calendar-week me-1"></i>Mingguan</span>
                    @else
                        <span class="badge bg-primary"><i class="fa fa-calendar-alt me-1"></i>Bulanan</span>
                    @endif
                </td>
                <td class="text-center align-middle">
                    <span class="fw-bold text-secondary">{{$d->jumlah_limit}} Kali</span>
                </td>
                <td class="text-center align-middle">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-warning me-1 rounded"
                                onclick="editInvestor({{$d->id}}, '{{$d->nama}}', '{{$d->periode}}', {{$d->jumlah_limit}})">
                            <i class="fa fa-edit"></i> Edit
                        </button>
                        <form action="{{route('database.cost-operational.delete', $d->id)}}" method="post" class="deleteForm d-inline">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-sm btn-danger rounded">
                                <i class="fa fa-trash"></i> Hapus
                            </button>
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
@endpush
@push('js')



<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    function editInvestor(data, id) {
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_periode').value = data.periode;
        document.getElementById('edit_jumlah_limit').value = data.jumlah_limit;
        // Populate other fields...
        document.getElementById('editForm').action = '/database/cost-operational/update/' + id;

        var editModal = new bootstrap.Modal(document.getElementById('editInvestor'));
        editModal.show();
    }

    $('#data').DataTable({
        paging: false,
        scrollCollapse: true,
        scrollY: "550px",
    });

    $('#createForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
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
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
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

</script>
@endpush
