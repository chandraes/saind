@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>FORM BUNGA INVESTOR</u></h1>
        </div>
    </div>
    @include('swal')
    <form action="{{route('billing.bunga-investor.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="uraian" class="form-label">Tanggal</label>
                <input type="text" class="form-control @if ($errors->has('uraian'))
                    is-invalid
                @endif" name="tanggal" id="tanggal" value="{{date('d M Y')}}" disabled>
            </div>
            <div class="col-md-8 mb-3">
                <label for="uraian" class="form-label">Kreditor</label>
                <select class="form-select" name="kreditor_id" id="kreditor_id" required onchange="checkKreditor()">
                    <option value="" disabled selected>-- Pilih Salah Satu --</option>
                    @foreach ($kreditor as $d)
                        <option value="{{$d->id}}">{{$d->nama}}</option>
                    @endforeach
                </select>

            </div>
            <div class="col-md-4 mb-3">
                <label for="nominal_transaksi" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nominal_transaksi'))
                    is-invalid
                @endif" name="nominal_transaksi" id="nominal_transaksi" onkeyup="reCalculate()">
                </div>
                @if ($errors->has('nominal_transaksi'))
                <div class="invalid-feedback">
                    {{$errors->first('nominal_transaksi')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="pph" class="form-label">PPh</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('pph'))
                    is-invalid
                @endif" name="pph" id="pph" data-thousands="." disabled>
                </div>
                @if ($errors->has('pph'))
                <div class="invalid-feedback">
                    {{$errors->first('pph')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="grand_total" class="form-label">Grand Total</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('grand_total'))
                    is-invalid
                @endif" name="grand_total" id="grand_total" data-thousands="." disabled>
                </div>
                @if ($errors->has('grand_total'))
                <div class="invalid-feedback">
                    {{$errors->first('grand_total')}}
                </div>
                @endif
            </div>

        </div>
        <hr>
        <h3>Transfer Ke</h3>
        <br>
        <div class="row">

            <div class="col-md-4 mb-3">
                <label for="transfer_ke" class="form-label">Nama</label>
                <input type="text" class="form-control @if ($errors->has('transfer_ke'))
                    is-invalid
                @endif" name="transfer_ke" id="transfer_ke" required maxlength="15">
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
                @endif" name="bank" id="bank" required maxlength="10">
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
            <a href="{{route('billing.index')}}" class="btn btn-secondary" type="button">Batal</a>
        </div>
    </form>
</div>
@endsection
@push('js')
<script>
    var nominal = new Cleave('#nominal_transaksi', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        var no_rekening = new Cleave('#no_rekening', {
            delimiter: '-',
            blocks: [4, 4, 8]
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

        function reCalculate() {
            var data_kreditor = @json($kreditor);
            var kreditor_id = document.getElementById('kreditor_id').value;
            var kreditor = data_kreditor.find(x => x.id == kreditor_id);
            var nominal = document.getElementById('nominal_transaksi').value;
            // remove . and convert to number
            nominal = parseInt(nominal.replace(/\./g, ''));
            if (kreditor) {
                var pph = kreditor.apa_pph == 1 ? nominal * 0.02 : 0;
                document.getElementById('pph').value = pph.toLocaleString('id-ID');
                document.getElementById('grand_total').value = (nominal - pph).toLocaleString('id-ID');

            }
        }

        function checkKreditor() {
            var data_kreditor = @json($kreditor);
            var modal = {{$modal}};
            var kreditor_id = document.getElementById('kreditor_id').value;
            var kreditor = data_kreditor.find(x => x.id == kreditor_id);

            if (kreditor) {
                document.getElementById('transfer_ke').value = kreditor.nama_rek;
                document.getElementById('bank').value = kreditor.bank;
                document.getElementById('no_rekening').value = kreditor.no_rek;

                var nominal = kreditor.persen * modal / 100;
                document.getElementById('nominal_transaksi').value = nominal.toLocaleString('id-ID');

                var pph = kreditor.apa_pph == 1 ? nominal * 0.02 : 0;
                document.getElementById('pph').value = pph.toLocaleString('id-ID');
                document.getElementById('grand_total').value = (nominal - pph).toLocaleString('id-ID');
            }
        }


</script>
@endpush
