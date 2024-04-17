@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Barang Maintenance</u></h1>
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
    <form action="{{route('billing.form-maintenance.jual-vendor-store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="id" class="form-label">Nomor Lambung</label>
                    <select class="form-select" name="vehicle_id" id="id" onchange="funGetVendor()">
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
                    class="form-control" name="vendor" id="vendor" aria-describedby="helpId" placeholder="" readonly>
                    <input type="hidden" name="vendor_id" id="vendor_id" required>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6">
                <div class="mb-3">
                    <label for="barang_maintenance_id" class="form-label">Nama Barang</label>
                    <select class="form-select" name="barang_maintenance_id" id="barang_maintenance_id" onchange="getHargaJual()">
                        <option value=""> -- Pilih barang -- </option>
                        @foreach ($kategori as $b)
                            <option value="{{$b->id}}">{{$b->nama}} ({{$b->stok}})</option>
                        @endforeach
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

    <script>


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

        function funGetVendor() {
            var id = $('#id').val();
            $.ajax({
                url: "{{route('billing.storing.get-vendor')}}",
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
            var barang_maintenance_id = $('#barang_maintenance_id').val();
            $.ajax({
                url: "{{route('billing.form-maintenance.get-harga-jual')}}",
                type: "GET",
                data: {
                    barang_maintenance_id: barang_maintenance_id
                },
                success: function(data){
                    // maskMoney
                    harga = data.harga_jual.toLocaleString('id-ID');
                    $('#harga_jual').val(harga);

                    // $('#harga_jual').val((data.harga_jual));
                }
            });
        }

        function getTotal() {
            var jumlah = $('#jumlah').val();
            var harga_jual = $('#harga_jual').val();
            // remove . from harga_jual
            harga_jual = harga_jual.replace(/\./g,'');
            var total = jumlah * harga_jual;

            $('#total').val(total.toLocaleString('id-ID'));

        }

    </script>
@endpush
