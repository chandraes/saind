@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Kas Uang Jalan (Penyesuaian)</u></h1>
        </div>
    </div>
    @include('swal')
    @php
        $role = ['admin', 'su'];
    @endphp
    <form action="{{route('kas-uang-jalan.penyesuaian.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-2 mb-3">
                <label for="uraian" class="form-label">Tanggal</label>
                <input type="text" class="form-control @if ($errors->has('uraian'))
                    is-invalid
                @endif" name="tanggal" id="tanggal" value="{{date('d M Y')}}" required disabled>
            </div>
            <div class="col-md-3 mb-3">
                <label for="uraian" class="form-label">Uraian</label>
                <input type="text" class="form-control @if ($errors->has('uraian'))
                    is-invalid
                @endif" name="uraian" id="uraian" required maxlength="20" value="{{old('uraian')}}">
            </div>
            <div class="col-md-3 mb-3">
                <div class="mb-3">
                    <label for="tipe" class="form-label">Jenis Transaksi</label>
                    <select class="form-select" name="tipe" id="tipe" required onchange="checkRekening()">
                        <option value="" disabled selected>-- Pilih Salah Satu --</option>
                        <option value="1" {{old('tipe') == 1 ? 'selected' : ''}}>Dana Masuk</option>
                        <option value="0" {{old('tipe') == 1 ? 'selected' : ''}}>Dana Keluar</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="nominal_transaksi" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nominal_transaksi'))
                    is-invalid
                @endif" value="{{old('nominal_transaksi')}}" name="nominal_transaksi" id="nominal_transaksi"  {{ !in_array(auth()->user()->role, $role) ? 'onkeyup=checkNominal()' : '' }} required>
                  </div>
                @if ($errors->has('nominal_transaksi'))
                <div class="invalid-feedback">
                    {{$errors->first('nominal_transaksi')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <h3>Transfer Ke</h3>
        <br>
        <div class="row">

            <div class="col-md-4 mb-3">
                <label for="nama_rek" class="form-label">Nama</label>
                <input type="text" class="form-control @if ($errors->has('transfer_ke'))
                    is-invalid
                @endif" name="transfer_ke" id="transfer_ke" required >
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
                @endif" name="bank" id="bank" required >
                @if ($errors->has('bank'))
                <div class="invalid-feedback">
                    {{$errors->first('bank')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="no_rek" class="form-label">Nomor Rekening</label>
                <input type="text" class="form-control @if ($errors->has('no_rek'))
                    is-invalid
                @endif" name="no_rekening" id="no_rekening" required>
                @if ($errors->has('no_rekening'))
                <div class="invalid-feedback">
                    {{$errors->first('no_rekening')}}
                </div>
                @endif
            </div>
        </div>

        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
             @if (auth()->user()->role == 'asisten-user')
            <a href="{{route('home')}}" class="btn btn-secondary" type="button">Batal</a>
            @else
            <a href="{{route('billing.index')}}" class="btn btn-secondary" type="button">Batal</a>
            @endif
          </div>
    </form>
</div>
@endsection
@push('js')
    <script>

        function checkRekening(){
                var rekening = @json($rekening);
                var tipe = document.getElementById('tipe').value;

                if (tipe == 1) {
                    document.getElementById('transfer_ke').value = rekening.nama_rekening;
                    document.getElementById('bank').value = rekening.nama_bank;
                    document.getElementById('no_rekening').value = rekening.nomor_rekening;
                } else {
                    document.getElementById('transfer_ke').value = '';
                    document.getElementById('bank').value = '';
                    document.getElementById('no_rekening').value = '';
                }
            }

        function checkNominal() {
            var nominal = document.getElementById('nominal_transaksi').value;
            nominal = nominal.replace(/\./g, '');
            var batasan = {!! $batasan !!};
            if (nominal > batasan) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Nominal melebihi batasan!',
                })
                document.getElementById('nominal_transaksi').value = '';
            }
        }

        var nominal = new Cleave('#nominal_transaksi', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });
        // masukForm on submit, sweetalert confirm
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
    </script>
@endpush
