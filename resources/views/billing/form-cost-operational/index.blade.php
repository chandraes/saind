@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>FORM COST OPERATIONAL</u></h1>
        </div>
    </div>
    {{-- if session has success, trigger sweet alert --}}
    @include('swal')
    <div class="row justify-content-left">

        <div class="col-md-3 text-center mt-5">
            <a href="{{route('billing.form-cost-operational.cost-operational')}}" class="text-decoration-none">
                <img src="{{asset('images/form-cost-operational.svg')}}" alt="" width="80">
                <h4 class="mt-3">FORM OPERATIONAL</h3>
            </a>
        </div>
        <div class="col-md-3 text-center mt-5">
            <!-- Modal trigger button -->
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formKasKecil">
                <img src="{{asset('images/kas-kecil.svg')}}" alt="" width="80">
                <h4 class="mt-3">FORM KAS KECIL</h3>
            </a>
            <!-- Modal Body -->
            <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
            <div class="modal fade" id="formKasKecil" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="mb-3 mt-3">
                                <select class="form-select form-select-lg" name="" id="tipeKasKecil">
                                    <option value="masuk">Permintaan Dana Kas Kecil</option>
                                    <option value="keluar">Pengeluaran Dana Kas Kecil</option>
                                    <option value="void">Void Dana Kas Kecil</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <a href="#" class="btn btn-primary" onclick="tipeFormKasKecil()">Lanjutkan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('billing.gaji.index', ['bulan' => $bulan, 'tahun' => $tahun])}}" class="text-decoration-none">
                <img src="{{asset('images/gaji.svg')}}" alt="" width="80">
                <h4 class="mt-3">FORM GAJI</h3>
            </a>
        </div>
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('billing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/back.svg')}}" alt="" width="80">
                <h4 class="mt-3">BACK</h3>
            </a>
        </div>
    </div>

</div>
@endsection
@push('js')
<script>
  function tipeFormKasKecil() {
            let val = document.getElementById('tipeKasKecil').value;
            if (val === 'masuk') {
                window.location.href = "{{route('kas-kecil.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('kas-kecil.keluar')}}";
            } else if (val === 'void') {
                window.location.href = "{{route('kas-kecil.void')}}";
            }
        }
</script>
@endpush
