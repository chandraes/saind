@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Nama Direksi</u></h1>
        </div>
    </div>
    @include('swal')
    <form action="{{route('billing.kasbon.direksi.bayar.list')}}" method="get">
        <div class="justify-content-center px-5">
            <div class="px-5">
                <label for="direksi_id" class="form-label">Direksi</label>
                <div class="input-group mb-3">
                    <select class="form-select" name="direksi_id" id="direksi_id" required>
                        <option selected> -- Pilih Direksi -- </option>
                        @foreach ($data as $d)
                        <option value="{{$d->id}}">{{$d->nama}}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-outline-primary" type="submit" id="">Lanjutkan</button>
                    <a href="{{route('billing.index')}}" class="btn btn-outline-danger">Keluar</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
