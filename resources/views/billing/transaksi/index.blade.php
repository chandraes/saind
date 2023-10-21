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
        <h2>Transaksi</h2>
        @if (auth()->user()->role === 'admin')

        @endif
        {{-- BACK BUTTON --}}
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('transaksi.nota-muat')}}" class="text-decoration-none">
                <img src="{{asset('images/muat.svg')}}" alt="" width="100">
                <h2>Nota Muat <span class="text-danger">{{$data->where('status', 1)->where('void',0)->count() > 0 ?
                        "(".$data->where('status', 1)->where('void', 0)->count().")" : '' }}</span></h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('transaksi.nota-bongkar')}}" class="text-decoration-none">
                <img src="{{asset('images/bongkar.svg')}}" alt="" width="100">
                <h2>Nota Bongkar <span class="text-danger">{{$data->where('status', 2)->where('void', 0)->count() > 0 ?
                        "(".$data->where('status', 2)->where('void', 0)->count().")" : '' }}</span></h2>
            </a>
        </div>
    </div>
    <div class="row justify-content-left mt-5">
        <h2>Pembayaran</h2>
        <div class="col-md-4 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#customerId">
                <img src="{{asset('images/tagihan.svg')}}" alt="" width="100">
                <h2>Nota Tagihan <span class="text-danger">{{$data->where('status', 3)->where('tagihan', 0)->where('void', 0)->count() > 0
                        ? "(".$data->where('status', 3)->where('tagihan', 0)->where('void', 0)->count().")" : '' }}</span></h2>
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
                                <div class="col-md-4 text-center mt-5">
                                    <a href="{{route('transaksi.nota-tagihan', $c)}}" class="text-decoration-none">
                                        <img src="{{asset('images/tambang.svg')}}" alt="" width="100">
                                        <h2>{{$c->singkatan}}</h2>
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
        <div class="col-md-4 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#vendorBayar">
                <img src="{{asset('images/bayar.svg')}}" alt="" width="100">
                <h2>Nota Bayar <span class="text-danger">{{$data->where('status', 3)->where('void', 0)->count() > 0 ?
                        "(".$data->where('status', 3)->where('void', 0)->count().")" : '' }}</span></h2>
            </a>

            <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
            <div class="modal fade" id="vendorBayar" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTitleId">Pilih Vendor</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('transaksi.nota-bayar') }}" method="post">
                        @csrf
                        </form>
                        <div class="modal-body">
                            <div class="mb-3">
                                <select class="form-select" name="" id="">
                                    <option selected>Select one</option>
                                    @foreach ($vendor as $v)
                                    <option value="{{$v->id}}">{{$v->kas_uang_jalan->vendor->nama}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary">Lanjutkan</button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('transaksi.nota-bongkar')}}" class="text-decoration-none">
                <img src="{{asset('images/bonus.svg')}}" alt="" width="100">
                <h2>Nota Bonus <span class="text-danger">{{$data->where('status', 3)->where('void', 0)->count() > 0 ?
                        "(".$data->where('status', 3)->where('void', 0)->count().")" : '' }}</span></h2>
            </a>
        </div>
    </div>
    <div class="row mt-5 justify-content-left">
        <h2>Cut Off</h2>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('transaksi.nota-bongkar')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice.svg')}}" alt="" width="100">
                <h2>Invoice</h2>
            </a>
        </div>
    </div>
    <div class="row mt-5 justify-content-left">
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('billing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/back.svg')}}" alt="" width="100">
                <h2>KEMBALI</h2>
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
