@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>Database</u></h1>
        </div>
    </div>
    <div class="row justify-content-left">
        <h4 class="mt-3">Customer</h4>
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'su')
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('rute.index')}}" class="text-decoration-none">
                <img src="{{asset('images/rute.svg')}}" alt="" width="80">
                <h4 class="mt-3">Rute</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('customer.index')}}" class="text-decoration-none">
                <img src="{{asset('images/company.svg')}}" alt="" width="80">
                <h4 class="mt-3">Customer</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('bbm-storing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/bbm.svg')}}" alt="" width="80">
                <h4 class="mt-3">BBM Storing</h4>
            </a>
        </div>
        @endif
    </div>
    <hr>
    <div class="row justify-content-left">
        <h4 class="mt-3">VENDOR</h4>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('sponsor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/sponsor.svg')}}" alt="" width="80">
                <h4 class="mt-3">Sponsor</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('vendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/vendor.svg')}}" alt="" width="80">
                <h4 class="mt-3">Vendor</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('vehicle.index')}}" class="text-decoration-none">
                <img src="{{asset('images/dumptruckempty.svg')}}" alt="" width="80">
                <h4 class="mt-3">Vehicle</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/upah-gendong.svg')}}" alt="" width="80">
                <h4 class="mt-3">Upah Gendong</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.aktivasi-maintenance')}}" class="text-decoration-none">
                <img src="{{asset('images/aktivasi-maintenance.svg')}}" alt="" width="80">
                <h4 class="mt-3">Aktivasi Maintenance Vehicle</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/db-ban.svg')}}" alt="" width="80">
                <h4 class="mt-3">Ban Luar</h4>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h4 class="mt-3">BIODATA</h4>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('karyawan.index')}}" class="text-decoration-none">
                <img src="{{asset('images/karyawan.svg')}}" alt="" width="80">
                <h4 class="mt-3">Staff</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('direksi.index')}}" class="text-decoration-none">
                <img src="{{asset('images/direksi.svg')}}" alt="" width="80">
                <h4 class="mt-3">Direksi</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('pemegang-saham.index')}}" class="text-decoration-none">
                <img src="{{asset('images/saham.svg')}}" alt="" width="80">
                <h4 class="mt-3">Pemegang Saham</h4>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h4 class="mt-3">OTHERS</h4>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('rekening.index')}}" class="text-decoration-none">
                <img src="{{asset('images/akun-bank.svg')}}" alt="" width="80">
                <h4 class="mt-3">Nomor Rekening Transaksi</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('kategori-barang.index')}}" class="text-decoration-none">
                <img src="{{asset('images/stock.svg')}}" alt="" width="80">
                <h4 class="mt-3">Kategori Barang<br>Umum</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.barang-maintenance')}}" class="text-decoration-none">
                <img src="{{asset('images/barang-maintenance.svg')}}" alt="" width="80">
                <h4 class="mt-3">Kategori Barang Maintenance</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="80">
                <h4 class="mt-3">Dashboard</h4>
            </a>
        </div>
    </div>
</div>
@endsection
