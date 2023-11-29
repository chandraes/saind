@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Kas Kecil (Void)</u></h1>
        </div>
    </div>
    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{session('error')}}',
        })
    </script>
    @endif

    <form action="{{route('kas-kecil.void.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-4 mb-3">
                <div class="mb-3">
                    <label for="kas_kecil_id" class="form-label">Pengeluaran</label>
                    <select class="form-select" name="kas_kecil_id" id="kas_kecil_id" onchange="selectPengeluaran()" required>
                        <option value=""> -- Pilih Pengeluaran Kas Kecil --</option>
                        @foreach ($data as $d)
                            <option value="{{$d->id}}">{{$d->uraian}} ({{$d->tanggal}})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-4 mb-3">
                <label for="uraian" class="form-label">Uraian</label>
                <input type="text" class="form-control @if ($errors->has('uraian'))
                    is-invalid
                @endif" name="uraian" id="uraian" required value="{{old('uraian')}}" maxlength="20" readonly>
            </div>
            <div class="col-md-4 mb-3">
                <label for="nominal_transaksi" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nominal_transaksi'))
                    is-invalid
                @endif" name="nominal_transaksi" id="nominal_transaksi" required data-thousands="." disabled>
                </div>
                @if ($errors->has('nominal_transaksi'))
                <div class="invalid-feedback">
                    {{$errors->first('nominal_transaksi')}}
                </div>
                @endif
            </div>
        </div>
        <hr>

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
    $(document).ready(function(){
            $('#nominal_transaksi').maskMoney({
                thousands: '.',
                decimal: ',',
                precision: 0,
                allowZero: true
            });
        });

        function selectPengeluaran()
        {
            var id = $('#kas_kecil_id').val();
            $.ajax({
                url: "{{route('kas-kecil.get-void')}}",
                type: "GET",
                data: {id: id},
                success: function(data){
                    console.log(data.data.uraian);
                    $('#uraian').val("Void " + data.data.uraian);
                    $('#nominal_transaksi').val(data.data.nominal_transaksi);
                    // maskMoney
                    $('#nominal_transaksi').maskMoney({
                        thousands: '.',
                        decimal: ',',
                        precision: 0
                    });
                    $('#nominal_transaksi').maskMoney('mask', data.data.nominal_transaksi);
                }
            });
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
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
