@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Tambah Customer</u></h1>
        </div>
    </div>
    @if (session('error'))
    <div class="row">
        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="success-alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>
                {{session('error')}}
            </strong>
        </div>
    </div>
    @endif
    <form action="{{route('customer.store')}}" method="post">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama</label>
                    <input type="text" class="form-control @if ($errors->has('nama')) is-invalid @endif" name="nama" id="nama" aria-describedby="helpId" required
                        placeholder="" value="{{old('nama')}}">
                    @if ($errors->has('nama'))
                    <div class="invalid-feedback">
                        {{$errors->first('nama')}}
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="singkatan" class="form-label">Singkatan</label>
                    <input type="text" class="form-control @if ($errors->has('singkatan')) is-invalid @endif" name="singkatan" id="singkatan" aria-describedby="helpId"
                        required placeholder="">
                        @if ($errors->has('singkatan'))
                        <div class="invalid-feedback">
                            {{$errors->first('singkatan')}}
                        </div>
                        @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="npwp" class="form-label">NPWP</label>
                    <input type="text" class="form-control @if ($errors->has('npwp')) is-invalid @endif" name="npwp" id="npwp" aria-describedby="helpId"
                        placeholder="" required>
                        @if ($errors->has('npwp'))
                        <div class="invalid-feedback">
                            {{$errors->first('npwp')}}
                        </div>
                        @endif
                </div>
            </div>
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control @if ($errors->has('alamat')) is-invalid @endif" name="alamat" id="alamat" rows="3"></textarea>
                    @if ($errors->has('alamat'))
                    <div class="invalid-feedback">
                        {{$errors->first('alamat')}}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <br>
        <hr>
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="contact_person" class="form-label">Contact Person</label>
                    <input type="text" class="form-control @if ($errors->has('contact_person')) is-invalid @endif" name="contact_person" id="contact_person" required
                        aria-describedby="helpId" placeholder="">
                        @if ($errors->has('contact_person'))
                        <div class="invalid-feedback">
                            {{$errors->first('contact_person')}}
                        </div>
                        @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="jabatan" class="form-label">Jabatan</label>
                    <input type="text" class="form-control @if ($errors->has('jabatan')) is-invalid @endif" name="jabatan" id="jabatan" required
                        aria-describedby="helpId" placeholder="">
                        @if ($errors->has('jabatan'))
                        <div class="invalid-feedback">
                            {{$errors->first('jabatan')}}
                        </div>
                        @endif
                </div>
            </div>
        </div>
        <br>
        <hr>
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="no_hp" class="form-label">Nomor HP</label>
                    <input type="text" class="form-control @if ($errors->has('no_hp')) is-invalid @endif" name="no_hp" id="no_hp" required
                        aria-describedby="helpId" placeholder="">
                        @if ($errors->has('no_hp'))
                        <div class="invalid-feedback">
                            {{$errors->first('no_hp')}}
                        </div>
                        @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="no_wa" class="form-label">Nomor WA</label>
                    <input type="text" class="form-control @if ($errors->has('no_wa')) is-invalid @endif" name="no_wa" id="no_wa" required
                        aria-describedby="helpId" placeholder="">
                        @if ($errors->has('no_wa'))
                        <div class="invalid-feedback">
                            {{$errors->first('no_wa')}}
                        </div>
                        @endif
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="text" class="form-control @if ($errors->has('email')) is-invalid @endif" name="email" id="email" required
                        aria-describedby="helpId" placeholder="">
                        @if ($errors->has('email'))
                        <div class="invalid-feedback">
                            {{$errors->first('email')}}
                        </div>
                        @endif
                </div>
            </div>
        </div>
        <br><hr>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="harga_opname" class="form-label">Harga OPNAME</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp.</span>
                        <input type="number" class="form-control @if ($errors->has('harga_opname')) is-invalid @endif" name="harga_opname" id="harga_opname" required
                            aria-describedby="helpId" placeholder="">
                            @if ($errors->has('harga_opname'))
                            <div class="invalid-feedback">
                                {{$errors->first('harga_opname')}}
                            </div>
                            @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="harga_titipan" class="form-label">Harga Titipan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp.</span>
                        <input type="number" class="form-control @if ($errors->has('harga_titipan')) is-invalid @endif" name="harga_titipan" id="harga_titipan" required
                            aria-describedby="helpId" placeholder="">
                            @if ($errors->has('harga_titipan'))
                            <div class="invalid-feedback">
                                {{$errors->first('harga_titipan')}}
                            </div>
                            @endif
                    </div>
                </div>
            </div>
        </div>
        <br><hr>
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="rute" class="form-label">Rute</label>
                    <select class="form-select select2 @if ($errors->has('rute')) is-invalid @endif" name="rute" id="rute" required multiple>
                        <option value="">Pilih Rute</option>
                        @foreach ($rute as $item)
                        <option value="{{$item->id}}">{{$item->nama}}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('rute'))
                    <div class="invalid-feedback">
                        {{$errors->first('rute')}}
                    </div>
                    @endif
                </div>
            </div>
        <br>
        <div class="d-grid gap-3 mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a type="button" class="btn btn-secondary" href="{{route('customer.index')}}">Batal</a>
        </div>
    </form>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush
@push('js')

<script src="{{asset('assets/js/jquery.min.js')}}"></script>
    <script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            $('#rute').select2();
        });
    </script>
@endpush
