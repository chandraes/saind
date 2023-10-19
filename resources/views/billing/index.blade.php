@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BILLING</u></h1>
        </div>
    </div>
    {{-- if session has success, trigger sweet alert --}}
    @if (session('success'))
    <script>
        Swal.fire(
                'Berhasil!',
                '{{session('success')}}',
                'success'
            )
    </script>
    @endif
    <div class="row justify-content-left">
        @if (auth()->user()->role === 'admin')
        <div class="col-md-4 text-center mt-5">
            <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
            <div class="modal fade" id="formKasBesar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="mb-3 mt-3">
                                <select class="form-select form-select-lg" name="" id="tipeKasBesar">
                                    <option value="masuk">Penambahan Deposit</option>
                                    <option value="keluar">Pengembalian Deposit</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary"
                                onclick="tipeFormKasBesar()">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formKasBesar">
                <img src="{{asset('images/kas-besar.svg')}}" alt="" width="100">
                <h2>FORM DEPOSIT</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <!-- Modal trigger button -->
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formKasKecil">
                <img src="{{asset('images/kas-kecil.svg')}}" alt="" width="100">
                <h2>FORM KAS KECIL</h2>
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
        <div class="col-md-4 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#uangJalan">
                <img src="{{asset('images/uang-jalan.svg')}}" alt="" width="100">
                <h2>FORM KAS UANG JALAN</h2>
            </a>
            <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
            <div class="modal fade" id="uangJalan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="mb-3 mt-3">
                                <select class="form-select form-select-lg" name="" id="tipeKasUangJalan">
                                    <option value="masuk">Permintaan Kas Uang Jalan</option>
                                    <option value="keluar">Pengeluaran Uang Jalan</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <a href="#" class="btn btn-primary" onclick="tipeFormKasUangJalan()">Lanjutkan</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formBarang">
                <img src="{{asset('images/barang.svg')}}" alt="" width="100">
                <h2>FORM BARANG</h2>
            </a>
            <div class="modal fade" id="formBarang" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="fllTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fllTitle">Form Barang</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <select class="form-select form-select-lg" name="" id="formBarangSelect">
                                <option value="masuk">Beli</option>
                                <option value="keluar">Jual</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" onclick="tipeFormBarang()">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formLain">
                <img src="{{asset('images/lain.svg')}}" alt="" width="100">
                <h2>FORM LAIN-LAIN</h2>
            </a>
            <div class="modal fade" id="formLain" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="fllTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fllTitle">Form Lain-lain</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <select class="form-select form-select-lg" name="" id="formLainlain">
                                <option value="masuk">Masuk</option>
                                <option value="keluar">Keluar</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" onclick="tipeFormLainlain()">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('billing.transaksi.index')}}" class="text-decoration-none">
                <img src="{{asset('images/transaction.svg')}}" alt="" width="100">
                <h2>FORM TRANSAKSI</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="100">
                <h2>Dashboard</h2>
            </a>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    function tipeFormKasBesar() {
            let tipeKasBesar = document.getElementById('tipeKasBesar').value;
            if (tipeKasBesar === 'masuk') {
                window.location.href = "{{route('kas-besar.masuk')}}";
            } else if (tipeKasBesar === 'keluar') {
                window.location.href = "{{route('kas-besar.keluar')}}";
            }
        }
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

        function tipeFormKasUangJalan()
        {
            let val = document.getElementById('tipeKasUangJalan').value;
            if (val === 'masuk') {
                window.location.href = "{{route('kas-uang-jalan.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('kas-uang-jalan.keluar')}}";
            }
        }

        function tipeFormLainlain()
        {
            let val = document.getElementById('formLainlain').value;
            if (val === 'masuk') {
                window.location.href = "{{route('form-lain-lain.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('form-lain-lain.keluar')}}";
            }
        }

        function tipeFormBarang()
        {
            let val = document.getElementById('formBarangSelect').value;
            if (val === 'masuk') {
                window.location.href = "#";
            } else if (val === 'keluar') {
                window.location.href = "#";
            }
        }
</script>

@endpush
