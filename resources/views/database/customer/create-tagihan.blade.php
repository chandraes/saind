@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Tagihan Customer</u></h1>
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
    <form action="{{route('customer.tagihan-store', $data)}}" method="post">
        @csrf
        <div class="row mt-3 mb-3">
            <table class="table table-hover table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th class="text-center align-middle">Rute</th>
                        <th class="text-center align-middle">Harga Tagihan ke Tambang</th>
                        <th class="text-center align-middle">Harga Vendor Opname</th>
                        <th class="text-center align-middle">Harga Vendor Titipan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data->rute as $i)
                    <tr>
                        <td class="text-center align-middle">
                            <input type="hidden" class="form-control" name="rute_id[]" id="rute" aria-describedby="helpId" placeholder="" value="{{$i->id}}" required>
                            {{$i->nama}}
                        </td>
                        <td class="text-center align-middle">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control @if ($errors->has('harga_tagihan'))
                                is-invalid
                            @endif" name="harga_tagihan[]" id="harga_tagihan-{{$i->id}}" required data-thousands="." required>
                              </div>
                            @if ($errors->has('harga_tagihan'))
                            <div class="invalid-feedback">
                                {{$errors->first('harga_tagihan')}}
                            </div>
                            @endif
                            <script>
                                $(function() {
                                        $('#harga_tagihan-{{$i->id}}').maskMoney();
                                });
                            </script>
                        </td>
                        <td class="text-center align-middle">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control @if ($errors->has('titipan'))
                                is-invalid
                            @endif" name="opname[]" id="opname-{{$i->id}}" required data-thousands="." required>
                              </div>
                            @if ($errors->has('opname'))
                            <div class="invalid-feedback">
                                {{$errors->first('opname')}}
                            </div>
                            @endif
                            <script>
                                $(function() {
                                        $('#opname-{{$i->id}}').maskMoney();
                                });
                            </script>
                        </td>
                        <td class="text-center align-middle">
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control @if ($errors->has('titipan'))
                                is-invalid
                            @endif" name="titipan[]" id="titipan-{{$i->id}}" required data-thousands="." required>
                              </div>
                            @if ($errors->has('titipan'))
                            <div class="invalid-feedback">
                                {{$errors->first('titipan')}}
                            </div>
                            @endif
                            <script>
                                $(function() {
                                        $('#titipan-{{$i->id}}').maskMoney();
                                });
                            </script>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- @foreach ($data->rute as $i)
            <div class="col-6 mb-3">
              <label for="rute" class="form-label">Rute</label>
              <input type="hidden"
                class="form-control" name="rute_id[]" id="rute" aria-describedby="helpId" placeholder="" value="{{$i->id}}" required>
                <input type="text"
                class="form-control" name="" id="rute" aria-describedby="helpId" placeholder="" value="{{$i->nama}}" disabled>
            </div>
            <div class="col-md-6 mb-3">
                <label for="harga_tagihan" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('harga_tagihan'))
                    is-invalid
                @endif" name="harga_tagihan[]" id="harga_tagihan-{{$i->id}}" required data-thousands=".">
                  </div>
                @if ($errors->has('harga_tagihan'))
                <div class="invalid-feedback">
                    {{$errors->first('harga_tagihan')}}
                </div>
                @endif
            </div>
            <script>
                $(function() {
                        $('#harga_tagihan-{{$i->id}}').maskMoney();
                });
            </script>
            @endforeach --}}
            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-block btn-primary">Simpan</button>
                    <a href="{{ route('vendor.index') }}" class="btn btn-block btn-danger">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@push('js')
    {{-- import select2 cdn --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- select2 to pembayaran --}}
    <script src="{{asset('assets/js/jquery.maskMoney.js')}}"></script>
    <script>
        // select2 to pembayaran
        $(document).ready(function() {
            $('#pembayaran').select2();

        });

    </script>
@endpush
