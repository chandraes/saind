@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Rute</u></h1>
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
                                src="{{asset('images/rute.svg')}}" alt="add-rute" width="30"> Tambah Rute</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Tambah Rute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('rute.store')}}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Rute</label>
                                <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId" required
                                    placeholder="">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="jarak" class="form-label">Jarak (Km)</label>
                                <input type="number" class="form-control" name="jarak" id="jarak" required step="any"
                                    aria-describedby="helpId" placeholder="">
                            </div>
                        </div>
                        <div class="col-md-5">
                            {{-- form group input number with prefix Rp. show input in currency format --}}
                            <div class="mb-3">
                                <label for="uang_jalan" class="form-label">Uang Jalan</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" name="uang_jalan" id="uang_jalan" required
                                        aria-describedby="helpId" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="container mt-5">
    <table class="table table-responsive table-bordered table-hover" id="data">
        <thead class="table-success">
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Rute</th>
                <th class="text-center">Jarak (Km)</th>
                <th class="text-center">Uang Jalan</th>
                <th class="text-center">Dibuat oleh</th>
                <th class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center">{{$loop->iteration}}</td>
                <td class="text-center">{{$d->nama}}</td>
                <td class="text-center">{{$d->jarak}}</td>
                <td class="text-center">Rp. {{number_format($d->uang_jalan, 0, ',', '.')}}</td>
                <td class="text-center">{{$d->user->name}}</td>
                <td>
                    <div class="d-flex justify-content-center">
                        <a href="" data-bs-toggle="modal" data-bs-target="#modal{{$d->id}}" class="btn btn-warning btn-sm me-2">Ubah</a>
                        <form action="{{route('rute.destroy', $d->id)}}" method="post">
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
                            <h5 class="modal-title" id="modalTitleId">Ubah Rute</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('rute.update', ['rute' => $d->id])}}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nama" class="form-label">Nama Rute</label>
                                            <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId" required
                                                placeholder="" value="{{$d->nama}}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="jarak" class="form-label">Jarak (Km)</label>
                                            <input type="number" class="form-control" name="jarak" id="jarak" required step="any"
                                                aria-describedby="helpId" placeholder="" value="{{$d->jarak}}">
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        {{-- form group input number with prefix Rp. show input in currency format --}}
                                        <div class="mb-3">
                                            <label for="uang_jalan" class="form-label">Uang Jalan</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp.</span>
                                                <input type="number" class="form-control" name="uang_jalan" id="uang_jalan" required
                                                    aria-describedby="helpId" placeholder="" value="{{$d->uang_jalan}}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Ubah</button>
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
@endpush
@push('js')

<script src="{{asset('assets/plugins/sweet-alert/sweetalert.min.js')}}"></script>
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.5/datatables.min.js"></script>

<script>
    const myModal = new bootstrap.Modal(document.getElementById('modalId'), options)

</script>
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

    // success-alert close after 5 second
    $("#success-alert").fadeTo(5000, 500).slideUp(500, function(){
        $("#success-alert").slideUp(500);
    });

    $(document).ready(function() {
        $('#data').DataTable();
    } );
</script>
@endpush
