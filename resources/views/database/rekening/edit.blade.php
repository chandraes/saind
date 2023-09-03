@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Edit Rekening {{strtoupper($data->untuk)}}</u></h1>
        </div>
    </div>
    <form action="{{route('rekening.update', $data->id)}}" method="post" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="nama_bank" class="form-label">Nama Bank</label>
                <input type="text" class="form-control @if ($errors->has('nama_bank'))
                    is-invalid
                @endif" name="nama_bank" id="nama" required value="{{$data->nama_bank}}">
                @if ($errors->has('nama_bank'))
                <div class="invalid-feedback">
                    {{$errors->first('nama_bank')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
                <input type="text" class="form-control @if ($errors->has('nomor_rekening'))
                    is-invalid
                @endif" name="nomor_rekening" id="nomor_rekening" required value="{{$data->nomor_rekening}}">
                @if ($errors->has('nomor_rekening'))
                <div class="invalid-feedback">
                    {{$errors->first('nomor_rekening')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="nama_rekening" class="form-label">Atas Nama</label>
                <input type="text" class="form-control @if ($errors->has('nama_rekening'))
                    is-invalid
                @endif" name="nama_rekening" id="nama_rekening" required value="{{$data->nama_rekening}}">
                @if ($errors->has('nama_rekening'))
                <div class="invalid-feedback">
                    {{$errors->first('nama_rekening')}}
                </div>
                @endif
            </div>
        </div>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{route('rekening.index')}}" class="btn btn-secondary" type="button">Batal</a>
          </div>
    </form>
</div>
@endsection
