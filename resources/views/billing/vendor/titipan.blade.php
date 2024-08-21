@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Titipan Vendor</u></h1>
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
    <form action="{{route('billing.vendor.titipan-store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-3">
                <div class="mb-3">
                  <label for="" class="form-label">Tanggal</label>
                  <input type="text"
                    class="form-control" name="" id="" aria-describedby="helpId" placeholder="" value="{{date('d-m-Y')}}" disabled>
                </div>
            </div>
            <div class="col-3">
                <div class="mb-3">
                    <label for="id" class="form-label">Nama Vendor</label>
                    <select class="form-select" name="id" id="id" onchange="funGetVendor()" required>
                        <option selected> -- Pilih Vendor -- </option>
                        @foreach ($vendor as $d)
                            <option value="{{$d->id}}">{{$d->nama}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-3">
                <div class="mb-3">
                  <label for="vendor" class="form-label">Nomor Lambung</label>
                  <input type="text"
                    class="form-control" name="nomor_lambung" id="nomor_lambung" aria-describedby="helpId" placeholder="" disabled>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="nilai" class="form-label">Nilai</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nilai'))
                    is-invalid
                @endif" name="nilai" id="nilai" data-thousands="." required>
                  </div>
            </div>
            <div class="col-3">
                <div class="mb-3">
                  <label for="vendor" class="form-label">Uraian</label>
                  <input type="text"
                    class="form-control" name="uraian" id="uraian" aria-describedby="helpId" placeholder="" required>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="transfer_ke" class="form-label">Nama Rekening</label>
                <input type="text" class="form-control @if ($errors->has('transfer_ke'))
                is-invalid
            @endif" name="transfer_ke" id="transfer_ke" value="{{old('transfer_ke')}}" maxlength="15">
                @if ($errors->has('transfer_ke'))
                <div class="invalid-feedback">
                    {{$errors->first('transfer_ke')}}
                </div>
                @endif
            </div>
            <div class="col-md-3 mb-3">
                <label for="bank" class="form-label">Bank</label>
                <input type="text" class="form-control @if ($errors->has('bank'))
                is-invalid
            @endif" name="bank" id="bank" value="{{old('bank')}}" maxlength="10">
                @if ($errors->has('bank'))
                <div class="invalid-feedback">
                    {{$errors->first('bank')}}
                </div>
                @endif
            </div>
            <div class="col-md-3 mb-3">
                <label for="no_rekening" class="form-label">Nomor Rekening</label>
                <input type="text" class="form-control @if ($errors->has('no_rekening'))
                is-invalid
            @endif" name="no_rekening" id="no_rekening" value="{{old('no_rekening')}}">
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
                url: "{{route('billing.vendor.get-vehicle')}}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(data){
                    funGetPlafonTitipan();
                    $('#nomor_lambung').val(data);
                    // get text from selected option
                    var text = $("#id option:selected").text();
                    // set value uraian to Titipan + text
                    var id_vendor = $('#id').val();

                    $('#uraian').val('Titipan ' + text);

                    // set value transfer_ke, bank, no_rekening to $vendor->nama_rekening, $vendor->no_rekening and $vendor->bank selected from select id
                    var vendor = {!! json_encode($vendor) !!};
                    for (let i = 0; i < vendor.length; i++) {
                        if (vendor[i].id == id_vendor) {
                            $('#transfer_ke').val(vendor[i].nama_rekening);
                            $('#bank').val(vendor[i].bank);
                            $('#no_rekening').val(vendor[i].no_rekening);
                        }
                    }

                }
            });
        }

        function funGetPlafonTitipan() {

            var id = $('#id').val();
            $.ajax({
                url: "{{route('billing.vendor.get-plafon-titipan')}}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(data){
                    console.log(data);
                    if (data > 0) {
                        $('#nilai').val(data);
                        $('#nilai').maskMoney('mask', data);
                    } else {
                        // swal sisa saldo melebihi plafon
                        $('#nilai').val('');
                        // destory maskMoney

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Sisa saldo melebihi plafon!',
                        });
                    }

                }
            });
        }

    </script>
@endpush
