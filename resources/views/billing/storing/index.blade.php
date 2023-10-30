@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Storing</u></h1>
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
    <form action="{{route('billing.storing.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-4">
                <div class="mb-3">
                    <label for="id" class="form-label">Nomor Lambung</label>
                    <select class="form-select" name="id" id="id" onchange="funGetVendor()" required>
                        <option selected> -- Pilih Nomor Lambung -- </option>
                        @foreach ($vehicle as $d)
                            <option value="{{$d->id}}">{{$d->nomor_lambung}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-4">
                <div class="mb-3">
                  <label for="vendor" class="form-label">Nama Vendor</label>
                  <input type="text"
                    class="form-control" name="vendor" id="vendor" aria-describedby="helpId" placeholder="" disabled>
                    <input type="hidden" name="vendor_id" id="vendor_id" required>
                </div>
            </div>
            <div class="col-4">
                <div class="mb-3">
                    <label for="storing_id" class="form-label">Pilih Lokasi</label>
                    <select class="form-select" name="storing_id" id="storing_id" onchange="funGetStoring()">
                        <option selected> -- Pilih Lokasi-- </option>
                        @foreach ($storing as $d)
                            <option value="{{$d->id}}">{{$d->km}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">

        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="mekanik" class="form-label">Mekanik</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('mekanik'))
                    is-invalid
                @endif" name="mekanik" id="mekanik" data-thousands="." disabled>
                  </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="harga_vendor" class="form-label">Vendor</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('harga_vendor'))
                    is-invalid
                @endif" name="harga_vendor" id="harga_vendor" data-thousands="." disabled>
                  </div>
            </div>
            <div class="col-md-4 mb-3" id="jasadiv">
                <label for="jasa" class="form-label">Jasa</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('jasa'))
                    is-invalid
                @endif" name="jasa" id="jasa" data-thousands=".">
                  </div>
            </div>
        </div>
        <hr>
        <h2>Transfer Ke</h2>
        <br>
        <div class="row">

            <div class="col-md-4 mb-3">
                <label for="transfer_ke" class="form-label">Nama</label>
                <input type="text" class="form-control @if ($errors->has('transfer_ke'))
                    is-invalid
                @endif" name="transfer_ke" id="transfer_ke" disabled value="{{$rekening->nama_rekening}}">
                @if ($errors->has('transfer_ke'))
                <div class="invalid-feedback">
                    {{$errors->first('transfer_ke')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="bank" class="form-label">Bank</label>
                <input type="text" class="form-control @if ($errors->has('bank'))
                    is-invalid
                @endif" name="bank" id="bank" disabled value="{{$rekening->nama_bank}}">
                @if ($errors->has('bank'))
                <div class="invalid-feedback">
                    {{$errors->first('bank')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="no_rekening" class="form-label">Nomor Rekening</label>
                <input type="text" class="form-control @if ($errors->has('no_rekening'))
                    is-invalid
                @endif" name="no_rekening" id="no_rekening" disabled value="{{$rekening->nomor_rekening}}">
                @if ($errors->has('no_rekening'))
                <div class="invalid-feedback">
                    {{$errors->first('no_rekening')}}
                </div>
                @endif
            </div>
        </div>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-primary">Ok</button>
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
             $('#jasa').maskMoney({
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
                    funGetStatusSo();
                    $('#vendor_id').val(data.id);
                    $('#vendor').val(data.nama);
                }
            });
        }

        function funGetStoring() {
            var id = $('#storing_id').val();

            $.ajax({
                url: "{{route('billing.storing.get-storing')}}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(data){


                    $('#mekanik').maskMoney('destroy');
                    $('#mekanik').maskMoney({
                        thousands: '.',
                        decimal: ',',
                        precision: 0
                    });
                    $('#mekanik').maskMoney('mask', (data.biaya_mekanik));
                    $('#harga_vendor').maskMoney('destroy');
                    $('#harga_vendor').maskMoney({
                        thousands: '.',
                        decimal: ',',
                        precision: 0
                    });
                    $('#harga_vendor').maskMoney('mask', (data.biaya_vendor));

                    // call funGetStatusSo

                }
            });
        }

        function funGetStatusSo()
        {
            var id = $('#id').val();

            $.ajax({
                url: "{{route('billing.storing.get-status-so')}}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(data){
                    console.log(data);
                    // if 1 disable jasa and hide jasadiv
                    if (data == 1) {

                        $('#jasa').prop('disabled', true);
                        // make jasa not required
                        $('#jasa').prop('required', false);
                        $('#jasadiv').hide();

                    } else if(data == 0) {
                        $('#jasa').prop('disabled', false);
                        // make jasa required
                        $('#jasa').prop('required', true);
                        $('#jasadiv').show();
                    }
                }
            });
        }

    </script>
@endpush
