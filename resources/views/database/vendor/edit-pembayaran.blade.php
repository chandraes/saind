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
    <form action="{{route('vendor.pembayaran.update', $data->id)}}" method="post" id="masukForm">
        @csrf
        <div class="row mt-3 mb-3">
            <div class="row">
                <div class="mb-3">

                  <label for="pembayaran" class="form-label">Pembayaran</label>
                  <select class="form-select" name="pembayaran" id="pembayaran">
                        <option>-- Pilih Metode --</option>
                        <option value="opname" {{$data->pembayaran == 'opname' ? 'selected' : ''}}>Opname</option>
                        <option value="titipan" {{$data->pembayaran == 'titipan' ? 'selected' : ''}}>Titipan</option>
                    </select>
                </div>
                @foreach ($customers as $v)
                <div class="col-md-12">
                    <h3>{{$v->nama}}</h3>
                </div>
                <input type="hidden" name="vendor_id" value="{{$data->id}}">
                <input type="hidden" name="customer_id[]" value="{{$v->id}}">
                <div class="col-md-3 mb-3 mt-3" id="opname-{{$v->id}}" {{$data->pembayaran == 'opname' ? '' : 'hidden'}}>
                    <label for="hk_opname" class="form-label">Harga Kesepakatan OPNAME</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp.</span>
                        <input type="number" class="form-control" name="hk_opname[]"
                            id="hk_opname-{{$v->id}}" {{$data->pembayaran == 'opname' ? 'required' : ''}} aria-describedby="helpId" placeholder="" value="{{$data->vendor_bayar->where('customer_id', $v->id)->first() && $data->pembayaran == 'opname' ? $data->vendor_bayar->where('customer_id', $v->id)->first()->harga_kesepakatan : ''}}"
                            @if (auth()->user()->role !== 'admin')
                            readonly
                            @endif>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mt-3" id='titipan-{{$v->id}}' {{$data->pembayaran == 'titipan' ? '' : 'hidden'}}>
                    <label for="hk_titipan" class="form-label">Harga Kesepakatan Titipan</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp.</span>
                        <input type="number" class="form-control" name="hk_titipan[]"
                            id="hk_titipan-{{$v->id}}" {{$data->vendor_bayar->where('customer_id', $v->id)->where('pembayaran', 'opname')->first() ? 'titipan' : ''}} aria-describedby="helpId" placeholder="" value="{{$data->vendor_bayar->where('customer_id', $v->id)->first() && $data->pembayaran == 'titipan' ? $data->vendor_bayar->where('customer_id', $v->id)->first()->harga_kesepakatan : ''}}" @if (auth()->user()->role !== 'admin')
                            readonly
                        @endif>
                    </div>
                </div>
                <br>
                <hr>
                @endforeach
            </div>
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

    <script>
        // select2 to pembayaran
        $(document).ready(function() {
            $('#pembayaran').select2();

        });

        $('#pembayaran').on('select2:select', function(e) {
                var data = e.params.data.id;
                console.log(data);
                var customer = {!! $customers !!};
                if (data == 'opname') {
                    for(var i = 0; i < customer.length; i++){
                        // $('#opname-'+customer[i].id).show();
                        // remove hidden attribute
                        $('#opname-'+customer[i].id).removeAttr('hidden');
                        $('#hk_opname-'+customer[i].id).val(customer[i].harga_opname);
                        $('#hk_titipan-'+customer[i].id).val('');
                        $('#opname-'+customer[i].id).show();
                        $('#titipan-'+customer[i].id).hide();
                        // set value to customer[i].harga_opname

                    }
                } else if(data == 'titipan'){
                    for(var i = 0; i < customer.length; i++){
                        console.log(customer[i].harga_titipan);
                        // $('#opname-'+customer[i].id).show();
                        // remove hidden attribute
                        $('#titipan-'+customer[i].id).removeAttr('hidden');
                        $('#titipan-'+customer[i].id).show();
                        $('#hk_titipan-'+customer[i].id).val(customer[i].harga_titipan);
                        $('#hk_opname-'+customer[i].id).val('');
                        $('#opname-'+customer[i].id).hide();
                        // set value to customer[i].harga_titipan

                    }
                }
            });
            $('#masukForm').submit(function(e){
                e.preventDefault();
                Swal.fire({
                    title: 'Apakah Data yang anda masukan sudah benar?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, simpan!'
                    }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                })
            });

    </script>
@endpush
