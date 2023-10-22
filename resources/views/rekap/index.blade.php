@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>REKAP</u></h1>
        </div>
    </div>
    <div class="row justify-content-left">
        @if (auth()->user()->role === 'admin')
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('rekap.kas-besar')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-besar.svg')}}" alt="" width="100">
                <h2>Kas Besar</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('rekap.kas-kecil')}}" class="text-decoration-none">
                <img src="{{asset('images/kas-kecil.svg')}}" alt="" width="100">
                <h2>Kas Kecil</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('rekap.kas-uang-jalan')}}" class="text-decoration-none">
                <img src="{{asset('images/uang-jalan.svg')}}" alt="" width="100">
                <h2>Kas Uang Jalan</h2>
            </a>
        </div>
        @endif
        <div class="col-md-4 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/stock.svg')}}" alt="" width="100">
                <h2>Stock Barang</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('rekap.nota-void')}}" class="text-decoration-none">
                <img src="{{asset('images/void.svg')}}" alt="" width="100">
                <h2>Nota Void</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/nota-lunas.svg')}}" alt="" width="100">
                <h2>Nota Lunas</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kas-vendor.svg')}}" alt="" width="100">
                <h2>Kas Vendor</h2>
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
