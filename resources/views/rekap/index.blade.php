@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>REKAP</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-left">
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'user' || auth()->user()->role === 'su')
        <h2 class="mt-3">UMUM</h2>
        <hr>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.kas-besar')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-besar.svg')}}" alt="" width="70">
                <h5 class="mt-3">KAS BESAR</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.nota-void')}}" class="text-decoration-none">
                <img src="{{asset('images/void.svg')}}" alt="" width="70">
                <h5 class="mt-3">NOTA VOID TRANSAKSI</h5>
            </a>
        </div>



    </div>
        <div class="row justify-content-left">
            <div class="col-md-2 text-center mt-5">
                <a href="#" class="text-decoration-none">
                    <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                    <h5 class="mt-3">DEPOSIT</h5>
                </a>
            </div>
            <div class="col-md-2 text-center mt-5">
                <a href="#" class="text-decoration-none">
                    <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                    <h5 class="mt-3">DIVIDEN</h5>
                </a>
            </div>
            <div class="col-md-2 text-center mt-5">
                <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalKasbon">
                    <img src="{{asset('images/kasbon.svg')}}" alt="" width="70">
                    <h5 class="mt-3">KASBON</h5>
                </a>
                <div class="modal fade" id="modalKasbon" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                    role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="direksiStafftitle">Pilih Jenis Rekap Kasbon</h5>
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
            <div class="col-md-2 text-center mt-5">
                <a href="#" class="text-decoration-none">
                    <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                    <h5 class="mt-3">LAIN-LAIN</h5>
                </a>
            </div>
            <div class="col-md-2 text-center mt-5">
                <a href="#" class="text-decoration-none">
                    <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                    <h5 class="mt-3">GANTI RUGI</h5>
                </a>
            </div>
            <div class="col-md-2 text-center mt-5">
                <a href="#" class="text-decoration-none">
                    <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                    <h5 class="mt-3">CSR<br>(TIDAK TERTENTU)</h5>
                </a>
            </div>

    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h2 class="mt-3">COST OPERATIONAL</h2>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.cost-operational')}}" class="text-decoration-none">
                <img src="{{asset('images/form-cost-operational.svg')}}" alt="" width="70">
                <h5 class="mt-3">OPERATIONAL</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.kas-kecil')}}" class="text-decoration-none">
                <img src="{{asset('images/kas-kecil.svg')}}" alt="" width="70">
                <h5 class="mt-3">KAS KECIL</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap-gaji')}}" class="text-decoration-none">
                <img src="{{asset('images/gaji.svg')}}" alt="" width="70">
                <h5 class="mt-3">GAJI</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">BUNGA INVESTOR</h5>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h2 class="mt-3">KHUSUS</h2>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.stock-barang')}}" class="text-decoration-none">
                <img src="{{asset('images/barang.svg')}}" alt="" width="70">
                <h5 class="mt-3">BARANG UMUM</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#maintenaceModal">
                <img src="{{asset('images/form-maintenance.svg')}}" alt="" width="70">
                <h5 class="mt-3">BARANG MAINTENANCE</h5>
            </a>

            <div class="modal fade" id="maintenaceModal" tabindex="-1" data-bs-backdrop="static"
                data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitleId">
                                Pilih Vehicle
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('rekap.maintenance-vehicle')}}" method="get">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <select class="form-select" name="vehicle_id" id="vehicle_id">
                                        @foreach ($maintenance as $m)
                                        <option value="{{$m->vehicle_id}}">{{$m->vehicle->nomor_lambung}}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Tutup
                                </button>
                                <button type="submit" class="btn btn-primary">Lanjutkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">STORING BBM</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#vendorModal">
                <img src="{{asset('images/form-vendor.svg')}}" alt="" width="70">
                <h5 class="mt-3">VENDOR</h5>
            </a>

            <div class="modal fade" id="vendorModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="vendorTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="vendorTitle">Pilih Vendor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('rekap.kas-vendor')}}" method="get">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <select class="form-select" name="vendor" id="vendor">
                                        <option value=""> -- Pilih Vendor -- </option>
                                        @foreach ($vendor as $v)
                                        <option value="{{$v->id}}">{{$v->nama}}</option>
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
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h2 class="mt-3">TRANSAKSI</h2>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.kas-uang-jalan')}}" class="text-decoration-none">
                <img src="{{asset('images/uang-jalan.svg')}}" alt="" width="70">
                <h5 class="mt-3">KAS UANG JALAN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">NOTA MUAT</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">NOTA BONGKAR</h5>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h2 class="mt-3">INVOICE</h2>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.nota-lunas')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-tagihan.svg')}}" alt="" width="70">
                <h5 class="mt-3">INVOICE CUSTOMER</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">INVOICE BAYAR VENDOR</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.bonus')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-bonus.svg')}}" alt="" width="70">
                <h5 class="mt-3">INVOICE BONUS SPONSOR</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.csr')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-csr.svg')}}" alt="" width="70">
                <h5 class="mt-3">INVOICE CSR<br>(TERTENTU)</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h5 class="mt-3">DASHBOARD</h5>
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    $(document).ready(function () {
        $('#vendor').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih Vendor --'
        });

        $('#vehicle_id').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih Nomor Lambung --'
        });
    });

    function tipeFormKasBon()
        {
            let val = document.getElementById('kasbonSelect').value;
            if (val === 'direksi') {
                window.location.href = "{{route('rekap.direksi')}}";
            } else if(val === 'staff') {
                window.location.href = "{{route('rekap.kas-bon')}}";
            }
        }
</script>
@endpush
