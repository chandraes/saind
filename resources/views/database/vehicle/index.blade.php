@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Vehicle</u></h1>
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
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalTambahVehicle"><img
                                src="{{asset('images/dumptruckempty.svg')}}" alt="add-document" width="30"> Tambah
                            Vehicle</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahVehicle" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="modalTitleTambah" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleTambah">Tambah Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('vehicle.store')}}" method="post">
                @csrf

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vendor_id" class="form-label">Vendor</label>
                            <select class="form-select" name="vendor_id" id="vendor_id" onchange="toggleInputTambah()" required>
                                <option value=""> -- Pilih Vendor -- </option>
                                @foreach ($vendors as $vendor)
                                <option value="{{$vendor->id}}">{{$vendor->nama}} {{$vendor->perusahaan}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nomor_lambung" class="form-label">Nomor Lambung</label>
                            <input type="text" class="form-control" id="nomor_lambung"
                                value="{{$no_lambung === 1 ? 101 : $no_lambung}}" disabled>
                        </div>
                    </div>
                    <hr>
                    <div class="" id="row-input" hidden>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nopol" class="form-label">Nomor Polisi</label>
                                <input type="text" class="form-control" name="nopol" id="nopol" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nama_stnk" class="form-label">Nama STNK</label>
                                <input type="text" class="form-control" name="nama_stnk" id="nama_stnk" required>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="no_rangka" class="form-label">Nomor Rangka</label>
                                <input type="text" class="form-control" name="no_rangka" id="no_rangka" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="no_mesin" class="form-label">Nomor Mesin</label>
                                <input type="text" class="form-control" name="no_mesin" id="no_mesin" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tipe" class="form-label">Tipe</label>
                                <input type="text" class="form-control" name="tipe" id="tipe" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="tahun" class="form-label">Tahun</label>
                                <input type="number" class="form-control" name="tahun" id="tahun" required>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="no_kartu_gps" class="form-label">Nomor Kartu GPS</label>
                                <input type="text" class="form-control" name="no_kartu_gps" id="no_kartu_gps" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" name="status" id="status" required>
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="container-fluid mt-5 table-responsive ">
<table class="table table-bordered table-hover" id="data-table">
    <thead class="table-success">
        <tr>
            <th class="text-center align-middle">No</th>
            <th class="text-center align-middle">Nomor Lambung</th>
            <th class="text-center align-middle">Vendor</th>
            <th class="text-center align-middle">Tipe</th>
            <th class="text-center align-middle">Tahun</th>
            <th class="text-center align-middle">Status</th>
            <th class="text-center align-middle">Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $d)
        <tr>
            <td class="text-center align-middle">{{$loop->iteration}}</td>
            <td class="text-center align-middle">
                <a href="#" data-bs-toggle="modal" data-bs-target="#modalShow{{$d->id}}">
                    <h5>{{$d->nomor_lambung}}</h5>
                </a>
            </td>
            <td class="align-middle">{{$d->vendor->nama}} {{$d->vendor->perusahaan}}</td>
            <td class="text-center align-middle">{{$d->tipe}}</td>
            <td class="text-center align-middle">{{$d->tahun}}</td>
            <td class="text-center align-middle">
                @if ($d->status == 'aktif')
                <h5><span class="badge bg-success">Aktif</span></h5>
                @else
                <h5><span class="badge bg-danger">Nonaktif</span></h5>
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
         <div class="modal fade" id="modalEdit{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleEdit{{$d->id}}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleEdit{{$d->id}}">EDIT VEHICLE</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('vehicle.update', $d->id)}}" method="post">
                    @csrf
                    @method('PATCH')

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vendor_id" class="form-label">Vendor</label>
                                <select class="form-select" name="vendor_id" id="vendor_id">
                                    <option value=""> -- Pilih Vendor -- </option>
                                    @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}" {{$d->vendor_id == $vendor->id ? 'selected' : ''}}>{{$vendor->nama}} {{$vendor->perusahaan}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nomor_lambung" class="form-label">Nomor Lambung</label>
                                <input type="text" class="form-control" name="nomor_lambung" id="nomor_lambung" readonly value="{{$d->nomor_lambung}}" disabled>
                            </div>
                        </div>
                        <hr>
                        <div class="" id="row-input">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nopol" class="form-label">Nomor Polisi</label>
                                    <input type="text" class="form-control" name="nopol" id="nopol" value="{{$d->nopol}}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nama_stnk" class="form-label">Nama STNK</label>
                                    <input type="text" class="form-control" name="nama_stnk" id="nama_stnk" value="{{$d->nama_stnk}}">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="no_rangka" class="form-label">Nomor Rangka</label>
                                    <input type="text" class="form-control" name="no_rangka" id="no_rangka" value="{{$d->no_rangka}}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="no_mesin" class="form-label">Nomor Mesin</label>
                                    <input type="text" class="form-control" name="no_mesin" id="no_mesin" value="{{$d->tipe}}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tipe" class="form-label">Tipe</label>
                                    <input type="text" class="form-control" name="tipe" id="tipe" value="{{$d->tipe}}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <input type="number" class="form-control" name="tahun" id="tahun" value="{{$d->tahun}}">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="no_kartu_gps" class="form-label">Nomor Kartu GPS</label>
                                    <input type="text" class="form-control" name="no_kartu_gps" id="no_kartu_gps" value="{{$d->no_kartu_gps}}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" name="status" id="status">
                                        <option value="aktif" {{$d->status == 'aktif' ? 'selected' : ''}}>Aktif</option>
                                        <option value="nonaktif" {{$d->status == 'nonaktif' ? 'selected' : ''}}>Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalShow{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitleId">Vehicle</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="vendor_id" class="form-label">Vendor</label>
                                <select class="form-select" name="vendor_id" id="vendor_id" readonly disabled>
                                    <option value=""> -- Pilih Vendor -- </option>
                                    @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}" {{$d->vendor_id == $vendor->id ? 'selected' : ''}}>{{$vendor->nama}} {{$vendor->perusahaan}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nomor_lambung" class="form-label">Nomor Lambung</label>
                                <input type="text" class="form-control" name="nomor_lambung" id="nomor_lambung" readonly disabled value="{{$d->nomor_lambung}}">
                            </div>
                        </div>
                        <hr>
                        <div class="" id="row-input">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nopol" class="form-label">Nomor Polisi</label>
                                    <input type="text" class="form-control" name="nopol" id="nopol" readonly disabled value="{{$d->nopol}}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nama_stnk" class="form-label">Nama STNK</label>
                                    <input type="text" class="form-control" name="nama_stnk" id="nama_stnk" readonly disabled value="{{$d->nama_stnk}}">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="no_rangka" class="form-label">Nomor Rangka</label>
                                    <input type="text" class="form-control" name="no_rangka" id="no_rangka" readonly disabled value="{{$d->no_rangka}}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="no_mesin" class="form-label">Nomor Mesin</label>
                                    <input type="text" class="form-control" name="no_mesin" id="no_mesin" readonly disabled value="{{$d->tipe}}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tipe" class="form-label">Tipe</label>
                                    <input type="text" class="form-control" name="tipe" id="tipe" readonly disabled value="{{$d->tipe}}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="tahun" class="form-label">Tahun</label>
                                    <input type="number" class="form-control" name="tahun" id="tahun" readonly disabled value="{{$d->tahun}}">
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="no_kartu_gps" class="form-label">Nomor Kartu GPS</label>
                                    <input type="text" class="form-control" name="no_kartu_gps" id="no_kartu_gps" readonly disabled value="{{$d->no_kartu_gps}}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" name="status" id="status" readonly disabled>
                                        <option value="aktif" {{$d->status == 'aktif' ? 'selected' : ''}}>Aktif</option>
                                        <option value="nonaktif" {{$d->status == 'nonaktif' ? 'selected' : ''}}>Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalEdit{{$d->id}}">
                            Edit
                          </button>
                    </div>
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
