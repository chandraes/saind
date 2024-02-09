@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>By Pass Kas Besar</u></h1>
        </div>
    </div>
    @include('swal')
    <form action="{{route('bypass-kas-besar.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="mb-3">
                    <label for="tipe" class="form-label">Tipe</label>
                    <select class="form-select" name="tipe" id="tipe" required>
                        <option>-- Pilih tipe masuk dana --</option>
                        <option value="1">Uang Masuk</option>
                        <option value="2">Uang Keluar</option>
                    </select>
                </div>
            </div>
            <div class="col-4 mb-3">
                <label for="uraian" class="form-label">Uraian</label>
                <input type="text" class="form-control @if ($errors->has('uraian'))
                    is-invalid
                @endif" name="uraian" id="uraian" required value="{{old('uraian')}}">
            </div>
            <div class="col-md-4 mb-3">
                <label for="nominal" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nominal'))
                    is-invalid
                @endif" name="nominal" id="nominal" required data-thousands=".">
                </div>
                @if ($errors->has('nominal'))
                <div class="invalid-feedback">
                    {{$errors->first('nominal')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{route('home')}}" class="btn btn-secondary" type="button">Batal</a>
        </div>
    </form>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>
            $('#nominal').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true,
            });

            $('#direksi_id').select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Vendor',
                allowClear: true,
            });

        // masukForm on submit, sweetalert confirm
        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
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
</script>
@endpush
