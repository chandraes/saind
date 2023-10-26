@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>Database</u></h1>
        </div>
    </div>
    <div class="row justify-content-left">
        <h2>Customer</h2>
        @if (auth()->user()->role === 'admin')
        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('rute.index')}}" class="text-decoration-none">
                <img src="{{asset('images/rute.svg')}}" alt="" width="100">
                <h2>Rute</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('customer.index')}}" class="text-decoration-none">
                <img src="{{asset('images/company.svg')}}" alt="" width="100">
                <h2>Customer</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('bbm-storing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/bbm.svg')}}" alt="" width="100">
                <h2>BBM Storing</h2>
            </a>
        </div>
        @endif
    </div>
    <hr>
    <div class="row justify-content-left">
        <h2>VENDOR</h2>
        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('sponsor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/sponsor.svg')}}" alt="" width="100">
                <h2>Sponsor</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('vendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/vendor.svg')}}" alt="" width="100">
                <h2>Vendor</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('vehicle.index')}}" class="text-decoration-none">
                <img src="{{asset('images/dumptruckempty.svg')}}" alt="" width="100">
                <h2>Vehicle</h2>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h2>OTHERS</h2>
        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('direksi.index')}}" class="text-decoration-none">
                <img src="{{asset('images/direksi.svg')}}" alt="" width="100">
                <h2>Direksi</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('karyawan.index')}}" class="text-decoration-none">
                <img src="{{asset('images/karyawan.svg')}}" alt="" width="100">
                <h2>Karyawan</h2>
            </a>
        </div>

        <div class="col-md-4 text-center MT-3 mb-3">
            <a href="{{route('rekening.index')}}" class="text-decoration-none">
                <img src="{{asset('images/akun-bank.svg')}}" alt="" width="100">
                <h2>Rekening</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('kategori-barang.index')}}" class="text-decoration-none">
                <img src="{{asset('images/stock.svg')}}" alt="" width="100">
                <h2>Kategori Barang</h2>
            </a>
        </div>

        <div class="col-md-4 text-center mt-3 mb-3">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="100">
                <h2>Dashboard</h2>
            </a>
        </div>
    </div>
</div>
@endsection
