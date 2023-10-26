@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-left mt-5">
        @if (auth()->user()->role === 'admin')

        @endif
        {{-- BACK BUTTON --}}
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('invoice.tagihan.index')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-tagihan.svg')}}" alt="" width="100">
                <h2>TAGIHAN <span class="text-danger">{{$invoice > 0 ? "(".$invoice.")" : ''}}</span></h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('invoice.bayar.index')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-bayar.svg')}}" alt="" width="100">
                <h2>BAYAR <span class="text-danger">{{$bayar > 0 ? "(".$bayar.")" : ''}}</span></h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('invoice.bonus.index')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-bonus.svg')}}" alt="" width="100">
                <h2>BONUS<span class="text-danger">{{$bonus > 0 ? "(".$bonus.")" : ''}}</span></h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('billing.transaksi.index')}}" class="text-decoration-none">
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
