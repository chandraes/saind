@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BBM Storing</u></h1>
        </div>
    </div>
    @include('swal')
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
                <tr>
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="#"  data-bs-toggle="modal" data-bs-target="#create-storing"><img src="{{asset('images/bbm.svg')}}"
                                width="30"> Tambah Storing</a>
                        @include('database.bbm-storing.create')
                    </td>

                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container mt-5 table-responsive">
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">KM</th>
                <th class="text-center align-middle">Tagihan ke Vendor</th>
                <th class="text-center align-middle">Bayar ke Mekanik</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$d->km}}</td>
                <td class="text-center align-middle">{{number_format($d->biaya_vendor, 0, ',', '.')}}</td>
                <td class="text-center align-middle">{{number_format($d->biaya_mekanik, 0, ',', '.')}}</td>
                <td class="text-center align-middle">
                    @include('database.bbm-storing.edit')
                    <form action="{{route('bbm-storing.destroy', $d)}}" method="post" id="deleteForm-{{$d->id}}">
                        @method('delete')
                        @csrf
                        <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal" data-bs-target="#edit-{{$d->id}}">
                            Edit
                        </button>
                        <button type="submit" class="btn btn-danger m-2">Hapus</button>
                    </form>
                    <script>

                        $('#deleteForm-{{$d->id}}').submit(function(e){
                            e.preventDefault();
                            Swal.fire({
                                title: 'Apakah anda yakin untuk menghapus data ini?',
                                icon: 'error',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Ya, hapus!'
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
<script src="{{asset('assets/js/jquery.maskMoney.js')}}"></script>
<script>
    // datatable
    $(document).ready(function() {
        $('#data').DataTable();
        $('#biaya_vendor').maskMoney();
        $('#biaya_mekanik').maskMoney();
    });

      // masukForm on submit, sweetalert confirm
      $('#masukForm').submit(function(e){
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
                    this.submit();
                }
            })
        });

</script>
@endpush
