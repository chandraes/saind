@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Kesepakatan Uang Jalan Vendor</u></h1>
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
    <form action="{{route('uj.vendor.uang-jalan.update', $data->id)}}" method="post">
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
                                <input type="hidden" name="vendor_id" value="{{$data->id}}">
                                <input type="hidden" name="rute_id[]" value="{{$v->id}}">
                                <input type="text" class="form-control" name="uang_jalan[]" required id="uang_jalan-{{$v->id}}"
                                    required aria-describedby="helpId" placeholder="" @if(auth()->user()->role !== 'admin')
                                    readonly
                                @endif data-thousands="." >
                            </td>
                        </tr>
                        <script>
                            $('#uang_jalan-{{$v->id}}').maskMoney({
                                    thousands: '.',
                                    decimal: ',',
                                    precision: 0
                                });

                            $('#uang_jalan-{{$v->id}}').maskMoney('mask', {{$v->uang_jalan}});

                        </script>
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
