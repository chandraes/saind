@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>FORM TRANSAKSI</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-left mt-5">
        <h4 class="mt-3">Transaksi</h4>
        @if (auth()->user()->role === 'admin')

        @endif
        {{-- BACK BUTTON --}}
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('transaksi.nota-muat')}}" class="text-decoration-none">
                <img src="{{asset('images/muat.svg')}}" alt="" width="80">
                <h4 class="mt-3">NOTA MUAT <span class="text-danger">{{$data->where('status', 1)->count() > 0 ?
                        "(".$data->where('status', 1)->count().")" : '' }}</span></h4>
            </a>
        </div>
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('transaksi.nota-bongkar')}}" class="text-decoration-none">
                <img src="{{asset('images/bongkar.svg')}}" alt="" width="80">
                <h4 class="mt-3">NOTA BONGKAR <span class="text-danger">{{$data->where('status', 2)->count() > 0 ?
                        "(".$data->where('status', 2)->count().")" : '' }}</span></h4>
            </a>
        </div>
        {{-- <div class="col-md-3 text-center mt-5">
            <a href="{{route('transaksi.sales-order')}}" class="text-decoration-none">
                <img src="{{asset('images/sales-order.svg')}}" alt="" width="80">
                <h4 class="mt-3">Sales Order <span class="text-danger">{{$data->whereIn('status', [1,2])->count() > 0
                        ? "(".$data->whereIn('status', [1,2])->count().")" : '' }}</span></h4>
            </a>
        </div> --}}
    </div>
    <div class="row justify-content-left mt-5">
        <h4 class="mt-3">Pembayaran</h4>
        <div class="col-md-3 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#customerId">
                <img src="{{asset('images/tagihan.svg')}}" alt="" width="80">
                <h4 class="mt-3">NOTA TAGIHAN CUSTOMER <span class="text-danger">{{$data->where('status', 3)->where('tagihan', 0)->count() > 0
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
                                <div class="col-md-3 text-center mt-5">
                                    <a href="{{route('transaksi.nota-tagihan', $c)}}" class="text-decoration-none">
                                        <img src="{{asset('images/tambang.svg')}}" alt="" width="80">
                                        <h4 class="mt-3">{{$c->singkatan}}
                                        @if ($data->where('status', 3)->where('tagihan', 0)->where('customer_id', $c->id)->count() > 0)
                                            <span class="text-danger">({{$data->where('status', 3)->where('tagihan', 0)->where('customer_id', $c->id)->count()}})</span>
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
        <div class="col-md-3 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#vendorBayar">
                <img src="{{asset('images/bayar.svg')}}" alt="" width="80">
                <h4 class="mt-3">NOTA BAYAR VENDOR <span class="text-danger">{{$data->where('status', 3)->where('bayar', 0)->count() > 0 ?
                        "(".$data->where('status', 3)->where('bayar', 0)->count().")" : '' }}</span></h4>
            </a>

            <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
            <div class="modal fade" id="vendorBayar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
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
                                        @if ($data->where('status', 3)->where('bayar', 0)->where('vendor_id', $v->kas_uang_jalan->vendor->id)->count() > 0)
                                        <span class="text-danger">({{$data->where('status', 3)->where('bayar', 0)->where('vendor_id', $v->kas_uang_jalan->vendor->id)->count()}})</span>
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
        <div class="col-md-3 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#sponsorModal">
                <img src="{{asset('images/bonus.svg')}}" alt="" width="80">
                <h4 class="mt-3">NOTA BONUS SPONSOR <span class="text-danger">{{$data->where('status', 3)->where('bonus', 0)->count() > 0 ?
                        "(".$data->where('status', 3)->where('bonus', 0)->count().")" : '' }}</span></h4>
            </a>

            <div class="modal fade" id="sponsorModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
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
                                        <option value="{{$v->kas_uang_jalan->vendor->sponsor->id}}">{{$v->kas_uang_jalan->vendor->sponsor->nama}}</option>
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
        <div class="col-md-3 text-center mt-5">
            <a href="#" class="text-decoration-none"  data-bs-toggle="modal" data-bs-target="#csrModal">
                <img src="{{asset('images/csr.svg')}}" alt="" width="80">
                <h4 class="mt-3">NOTA CSR
                    <span class="text-danger">{{$data->where('status', 3)->where('csr', 0)->where('nominal_csr', '>', 0)->count() > 0 ?
                        "(".$data->where('status', 3)->where('csr', 0)->where('nominal_csr', '>', 0)->count().")" : '' }}</span>
                </h4>
            </a>

            <div class="modal fade" id="csrModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="csrTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="csrTitle">Pilih Customer CSR</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                        @foreach ($customer->where('csr', 1) as $c)
                            <div class="col-md-3 text-center mt-5">
                                <form action="{{route('billing.nota-csr')}}" method="get">
                                    <input type="hidden" name="customer_id" value="{{$c->id}}">
                                    <button type="submit" class="text-decoration-none" style="border: none; background: none;">
                                        <img src="{{asset('images/nota-csr.svg')}}" alt="" width="80">
                                        <h4 class="mt-3">{{$c->singkatan}}</h4>
                                    </button>
                                </form>
                                    {{-- <img src="{{asset('images/nota-csr.svg')}}" alt="" width="80">
                                    <h4 class="mt-3">{{$c->singkatan}}</h4> --}}
                                </a>
                            </div>
                        @endforeach
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-5 justify-content-left">
        <h4 class="mt-3">Cut Off</h4>
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('invoice.tagihan.index')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-tagihan.svg')}}" alt="" width="80">
                <h4 class="mt-3">INVOICE TAGIHAN CUSTOMER <span class="text-danger">{{$invoice > 0 ? "(".$invoice.")" : ''}}</span></h4>
            </a>
        </div>
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('invoice.bayar.index')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-bayar.svg')}}" alt="" width="80">
                <h4 class="mt-3">INVOICE BAYAR VENDOR <span class="text-danger">{{$bayar > 0 ? "(".$bayar.")" : ''}}</span></h4>
            </a>
        </div>
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('invoice.bonus.index')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-bonus.svg')}}" alt="" width="80">
                <h4 class="mt-3">INVOICE BONUS SPONSOR <span class="text-danger">{{$bonus > 0 ? "(".$bonus.")" : ''}}</span></h4>
            </a>
        </div>
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('billing.invoice-csr')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-csr.svg')}}" alt="" width="80">
                <h4 class="mt-3">INVOICE CSR <span class="text-danger">{{$invoice_csr > 0 ? "(".$invoice_csr.")" : ''}}</span></h4>
            </a>
        </div>
    </div>
    <div class="row mt-5 justify-content-left">
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('billing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/back.svg')}}" alt="" width="80">
                <h4 class="mt-3">KEMBALI</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="80">
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
        $(document).ready(function() {
            $('#vendorSelect').select2({
                placeholder: 'Pilih Vendor',
                width: '100%',
                dropdownParent: $('#vendorBayar')
            });
        });
    </script>
@endpush
