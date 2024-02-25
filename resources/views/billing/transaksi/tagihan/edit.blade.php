@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Edit Tagihan</u></h1>
        </div>
    </div>
    @include('swal')
    @if ($errors->any())
    <script>
        Swal.fire({
            title: 'Error!',
            text: '{{$errors->first()}}',
            icon: 'error',
            confirmButtonText: 'Ok'
        })
    </script>
    @endif
    <form action="{{route('transaksi.nota-tagihan.update', $d)}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-4 mb-3">
                <label for="kode" class="form-label">Kode</label>
                <input type="text" class="form-control" name="kode"
                    id="kode" placeholder="" value="UJ{{sprintf("%02d",
                    $d->kas_uang_jalan->nomor_uang_jalan)}}" readonly>
            </div>
            <div class="col-4 mb-3">
                <label for="tanggal_uang_jalan" class="form-label">Tanggal</label>
                <input type="text" class="form-control" name="tanggal_uang_jalan"
                    id="tanggal_uang_jalan" placeholder="" value="{{$d->kas_uang_jalan->tanggal}}" readonly>
            </div>
            <div class="col-4 mb-3">
                <label for="no_lambung" class="form-label">Nomor Lambung</label>
                <input type="text" class="form-control" name="no_lambung"
                    id="no_lambung" placeholder=""
                    value="{{$d->kas_uang_jalan->vehicle->nomor_lambung}}" readonly>
            </div>
            <div class="col-4 mb-3">
                <label for="vendor" class="form-label">Vendor</label>
                <input type="text" class="form-control" name="vendor" id="vendor"
                    placeholder="" value="{{$d->kas_uang_jalan->vendor->nickname}}"
                    readonly>
            </div>
            <div class="col-4 mb-3">
                <label for="tambang" class="form-label">Tambang</label>
                <input type="text" class="form-control" name="tambang" id="tambang"
                    placeholder="" value="{{$d->kas_uang_jalan->customer->singkatan}}"
                    readonly>
            </div>
            <div class="col-4 mb-3">
                <label for="rute" class="form-label">Rute</label>
                <input type="text" class="form-control" name="rute" id="rute"
                    placeholder="" value="{{$d->kas_uang_jalan->rute->nama}}" readonly>
            </div>
        </div>
        <hr>
        <div class="row">
            <h3>Informasi Muat</h3>
            <div class="col-4 mb-3">
                <label for="tanggal_muat" class="form-label">Tanggal Muat</label>
                <input type="text" class="form-control" name="tanggal_muat" id="tanggal_muat"
                    placeholder="" value="{{$d->id_tanggal_muat}}" required>
            </div>
            <div class="col-4 mb-3">
                <label for="nota_muat" class="form-label">Nota Muat</label>
                <input type="text" class="form-control" name="nota_muat" id="nota_muat"
                    placeholder="" value="{{$d->nota_muat}}" required>
            </div>
            @if ($d->kas_uang_jalan->customer->gt_muat == 0)
            <div class="col-md-4 mb-3">
                <label for="tonase" class="form-label">Tonase </label>
                <input type="text" class="form-control" name="tonase" id="tonase" placeholder="" required>
                <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
            </div>
            @else
            <div class="col-md-4 mb-3">
                <label for="gross_muat" class="form-label">GROSS</label>
                <input type="text" class="form-control" name="gross_muat" id="gross_muat" value="{{$d->gross_muat}}" required oninput="calNettoMuat()">
                <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
                <br>
                <br>
                <label for="tarra_muat" class="form-label">TARRA</label>
                <input type="text" class="form-control" name="tarra_muat" id="tarra_muat" value="{{$d->tarra_muat}}" required  oninput="calNettoMuat()">
                <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
                <br><br>
                <label for="tonase" class="form-label">NETTO</label>
                <input type="text" class="form-control" name="tonase" id="tonase" value="{{$d->tonase}}" required readonly>
                <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
            </div>
            @endif
        </div>
        <hr>
        <div class="row">
            <h3>Informasi Bongkar</h3>
            <div class="col-4 mb-3">
                <label for="tanggal_bongkar" class="form-label">Tanggal Bongkar</label>
                <input type="text" class="form-control" name="tanggal_bongkar" id="tanggal_bongkar"
                    placeholder="" value="{{$d->id_tanggal_bongkar}}" required>
            </div>
            <div class="col-4 mb-3">
                <label for="nota_bongkar" class="form-label">Nota Bongkar</label>
                <input type="text" class="form-control" name="nota_bongkar" id="nota_bongkar"
                    placeholder="" value="{{$d->nota_bongkar}}" required>
            </div>
            @if ($d->kas_uang_jalan->customer->gt_bongkar == 0)
            <div class="col-md-4 mb-3">
                <label for="timbangan_bongkar" class="form-label">Tonase Bongkar</label>
                <input type="text" class="form-control" name="timbangan_bongkar" id="timbangan_bongkar"
                    placeholder="" value="{{$d->timbangan_bongkar}}" required>
                    <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
            </div>
            @else
            <div class="col-md-4 mb-3">
                <label for="gross_bongkar" class="form-label">GROSS</label>
                <input type="text" class="form-control" name="gross_bongkar" id="gross_bongkar" value="{{$d->gross_bongkar}}" required oninput="calNettoBongkar()">
                <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
                <br>
                <br>
                <label for="tarra_bongkar" class="form-label">TARRA</label>
                <input type="text" class="form-control" name="tarra_bongkar" id="tarra_bongkar" value="{{$d->tarra_bongkar}}" required  oninput="calNettoBongkar()">
                <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
                <br><br>
                <label for="timbangan_bongkar" class="form-label">Tonase Bongkar</label>
                <input type="text" class="form-control" name="timbangan_bongkar" id="timbangan_bongkar"
                    placeholder="" value="{{$d->timbangan_bongkar}}" required readonly>
                    <small id="helpId" class="form-text text-danger">(Gunakan "." untuk pemisah desimal)</small>
            </div>
            @endif

        </div>

        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{route('billing.transaksi.index')}}" class="btn btn-secondary" type="button">Batal</a>
          </div>
    </form>
</div>
@endsection
@push('css')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
@endpush
@push('js')

    <script>
            $( function() {

                $( "#tanggal_muat" ).datepicker({
                    dateFormat: "dd-mm-yy"
                });

                $( "#tanggal_bongkar" ).datepicker({
                    dateFormat: "dd-mm-yy"
                });

            });

            function calNettoMuat() {
                var gross = parseFloat(document.getElementById('gross_muat').value);
                var tarra = parseFloat(document.getElementById('tarra_muat').value);
                var netto = (gross - tarra).toFixed(2); // Keep only two decimals
                document.getElementById('tonase').value = netto;
            }

            function calNettoBongkar() {
                var grossBongkar = parseFloat(document.getElementById('gross_bongkar').value);
                var tarraBongkar = parseFloat(document.getElementById('tarra_bongkar').value);
                var nettoBongkar = (grossBongkar - tarraBongkar).toFixed(2);
                document.getElementById('timbangan_bongkar').value = nettoBongkar;
            }


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
