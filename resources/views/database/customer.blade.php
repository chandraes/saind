@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Customer</u></h1>
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
                                src="{{asset('images/company.svg')}}" alt="add-rute" width="30"> Tambah Customer</a>
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
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Tambah Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('customer.store')}}" method="post">
                @csrf
                <div class="modal-body bg-white">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama</label>
                                <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId"
                                    required placeholder="">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="singkatan" class="form-label">Singkatan</label>
                                <input type="text" class="form-control" name="singkatan" id="singkatan"
                                    aria-describedby="helpId" required placeholder="">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="contact_person" class="form-label">Contact Person</label>
                                <input type="text" class="form-control" name="contact_person" id="contact_person"
                                    required aria-describedby="helpId" placeholder="">
                            </div>
                        </div>
                    </div>
                    <br>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="harga_opname" class="form-label">Harga OPNAME</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" name="harga_opname" id="harga_opname"
                                        required aria-describedby="helpId" placeholder="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="harga_titipan" class="form-label">Harga Titipan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" name="harga_titipan"
                                        id="harga_titipan" required aria-describedby="helpId" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <hr>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="container-fluid mt-5">
    <table class="table table-responsive table-bordered table-hover" id="data">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Nama Customer</th>
                <th class="text-center align-middle">Contact Person</th>
                <th class="text-center align-middle">Harga OPNAME</th>
                <th class="text-center align-middle">Harga Titipan</th>
                <th class="text-center align-middle">Dibuat Oleh</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr class="text-center align-middle">
                <td>{{$loop->iteration}}</td>
                <td>{{$d->nama}} ({{$d->singkatan}})</td>
                <td>{{$d->contact_person}}</td>
                <td>Rp. {{number_format($d->harga_opname, 0, ',', '.')}}</td>
                <td>Rp. {{number_format($d->harga_titipan, 0, ',', '.')}}</td>
                <td>{{$d->createdBy['name']}}</td>
                <td>
                    <div class="d-flex justify-content-center">
                        <a href="" data-bs-toggle="modal" data-bs-target="#modal{{$d->id}}"
                            class="btn btn-warning btn-sm me-2">Ubah</a>
                        <form action="{{route('customer.destroy', $d->id)}}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
            <div class="modal fade" id="modal{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
                aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitleId">Tambah Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('customer.update', [$d->id])}}" method="post">
                            @csrf
                            @method('PATCH')
                            <div class="modal-body bg-white">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nama" class="form-label">Nama</label>
                                            <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId"
                                                required placeholder="" value="{{$d->nama}}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="singkatan" class="form-label">Singkatan</label>
                                            <input type="text" class="form-control" name="singkatan" id="singkatan"
                                                aria-describedby="helpId" required placeholder="" value="{{$d->singkatan}}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="contact_person" class="form-label">Contact Person</label>
                                            <input type="text" class="form-control" name="contact_person" id="contact_person"
                                                required aria-describedby="helpId" placeholder="" value="{{$d->contact_person}}">
                                        </div>
                                    </div>
                                </div>
                                <br>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="harga_opname" class="form-label">Harga OPNAME</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp.</span>
                                                <input type="number" class="form-control" name="harga_opname" id="harga_opname"
                                                    required aria-describedby="helpId" placeholder="" value="{{$d->harga_opname}}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="harga_titipan_dibawah" class="form-label">Harga Titipan</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp.</span>
                                                <input type="number" class="form-control" name="harga_titipan_dibawah"
                                                    id="harga_titipan_dibawah" required aria-describedby="helpId" placeholder="" value="{{$d->harga_titipan}}">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <br>
                                <hr>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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

<script>
    // success-alert close after 5 second
    $("#success-alert").fadeTo(5000, 500).slideUp(500, function(){
        $("#success-alert").slideUp(500);
    });

    $(document).ready(function() {
        var data = {!! $data->pluck('id') !!}

        $('#data').DataTable();

        for (let i = 0; i < data.length; i++) {
            $('.edit-'+data[i]).select2(
                {
                    placeholder: "Pilih Rute",
                    allowClear: true,
                    theme: 'bootstrap-5',
                    width: 'resolve'
                }
            );
        }

        $('#store-route').select2(
            {
                placeholder: "Pilih Rute",
                allowClear: true,
                theme: 'bootstrap-5',
                width: 'resolve'
            }
        );
    } );
</script>
@endpush
