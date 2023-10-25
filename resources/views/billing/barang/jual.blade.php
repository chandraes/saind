@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Jual Barang</u></h1>
        </div>
    </div>
    @include('swal')
    {{-- show error first --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Whoops!</strong> Terjadi kesalahan.<br><br>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </ul>
        </div>
    @endif
    <form action="{{route('billing.form-barang.jual-store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="id" class="form-label">Nomor Lambung</label>
                    <select class="form-select" name="id" id="id" onchange="funGetVendor()">
                        <option selected> -- Pilih Nomor Lambung -- </option>
                        @foreach ($vehicle as $d)
                            <option value="{{$d->id}}">{{$d->nomor_lambung}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-6">
                <div class="mb-3">
                  <label for="vendor" class="form-label">Nama Vendor</label>
                  <input type="text"
                    class="form-control" name="vendor" id="vendor" aria-describedby="helpId" placeholder="" disabled>
                    <input type="hidden" name="vendor_id" id="vendor_id" required>
                </div>
            </div>
        </div>
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
                    <select class="form-select" name="barang_id" id="barang_id" onchange="getHargaJual()">
                        <option value=""> -- Pilih kategori barang -- </option>
                    </select>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-4">
                <div class="mb-3">
                  <label for="jumlah" class="form-label">Jumlah</label>
                  <input type="number"
                    class="form-control" name="jumlah" id="jumlah" aria-describedby="helpId" placeholder="" required oninput="getTotal()">
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="harga_jual" class="form-label">Harga Satuan</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('harga_jual'))
                    is-invalid
                @endif" name="harga_jual" id="harga_jual" data-thousands="." disabled>
                  </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="total" class="form-label">Total</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('total'))
                    is-invalid
                @endif" name="total" id="total" data-thousands="." disabled>
                  </div>
            </div>
        </div>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-primary">Jual</button>
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
             $('#harga_satuan').maskMoney();
        });

        $('#masukForm').submit(function(e){
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
                    this.submit();
                }
            })
        });

        function funGetVendor() {
            var id = $('#id').val();
            $.ajax({
                url: "{{route('kas-uang-jalan.get-vendor')}}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(data){
                    console.log(data.id);
                    $('#vendor_id').val(data.id);
                    $('#vendor').val(data.nama);
                }
            });
        }

        function getHargaJual() {
            var barang_id = $('#barang_id').val();
            $.ajax({
                url: "{{route('billing.form-barang.get-harga-jual')}}",
                type: "GET",
                data: {
                    barang_id: barang_id
                },
                success: function(data){
                    console.log(data);
                    // maskMoney
                    $('#harga_jual').maskMoney('destroy');
                    $('#harga_jual').maskMoney();
                    $('#harga_jual').maskMoney('mask', (data.harga_jual));

                    // $('#harga_jual').val((data.harga_jual));
                }
            });
        }

        function getTotal() {
            var jumlah = $('#jumlah').val();
            var harga_jual = $('#harga_jual').val();
            // remove . from harga_jual
            harga_jual = harga_jual.replace(/\./g,'');
            console.log(harga_jual);
            console.log(jumlah);
            var total = jumlah * harga_jual;
            console.log(total);
            $('#total').maskMoney('destroy');
            $('#total').maskMoney();
            $('#total').maskMoney('mask', (total));
        }

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
                        $('#barang_id').append('<option value="'+value.id+'">'+value.nama+' ('+value.stok+')'+'</option>');
                    });
                }
            });
        }

    </script>
@endpush
