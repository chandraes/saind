@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Kas Kecil (Keluar)</u></h1>
        </div>
    </div>
    @include('swal')
    <form action="{{route('kas-kecil.keluar.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-4 mb-3">
                <label for="uraian" class="form-label">Uraian</label>
                <input type="text" class="form-control @if ($errors->has('uraian'))
                    is-invalid
                @endif" name="uraian" id="uraian" required value="{{old('uraian')}}" maxlength="20">
            </div>
            <div class="col-md-4 mb-3">
                <label for="nominal_transaksi" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nominal_transaksi'))
                    is-invalid
                @endif" name="nominal_transaksi" id="nominal_transaksi" required data-thousands=".">
                </div>
                @if ($errors->has('nominal_transaksi'))
                <div class="invalid-feedback">
                    {{$errors->first('nominal_transaksi')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <div class="mb-3">
                    <label for="tipe" class="form-label">Sistem Pembayaran</label>
                    <select class="form-select" name="tipe" id="tipe" onchange="tipeFun()" required>
                        <option>-- Pilih transfer / cash --</option>
                        <option value="1">Cash</option>
                        <option value="2">Transfer</option>
                    </select>
                </div>
            </div>
        </div>
        <hr>
        <div class="" id="fieldTransfer" hidden>
            <h3>Transfer Ke</h3>
            <br>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="transfer_ke" class="form-label">Nama</label>
                    <input type="text" class="form-control @if ($errors->has('transfer_ke'))
                    is-invalid
                @endif" name="transfer_ke" id="transfer_ke" value="{{old('transfer_ke')}}" maxlength="15">
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
                @endif" name="bank" id="bank" value="{{old('bank')}}" maxlength="10">
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
                @endif" name="no_rekening" id="no_rekening" value="{{old('no_rekening')}}">
                    @if ($errors->has('no_rekening'))
                    <div class="invalid-feedback">
                        {{$errors->first('no_rekening')}}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{route('billing.index')}}" class="btn btn-secondary" type="button">Batal</a>
        </div>
    </form>
</div>
@endsection
@push('js')
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}

<script>
            $('#nominal_transaksi').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true,
            });

        function tipeFun()
        {
            var tipe = $('#tipe').val();
            if(tipe == 1){
                // show fieldTransfer
                $('#fieldTransfer').attr('hidden', true);

                $('#transfer_ke').attr('disabled', true);
                $('#bank').attr('disabled', true);
                $('#no_rekening').attr('disabled', true);
            }else if(tipe == 2){
                // hide fieldTransfer
                $('#fieldTransfer').attr('hidden', false);
                $('#transfer_ke').val('');
                $('#bank').val('');
                $('#no_rekening').val('');
                $('#transfer_ke').attr('disabled', false);
                $('#bank').attr('disabled', false);
                $('#no_rekening').attr('disabled', false);
            } else {
                $('#fieldTransfer').attr('hidden', true);
            }
        }

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
                    this.submit();
                }
            })
        });
</script>
@endpush
