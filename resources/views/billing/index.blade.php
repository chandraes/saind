@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BILLING</u></h1>
        </div>
    </div>
    {{-- if session has success, trigger sweet alert --}}
    @include('swal')
    @include('billing.form-cost-operational.modal-co')
    <div class="row justify-content-left">
        <h4 class="mt-3">UMUM</h4>
        <div class="col-md-2 text-center mt-5">
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
                <img src="{{asset('images/form-deposit.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM DEPOSIT</h4>
            </a>
        </div>

        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.deviden.index')}}" class="text-decoration-none">
                <img src="{{asset('images/dividen.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM DEVIDEN</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formVendor">
                <img src="{{asset('images/form-vendor.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM VENDOR</h4>
            </a>

        </div>
        <div class="modal fade" id="formVendor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="mb-3 mt-3">
                            <select class="form-select form-select-lg" name="" id="vendorSelect">
                                <option value="titipan">Titipan Vendor</option>
                                <option value="pelunasan">Pelunasan Tagihan</option>
                                <option value="bayar">Bayar dari Vendor</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <a href="#" class="btn btn-primary" onclick="tipeformVendor()">Lanjutkan</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#direksiStaff">
                <img src="{{asset('images/kasbon.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM KASBON</h4>
            </a>
            <div class="modal fade" id="direksiStaff" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="direksiStafftitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="direksiStafftitle">Pilih Jenis Kasbon</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <select class="form-select form-select-lg" name="" id="kasbonSelect">
                                <option value="direksi">Direksi</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" onclick="tipeFormKasBon()">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'su')
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formLain">
                <img src="{{asset('images/form-lain.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM LAIN-LAIN</h4>
            </a>
            <div class="modal fade" id="formLain" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="fllTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fllTitle">Form Lain-lain</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <select class="form-select form-select-lg" name="" id="formLainlain">
                                <option value="masuk">Dana Masuk</option>
                                <option value="keluar">Dana Keluar</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary"
                                onclick="tipeFormLainlain()">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
    <hr>
    <br>

    <div class="row justify-content-left">
        <h4 class="mt-3">COST OPERATIONAL</h4>
        <div class="col-md-2 text-center mt-5">
            <a @if (Auth::user()->role == 'admin' || Auth::user()->role == 'su')
                href="#" data-bs-toggle="modal" data-bs-target="#modalCo"
                @else
                href="{{route('billing.form-cost-operational.cost-operational')}}"
            @endif class="text-decoration-none">
                <img src="{{asset('images/form-cost-operational.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM OPERATIONAL</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <!-- Modal trigger button -->
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formKasKecil">
                <img src="{{asset('images/kas-kecil.svg')}}" alt="" width="70">
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
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.gaji.index', ['bulan' => $bulan, 'tahun' => $tahun])}}" class="text-decoration-none">
                <img src="{{asset('images/gaji.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM GAJI</h3>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h4 class="mt-3">KHUSUS</h4>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formBarang">
                <img src="{{asset('images/barang.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM BARANG UMUM</h4>
            </a>
            <div class="modal fade" id="formBarang" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="fllTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fllTitle">Form Barang</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <select class="form-select form-select-lg" name="" id="formBarangSelect">
                                <option value="masuk">Beli</option>
                                <option value="keluar">Jual ke Vendor</option>
                                <option value="keluar-umum">Jual ke Umum</option>
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
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formMaintenance">
                <img src="{{asset('images/form-maintenance.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM BARANG MAINTENANCE</h4>
            </a>
            <div class="modal fade" id="formMaintenance" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="fllTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="fllTitle">Form Barang Maintenance</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <select class="form-select form-select-lg" name="" id="fomrMaintenanceSelect">
                                <option value="masuk">Beli</option>
                                <option value="keluar">Jual ke Vendor</option>
                                <option value="keluar-umum">Jual ke Umum</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" onclick="tipeFormMaintenance()">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.storing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/storing.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM STORING BBM</h4>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h4 class="mt-3">TRANSAKSI</h4>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#uangJalan">
                <img src="{{asset('images/uang-jalan.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM KAS UANG JALAN</h4>
            </a>
            <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
            <div class="modal fade" id="uangJalan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
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
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.transaksi.index')}}" class="text-decoration-none">
                <img src="{{asset('images/transaksi.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM TRANSAKSI</h4>
            </a>
        </div>


        <div class="col-md-2 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-3">DASHBOARD</h4>
            </a>
        </div>
    </div>
    <hr>
    <br>

</div>
@endsection
@push('js')

@endpush
@push('js')
<script>
    function tipeFormKasBesar() {
            let val = document.getElementById('tipeKasBesar').value;
            if (val === 'masuk') {
                window.location.href = "{{route('kas-besar.masuk')}}";
            } else if (val === 'keluar') {
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

        function tipeFormCo() {
            let val = document.getElementById('formCo').value;
            if (val === 'masuk') {
                window.location.href = "{{route('billing.form-cost-operational.masuk')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('billing.form-cost-operational.cost-operational')}}";
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
                window.location.href = "{{route('billing.form-barang.beli')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('billing.form-barang.jual')}}";
            } else if(val === 'keluar-umum') {
                window.location.href = "{{route('billing.form-barang.jual-umum')}}";
            }
        }

        function tipeFormMaintenance()
        {
            let val = document.getElementById('fomrMaintenanceSelect').value;
            if (val === 'masuk') {
                window.location.href = "{{route('billing.form-maintenance.beli')}}";
            } else if (val === 'keluar') {
                window.location.href = "{{route('billing.form-maintenance.jual-vendor')}}";
            } else if (val === 'keluar-umum') {
                window.location.href = "{{route('billing.form-maintenance.jual-umum')}}";
            }
        }

        function tipeformVendor()
        {
            let val = document.getElementById('vendorSelect').value;
            if (val === 'titipan') {
                window.location.href = "{{route('billing.vendor.titipan')}}";
            } else if(val === 'pelunasan') {
                window.location.href = "{{route('billing.vendor.pelunasan')}}";
            } else if(val === 'bayar') {
                window.location.href = "{{route('billing.vendor.bayar')}}";
            }
        }

        function tipeFormKasBon()
        {
            let val = document.getElementById('kasbonSelect').value;
            if (val === 'direksi') {
                window.location.href = "{{route('billing.kasbon.direksi.index')}}";
            } else if(val === 'staff') {
                window.location.href = "{{route('billing.kasbon.kas-bon-staff')}}";
            }
        }
</script>
@endpush
