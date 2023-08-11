@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Pembayaran Vendor</u></h1>
        </div>
    </div>
    @if (session('error'))
    <div class="row">
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <strong>
                {{session('error')}}
            </strong>
        </div>
    </div>
    @endif
    <form action="{{route('vendor.uang-jalan.store')}}" method="post">
        @csrf
        <div class="row mt-3 mb-3">
            <div class="row justify-content-center">
                <div class="col-md-6">
                <table class="table table-bordered">
                    <thead>
                        <tr class="text-center align-middle">
                            <th class="text-center align-middle">No</th>
                            <th scope="col">Rute</th>
                            <th scope="col">Harga Kesepakatan Uang Jalan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white">
                        @foreach ($rutes as $v)
                        <tr>
                            <td class="text-center align-middle">{{$loop->iteration}}</td>
                            <td>{{$v->nama}}</td>
                            <td>
                                <input type="hidden" name="vendor_id" value="{{$id}}">
                                <input type="hidden" name="rute_id[]" value="{{$v->id}}">
                                <input type="number" class="form-control" name="uang_jalan[]" required id="uang_jalan"
                                    required aria-describedby="helpId" placeholder="" value="{{$v->uang_jalan}}" @if(auth()->user()->role !== 'admin')
                                    readonly
                                @endif >
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            </div>
            <div class="row justify-content-center text-center">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-block btn-primary">Simpan & Selesai</button>
                    <a href="{{ route('vendor.index') }}" class="btn btn-block btn-danger">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
