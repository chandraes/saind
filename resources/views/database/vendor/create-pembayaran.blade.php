@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>Pembayaran ke Vendor</u></h1>
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
    <form action="{{route('vendor.pembayaran.store')}}" method="post">
        @csrf
        <div class="row mt-3 mb-3">
            <div class="row">
                @foreach ($customers as $v)
                <div class="col-md-12">
                    <h3>{{$v->nama}}</h3>
                </div>
                <table class="table table-bordered table-hover">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center align-middle">Rute</th>
                            <th class="text-center align-middle">Harga Kesepakatan Opname</th>
                            <th class="text-center align-middle">Harga Kesepakatan Titipan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($v->rute as $i)
                        <input type="hidden" name="vendor_id" value="{{$id}}">
                        <input type="hidden" name="customer_id[]" value="{{$v->id}}">
                        <input type="hidden" name="rute_id[]" value="{{$i->id}}">
                        <tr>
                            <td class="text-center align-middle">
                                {{$i->nama}}
                            </td>
                            <td class="text-center align-middle">
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" name="hk_opname[]"
                                        id="hk_opname" required aria-describedby="helpId" placeholder="" @if (auth()->user()->role !== 'admin')
                                        readonly
                                    @endif >
                                </div>
                            </td>
                            <td class="text-center align-middle">
                                <div class="input-group">
                                    <span class="input-group-text">Rp.</span>
                                    <input type="number" class="form-control" name="hk_titipan[]"
                                        id="hk_titipan" required aria-describedby="helpId" placeholder="" @if (auth()->user()->role !== 'admin')
                                        readonly
                                    @endif>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- @foreach ($v->rute as $i)
                <input type="hidden" name="vendor_id" value="{{$id}}">
                <input type="hidden" name="customer_id[]" value="{{$v->id}}">
                <input type="hidden" name="rute_id[]" value="{{$i->id}}">

                <div class="col-md-4 mb-3 mt-3" id="rute-{{$v->id}}">
                       <div class="mb-3">
                         <label for="rute" class="form-label">Rute</label>
                         <input type="text"
                           class="form-control" name="rute" id="rute" aria-describedby="helpId" placeholder="" value="{{$i->nama}}">
                       </div>
                </div>
                <div class="col-md-4 mb-3 mt-3" id="opname-{{$v->id}}">
                    <label for="hk_opname" class="form-label">Harga Kesepakatan OPNAME</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp.</span>
                        <input type="number" class="form-control" name="hk_opname[]"
                            id="hk_opname" required aria-describedby="helpId" placeholder="" @if (auth()->user()->role !== 'admin')
                            readonly
                        @endif >
                    </div>
                </div>
                <div class="col-md-4 mb-3 mt-3" id='titipan-{{$v->id}}'>
                    <label for="hk_titipan" class="form-label">Harga Kesepakatan Titipan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp.</span>
                        <input type="number" class="form-control" name="hk_titipan[]"
                            id="hk_titipan" required aria-describedby="helpId" placeholder="" @if (auth()->user()->role !== 'admin')
                            readonly
                        @endif>
                    </div>
                </div>

                @endforeach --}}
                <br>
                <hr>
                @endforeach
            </div>
            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-block btn-primary">Simpan & Lanjutkan</button>
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

    <script>
        // select2 to pembayaran
        $(document).ready(function() {
            $('#pembayaran').select2();

        });

        $('#pembayaran').on('select2:select', function(e) {
            var data = e.params.data.id;
            var customer = {!! $customers !!};
            if (data == 'opname') {
                for(var i = 0; i < customer.length; i++){
                    // $('#opname-'+customer[i].id).show();
                    // remove hidden attribute
                    $('#opname-'+customer[i].id).removeAttr('hidden');
                    $('#opname-'+customer[i].id).show();
                    // clear titipan value
                    $('#titipan-'+customer[i].id).val('');
                    $('#titipan-'+customer[i].id).hide();
                }
            } else if(data == 'titipan'){
                for(var i = 0; i < customer.length; i++){
                    // $('#opname-'+customer[i].id).show();
                    // remove hidden attribute
                    $('#titipan-'+customer[i].id).removeAttr('hidden');
                    $('#titipan-'+customer[i].id).show();
                    // clear opname value
                    $('#opname-'+customer[i].id).val('');
                    $('#opname-'+customer[i].id).hide();
                }
            }
        });

    </script>
@endpush
