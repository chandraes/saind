@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Customer</u></h1>
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
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('database')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen"
                                width="30"> Database</a></td>
                    <td><a href="{{route('customer.create')}}"><img src="{{asset('images/company.svg')}}" alt="add-rute"
                                width="30"> Tambah Customer</a>
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
                <th class="text-center align-middle">Nama Customer</th>
                <th class="text-center align-middle">Contact Person</th>
                <th class="text-center align-middle">Harga Tagihan</th>
                <th class="text-center align-middle">Rute</th>
                <th class="text-center align-middle">Dokumen</th>
                <th class="text-center align-middle">Dibuat Oleh</th>
                <th class="text-center align-middle">Status</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr class="text-center align-middle">
                <td>{{$loop->iteration}}</td>
                <td><a href="{{route('customer.show', $d->id)}}"><strong>{{$d->nama}} ({{$d->singkatan}})</strong></a></td>
                <td>{{$d->contact_person}}</td>
                <td>
                    @foreach ($d->customer_tagihan as $t)
                    <h5><span class="badge bg-primary">Rp. {{number_format($t->harga_tagihan, 0, ',', '.')}}</span></h5>
                    @endforeach
                </td>
                <td>
                    @foreach ($d->rute as $r)
                    <h5><span class="badge bg-primary">{{$r->nama}}</span></h5>
                    @endforeach
                </td>
                <td>
                    <!-- Modal trigger button -->
                    <button type="button" class="btn btn-success" data-bs-toggle="modal"
                        data-bs-target="#modalTambahDokumen{{$d->id}}">
                        Tambah Dokumen
                    </button>

                    <div class="modal fade" id="modalTambahDokumen{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                        data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitleId">Tambah Dokumen</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{route('customer.document-store', [$d->id])}}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="mb-3">
                                                <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                                                <input type="text"
                                                    class="form-control @if ($errors->has('nama_dokumen')) is-invalid @endif"
                                                    name="nama_dokumen" id="nama_dokumen" required aria-describedby="helpId"
                                                    placeholder="">
                                                @if ($errors->has('nama_dokumen'))
                                                <div class="invalid-feedback">
                                                    {{$errors->first('nama_dokumen')}}
                                                </div>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <label for="file" class="form-label">File</label>
                                                <input type="file"
                                                    class="form-control @if ($errors->has('file')) is-invalid @endif"
                                                    name="file" id="file" required aria-describedby="helpId"
                                                    placeholder="">
                                                @if ($errors->has('file'))
                                                <div class="invalid-feedback">
                                                    {{$errors->first('file')}}
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal trigger button -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#modalDokumen{{$d->id}}">
                        Lihat Dokumen
                    </button>

                    <div class="modal fade" id="modalDokumen{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                        data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitleId">Dokumen Customer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-success">
                                            <tr>
                                                <th class="text-center align-middle">No</th>
                                                <th class="text-center align-middle">Nama Dokumen</th>
                                                <th class="text-center align-middle">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($d->document as $doc)
                                            <tr class="text-center align-middle">
                                                <td>{{$loop->iteration}}</td>
                                                <td>{{$doc->nama_dokumen}}</td>
                                                <td>
                                                    <a href="{{route('customer.document-download', [$doc->id])}}"
                                                        class="btn btn-primary m-2" target="_blank">
                                                        <i class="fa fa-download"></i>
                                                    </a>
                                                    <form action="{{route('customer.document-destroy', [$doc->id])}}"
                                                        method="post" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger m-2"
                                                            onclick="return confirm('Apakah anda yakin ingin menghapus data ini?')">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <td>{{$d->createdBy['name']}}</td>
                <td class="text-center align-middle">
                    <div class="text-center">
                        <button class="btn {{$d->status == 1 ? "btn-success" : "btn-danger"}}" data-bs-toggle="modal"
                        data-bs-target="#void-{{$d->id}}">{{$d->status == 1 ? "Aktif" : "Nonaktif"}}</button>
                    </div>
                    <div class="modal fade" id="void-{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                        data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm"
                            role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTitleId">Ubah Status Customer</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{route('customer.ubah-status', $d)}}" method="post">
                                    @csrf
                                    <div class="modal-body">
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Password" aria-label="Password" aria-describedby="password"
                                            required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Tutup</button>
                                        <button type="submit" class="btn btn-primary">Lanjutkan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    {{-- edit tagihan button --}}
                    <a href="{{route('customer.tagihan-edit', $d->id)}}" class="btn btn-info">Edit Tagihan</a>
                    <a href="{{route('customer.edit', $d->id)}}" class="btn btn-primary m-2">
                        <i class="fa fa-edit"></i>
                    </a>
                    <form action="{{route('customer.destroy', [$d->id])}}" method="post" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger m-2"
                            onclick="return confirm('Apakah anda yakin ingin menghapus data ini?')">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
{{--
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" /> --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
{{--
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}
@endpush
@push('js')


<script src="{{asset('assets/js/dt5.min.js')}}"></script>
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
