@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Beli Barang</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-3 mb-3">
        <div class="col-5">
            <table>
                <tr>
                    <td>
                        <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#keranjangBelanja" >
                            <i class="fa fa-shopping-cart"> Keranjang </i> ({{$keranjang->count()}})
                        </a>
                        @include('billing.barang.keranjang')
                    </td>
                    <td>
                        <form action="{{route('billing.form-barang.keranjang-empty')}}" method="get" id="kosongKeranjang">
                            <button class="btn btn-danger" type="submit">
                                <i class="fa fa-trash"> Kosongkan Keranjang </i>
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @include('swal')
    <form action="{{route('billing.form-barang.keranjang-store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="kategori_barang_id" class="form-label">Kategori Barang</label>
                    <select class="form-select" name="kategori_barang_id" id="kategori_barang_id" onchange="funGetBarang()">
                        <option value=""> -- Pilih kategori barang -- </option>
                        @foreach ($kategori as $k)
                            <option value="{{$k->id}}">{{$k->nama}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                    <label for="barang_id" class="form-label">Nama Barang</label>
                    <select class="form-select" name="barang_id" id="barang_id">
                        <option value=""> -- Pilih kategori barang -- </option>
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                  <label for="jumlah" class="form-label">Jumlah</label>
                  <input type="number"
                    class="form-control" name="jumlah" id="jumlah" aria-describedby="helpId" placeholder="" required>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="harga_satuan" class="form-label">Harga Satuan</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('harga_satuan'))
                    is-invalid
                @endif" name="harga_satuan" id="harga_satuan" data-thousands="." required>
                  </div>
            </div>
        </div>
        <hr>

        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-primary">Masukan Keranjang</button>
            <a href="{{route('billing.index')}}" class="btn btn-secondary" type="button">Batal</a>
          </div>
    </form>
</div>
@endsection
@push('js')
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
    <script src="{{asset('assets/js/jquery.maskMoney.js')}}"></script>
    <script>
        $(function() {
             $('#harga_satuan').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true,
            });
        });

        $('#kosongKeranjang').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        $('#beliBarang').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        // funGetBarang
        function funGetBarang() {
            var kategori_barang_id = $('#kategori_barang_id').val();
            $.ajax({
                url: "{{route('billing.form-barang.get-barang')}}",
                type: "GET",
                data: {
                    kategori_barang_id: kategori_barang_id
                },
                success: function(data){
                    console.log(data);
                    $('#barang_id').empty();
                    $('#barang_id').append('<option value=""> -- Pilih kategori barang -- </option>');
                    $.each(data, function(index, value){
                        $('#barang_id').append('<option value="'+value.id+'">'+value.nama+'</option>');
                    });
                }
            });
        }

    </script>
@endpush
