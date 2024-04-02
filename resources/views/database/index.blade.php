@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>Database</u></h1>
        </div>
    </div>
    <div class="row justify-content-left">
        <h3 class="mt-2">Customer</h3>
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'su')
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('rute.index')}}" class="text-decoration-none">
                <img src="{{asset('images/rute.svg')}}" alt="" width="80">
                <h3 class="mt-2">Rute</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('customer.index')}}" class="text-decoration-none">
                <img src="{{asset('images/company.svg')}}" alt="" width="80">
                <h3 class="mt-2">Customer</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('bbm-storing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/bbm.svg')}}" alt="" width="80">
                <h3 class="mt-2">BBM Storing</h3>
            </a>
        </div>
        @endif
    </div>
    <hr>
    <div class="row justify-content-left">
        <h3 class="mt-2">VENDOR</h3>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('sponsor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/sponsor.svg')}}" alt="" width="80">
                <h3 class="mt-2">Sponsor</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('vendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/vendor.svg')}}" alt="" width="80">
                <h3 class="mt-2">Vendor</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('vehicle.index')}}" class="text-decoration-none">
                <img src="{{asset('images/dumptruckempty.svg')}}" alt="" width="80">
                <h3 class="mt-2">Vehicle</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/upah-gendong.svg')}}" alt="" width="80">
                <h3 class="mt-2">Upah Gendong</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('vehicle.index')}}" class="text-decoration-none">
                <img src="{{asset('images/aktivasi-maintenance.svg')}}" alt="" width="80">
                <h3 class="mt-2">Aktivasi Maintenance Vehicle</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/db-ban.svg')}}" alt="" width="80">
                <h3 class="mt-2">Ban Luar</h3>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h3 class="mt-2">BIODATA</h3>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('karyawan.index')}}" class="text-decoration-none">
                <img src="{{asset('images/karyawan.svg')}}" alt="" width="80">
                <h3 class="mt-2">Staff</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('direksi.index')}}" class="text-decoration-none">
                <img src="{{asset('images/direksi.svg')}}" alt="" width="80">
                <h3 class="mt-2">Direksi</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('pemegang-saham.index')}}" class="text-decoration-none">
                <img src="{{asset('images/saham.svg')}}" alt="" width="80">
                <h3 class="mt-2">Pemegang Saham</h3>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h3 class="mt-2">OTHERS</h3>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('rekening.index')}}" class="text-decoration-none">
                <img src="{{asset('images/akun-bank.svg')}}" alt="" width="80">
                <h3 class="mt-2">Nomor Rekening Transaksi</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('kategori-barang.index')}}" class="text-decoration-none">
                <img src="{{asset('images/stock.svg')}}" alt="" width="80">
                <h3 class="mt-2">Kategori Barang<br>Umum</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.barang-maintenance')}}" class="text-decoration-none">
                <img src="{{asset('images/barang-maintenance.svg')}}" alt="" width="80">
                <h3 class="mt-2">Kategori Barang Maintenance</h3>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="80">
                <h3 class="mt-2">Dashboard</h3>
            </a>
        </div>
    </div>
</div>
@endsection
