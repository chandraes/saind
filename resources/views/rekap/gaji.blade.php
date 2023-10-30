@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>PILIH BULAN DAN TAHUN</u></h1>
        </div>
    </div>
    @include('swal')
    @php
    $bulan = [
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
    @endphp
    <form action="{{route('rekap-gaji-detail')}}" method="get">
        <div class="justify-content-center px-5">
            <div class="px-5">
                <label for="bulan" class="form-label">Bulan & Tahun</label>
                <div class="input-group mb-3">
                    <select class="form-select" name="bulan" id="bulan" required>
                        <option selected> -- Pilih Bulan -- </option>
                        @foreach ($bulan as $key => $value)
                        <option value="{{$key}}">{{$value}}</option>
                        @endforeach
                    </select>
                    <input type="text" name="tahun" id="tahun" class="form-control" value="{{date('Y')}}" required>
                    <button class="btn btn-outline-primary" type="submit" id="">Lanjutkan</button>
                    <a href="{{route('billing.index')}}" class="btn btn-outline-danger">Keluar</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
