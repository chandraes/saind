@extends('layouts.app')
@section('content')
@php
    $bulan = [
        '1' => 'Januari',
        '2' => 'Februari',
        '3' => 'Maret',
        '4' => 'April',
        '5' => 'Mei',
        '6' => 'Juni',
        '7' => 'Juli',
        '8' => 'Agustus',
        '9' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember',
    ];
@endphp
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Kasbon</u></h1>
            <h1>Cicilan</h1>
        </div>
    </div>
    @include('swal')
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
    <form action="{{route('billing.kasbon.kas-bon-cicil-store')}}" method="post" id="masukForm">
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
                    <label for="karyawan_id" class="form-label">Nama Karyawan</label>
                    <select class="form-select" name="karyawan_id" id="karyawan_id" required>
                        <option selected> -- Pilih Karyawan -- </option>
                        @foreach ($karyawan as $d)
                            <option value="{{$d->id}}">{{$d->nama}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-4 mb-3">
                <label for="nominal" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nominal'))
                    is-invalid
                @endif" name="nominal" id="nominal" data-thousands="." required>
                  </div>
            </div>
        </div>
        <div class="row">
            <div class="col-2">
                <div class="mb-3">
                  <label for="cicil_kali" class="form-label">Berapa Kali Cicil</label>
                  <input type="number"
                    class="form-control" name="cicil_kali" id="cicil_kali" aria-describedby="helpId" placeholder="" required>
                </div>
            </div>
            <div class="col-2">
                <label for="mulai_bulan" class="form-label">Mulai Bulan</label>
                <select class="form-select" name="mulai_bulan" id="mulai_bulan" required></select>
            </div>
            <div class="col-2">
                <div class="mb-3">
                  <label for="mulai_tahun" class="form-label">Mulai Tahun</label>
                  <input type="text"
                    class="form-control" name="mulai_tahun" id="mulai_tahun" aria-describedby="helpId" placeholder="" required minlength="4" maxlength="4" value="{{date('Y')}}" onchange="tahunInput()">
                </div>
            </div>
        </div>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-primary" type="submit">Ok</button>
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
            var nominal = new Cleave('#nominal', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
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

        // document ready tahunInput()
        $(document).ready(function() {
            tahunInput();
        });

        function tahunInput() {
            var tahun = $('#mulai_tahun').val();
            console.log(tahun);
            if (tahun > {{date('Y')}}) {
                $('#mulai_bulan').empty();
                $('#mulai_bulan').append('<option value=""> -- Pilih Bulan -- </option>');
                for (let i = 1; i <= 12; i++) {
                    $('#mulai_bulan').append('<option value="'+i+'">'+@json($bulan) [i]+'</option>');
                }
            } else if(tahun == {{date('Y')}}) {
                $('#mulai_bulan').empty();
                $('#mulai_bulan').append('<option value=""> -- Pilih Bulan -- </option>');
                for (let i = {{date('m')}}; i <= 12; i++) {
                    $('#mulai_bulan').append('<option value="'+i+'">'+@json($bulan) [i]+'</option>');
                }
            } else {
                $('#mulai_bulan').empty();
                $('#mulai_bulan').append('<option value=""> -- Pilih Bulan -- </option>');
            }
        }


    </script>
@endpush
