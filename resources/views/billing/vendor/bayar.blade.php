@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Bayar dari Vendor</u></h1>
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
    <form action="{{route('billing.vendor.bayar-store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-4">
                <div class="mb-3">
                  <label for="" class="form-label">Tanggal</label>
                  <input type="text"
                    class="form-control" name="" id="" aria-describedby="helpId" placeholder="" value="{{date('d-m-Y')}}" disabled>
                </div>
            </div>
            <div class="col-4">
                <div class="mb-3">
                    <label for="vendor_id" class="form-label">Vendor</label>
                    <select class="form-select" name="vendor_id" id="vendor_id" onchange="funGetKas()" required>
                        <option selected> -- Pilih Vendor -- </option>
                        @foreach ($vendor as $d)
                            <option value="{{$d->id}}">{{$d->nama}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="nilai" class="form-label">Nilai Tagihan</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nilai'))
                    is-invalid
                @endif" name="nilai" id="nilai" data-thousands="." required>
                <input type="hidden" name="nominal" id="nominal">
                  </div>
            </div>
            <div class="col-12 mb-3">
                <div class="mb-3">
                  <label for="uraian" class="form-label">Uraian</label>
                  <input type="text"
                    class="form-control" name="uraian" id="uraian" aria-describedby="helpId" placeholder="" maxlength="20">
                </div>
            </div>
        </div>
        <hr>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-primary" hidden id="ok">Ok</button>
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
             $('#nilai').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0
            });
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

        function funGetKas() {
            var vendor_id = $('#vendor_id').val();
            $.ajax({
                url: "{{route('billing.vendor.get-kas-vendor')}}",
                type: "GET",
                data: {
                    vendor_id: vendor_id
                },
                success: function(data){
                    if (data > 0) {
                        // swal tidak ada tagihan

                        $('#ok').removeAttr('hidden');
                        $('#nilai').maskMoney('mask', data);
                        $('#nominal').val(data);


                    }
                    else if(data <= 0){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Tidak ada Pembayaran yang harus dilunasi!',
                        });
                        $('#ok').attr('hidden', true);
                        $('#nilai').val('');
                        $('#nominal').val('');
                    }
                }
            });
        }

    </script>
@endpush
