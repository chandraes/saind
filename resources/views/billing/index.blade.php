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
                <h4 class="mt-3">FORM DIVIDEN</h4>
            </a>
        </div>

        <div class="modal fade" id="formVendor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
            role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="mb-3 mt-3">
                            <select class="form-select form-select-lg" name="" id="titipVendor">
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
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM GANTI RUGI</h4>
            </a>
        </div>
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
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM BUNGA INVESTOR</h4>
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
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formVendor">
                <img src="{{asset('images/form-vendor.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM VENDOR</h4>
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
        {{-- BACK BUTTON --}}
         <div class="col-md-2 text-center mt-5">
            <a href="{{route('transaksi.nota-muat')}}" class="text-decoration-none">
                <img src="{{asset('images/muat.svg')}}" alt="" width="70">
                <h4 class="mt-3">NOTA MUAT <span class="text-danger">{{$data->where('status', 1)->count() > 0 ?
                        "(".$data->where('status', 1)->count().")" : '' }}</span></h4>
            </a>
        </div>
         <div class="col-md-2 text-center mt-5">
            <a href="{{route('transaksi.nota-bongkar')}}" class="text-decoration-none">
                <img src="{{asset('images/bongkar.svg')}}" alt="" width="70">
                <h4 class="mt-3">NOTA BONGKAR <span class="text-danger">{{$data->where('status', 2)->count() > 0 ?
                        "(".$data->where('status', 2)->count().")" : '' }}</span></h4>
            </a>
        </div>
        {{--  <div class="col-md-2 text-center mt-5">
            <a href="{{route('transaksi.sales-order')}}" class="text-decoration-none">
                <img src="{{asset('images/sales-order.svg')}}" alt="" width="70">
                <h4 class="mt-3">Sales Order <span class="text-danger">{{$data->whereIn('status', [1,2])->count() > 0
                        ? "(".$data->whereIn('status', [1,2])->count().")" : '' }}</span></h4>
            </a>
        </div> --}}
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h4 class="mt-3">NOTA</h4>
         <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#customerId">
                <img src="{{asset('images/tagihan.svg')}}" alt="" width="70">
                <h4 class="mt-3">NOTA TAGIHAN CUSTOMER <span class="text-danger">{{$data->where('status',
                        3)->where('tagihan', 0)->count() > 0
                        ? "(".$data->where('status', 3)->where('tagihan', 0)->count().")" : '' }}</span></h4>
            </a>
            <div class="modal fade" id="customerId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="customerTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable  modal-dialog-centered modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="customerTitleId">Nota Tagihan Customer</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                @foreach ($customer as $c)
                                 <div class="col-md-2 text-center mt-5">
                                    <a href="{{route('transaksi.nota-tagihan', $c)}}" class="text-decoration-none">
                                        <img src="{{asset('images/tambang.svg')}}" alt="" width="70">
                                        <h4 class="mt-3">{{$c->singkatan}}
                                            @if ($data->where('status', 3)->where('tagihan', 0)->where('customer_id',
                                            $c->id)->count() > 0)
                                            <span class="text-danger">({{$data->where('status', 3)->where('tagihan',
                                                0)->where('customer_id', $c->id)->count()}})</span>
                                            @endif
                                        </h4>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#vendorBayar">
                <img src="{{asset('images/bayar.svg')}}" alt="" width="70">
                <h4 class="mt-3">NOTA BAYAR VENDOR <span class="text-danger">{{$data->where('status', 3)->where('bayar',
                        0)->count() > 0 ?
                        "(".$data->where('status', 3)->where('bayar', 0)->count().")" : '' }}</span></h4>
            </a>
            <div class="modal fade" id="vendorBayar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitleId">Pilih Vendor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('transaksi.nota-bayar') }}" method="get">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <select class="form-select" name="vendor_id" id="vendorSelect">
                                        <option value="">Select one</option>
                                        @foreach ($vendor as $v)
                                        <option value="{{$v->kas_uang_jalan->vendor->id}}">
                                            {{$v->kas_uang_jalan->vendor->nama}}
                                            @if ($data->where('status', 3)->where('bayar', 0)->where('vendor_id',
                                            $v->kas_uang_jalan->vendor->id)->count() > 0)
                                            <span class="text-danger">({{$data->where('status', 3)->where('bayar',
                                                0)->where('vendor_id',
                                                $v->kas_uang_jalan->vendor->id)->count()}})</span>
                                            @endif
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-primary">Lanjutkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
         <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#sponsorModal">
                <img src="{{asset('images/bonus.svg')}}" alt="" width="70">
                <h4 class="mt-3">NOTA BONUS SPONSOR <span class="text-danger">{{$data->where('status',
                        3)->where('bonus', 0)->count() > 0 ?
                        "(".$data->where('status', 3)->where('bonus', 0)->count().")" : '' }}</span></h4>
            </a>

            <div class="modal fade" id="sponsorModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitleId">Pilih Sponsor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('transaksi.nota-bonus') }}" method="get">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <select class="form-select" name="sponsor_id" id="vendorSelect">
                                        <option selected>Select one</option>
                                        @foreach ($sponsor as $v)
                                        <option value="{{$v->kas_uang_jalan->vendor->sponsor->id}}">
                                            {{$v->kas_uang_jalan->vendor->sponsor->nama}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                <button type="submit" class="btn btn-primary">Lanjutkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
         <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#csrModal">
                <img src="{{asset('images/csr.svg')}}" alt="" width="70">
                <h4 class="mt-3">NOTA CSR
                    <span class="text-danger">{{$data->where('status', 3)->where('csr', 0)->where('nominal_csr', '>',
                        0)->count() > 0 ?
                        "(".$data->where('status', 3)->where('csr', 0)->where('nominal_csr', '>', 0)->count().")" : ''
                        }}</span>
                </h4>
            </a>

            <div class="modal fade" id="csrModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="csrTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="csrTitle">Pilih Customer CSR</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                @foreach ($customer->where('csr', 1) as $c)
                                 <div class="col-md-2 text-center mt-5">
                                    <form action="{{route('billing.nota-csr')}}" method="get">
                                        <input type="hidden" name="customer_id" value="{{$c->id}}">
                                        <button type="submit" class="text-decoration-none"
                                            style="border: none; background: none;">
                                            <img src="{{asset('images/nota-csr.svg')}}" alt="" width="70">
                                            <h4 class="mt-3">{{$c->singkatan}}</h4>
                                        </button>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h4 class="mt-3">INVOICE</h4>
         <div class="col-md-2 text-center mt-5">
            <a href="{{route('invoice.tagihan.index')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-tagihan.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVOICE CUSTOMER <span class="text-danger">{{$invoice > 0 ? "(".$invoice.")" :
                        ''}}</span></h4>
            </a>
        </div>
         <div class="col-md-2 text-center mt-5">
            <a href="{{route('invoice.bayar.index')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-bayar.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVOICE BAYAR VENDOR <span class="text-danger">{{$bayar > 0 ? "(".$bayar.")" :
                        ''}}</span></h4>
            </a>
        </div>
         <div class="col-md-2 text-center mt-5">
            <a href="{{route('invoice.bonus.index')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-bonus.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVOICE BONUS SPONSOR <span class="text-danger">{{$bonus > 0 ? "(".$bonus.")" :
                        ''}}</span></h4>
            </a>
        </div>
         <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.invoice-csr')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-csr.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVOICE CSR <span class="text-danger">{{$invoice_csr > 0 ? "(".$invoice_csr.")" :
                        ''}}</span></h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-3">DASHBOARD</h4>
            </a>
        </div>
    </div>

</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>

    $('#vendorSelect').select2({
                placeholder: 'Pilih Vendor',
                width: '100%',
                dropdownParent: $('#vendorBayar')
            });
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
            let val = document.getElementById('titipVendor').value;
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
