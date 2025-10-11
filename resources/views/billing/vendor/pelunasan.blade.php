@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Pelunasan Vendor</u></h1>
        </div>
    </div>
    @include('swal')
    {{-- show error first --}}
    @php
    $role = Auth::user()->role;
    $roles = ['admin', 'su'];

    @endphp
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
    <form action="{{route('billing.vendor.pelunasan-store')}}" method="post" id="masukForm">
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
                    @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
                    <input type="text" class="form-control" name="nilai" id="nilai" onkeyup="changeNominal()" required>
                    @else
                    <input type="text" class="form-control" name="nilai" id="nilai" data-thousands="." disabled>
                    @endif

                <input type="hidden" name="nominal" id="nominal">
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
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>

<script>

$('#vendor_id').select2({
    theme: 'bootstrap-5',
    placeholder: '-- Pilih Vendor --'
});

var nominal = new Cleave('#nilai', {
    numeral: true,
    numeralThousandsGroupStyle: 'thousand',
    numeralDecimalMark: ',',
    delimiter: '.'
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

        function funGetKas() {
            var vendor_id = $('#vendor_id').val();
            var role = "{!! $role !!}";

            $.ajax({
                url: "{{route('billing.vendor.get-kas-vendor')}}",
                type: "GET",
                data: {
                    vendor_id: vendor_id
                },
                success: function(data){
                    if (data >= 0) {
                        // swal tidak ada tagihana

                    if(role != 'admin' && role != 'su'){
                            console.log('masuk sini');
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Tidak ada tagihan yang harus dilunasi!',
                            });
                            $('#ok').attr('hidden', true);
                            $('#nilai').val('');
                            $('#nominal').val('');
                        } else {
                            console.log('masuk');
                            $('#ok').removeAttr('hidden');
                        }
                    }
                    else if(data < 0){
                        // mask money
                        // show button ok
                        $('#ok').removeAttr('hidden');
                        var nilai = parseInt(data) * -1;
                        // make data to local String id-ID
                        nilai = nilai.toLocaleString('id-ID');

                        $('#nilai').val(nilai);

                        $('#nominal').val(data);
                    }
                }
            });
        }

        function changeNominal() {
            var nominal = $('#nilai').val();
            var nominal = nominal.replace(/\./g, '');
            var nominal = parseInt(nominal) * -1;
            $('#nominal').val(nominal);
        }

    </script>
@endpush
