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
                <h4 class="mt-3">RUTE</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('customer.index')}}" class="text-decoration-none">
                <img src="{{asset('images/company.svg')}}" alt="" width="80">
                <h4 class="mt-3">CUSTOMER</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('bbm-storing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/bbm.svg')}}" alt="" width="80">
                <h4 class="mt-3">BBM STORING</h4>
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
                <h4 class="mt-3">SPONSOR</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('vendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/vendor.svg')}}" alt="" width="80">
                <h4 class="mt-3">VENDOR</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('vehicle.index')}}" class="text-decoration-none">
                <img src="{{asset('images/dumptruckempty.svg')}}" alt="" width="80">
                <h4 class="mt-3">VEHICLE</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/upah-gendong.svg')}}" alt="" width="80">
                <h4 class="mt-3">UPAH GENDONG</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.aktivasi-maintenance')}}" class="text-decoration-none">
                <img src="{{asset('images/aktivasi-maintenance.svg')}}" alt="" width="80">
                <h4 class="mt-3">AKTIVASI MAINTENANCE VEHICLE</h4>
            </a>
        </div>
        {{-- <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/db-ban.svg')}}" alt="" width="80">
                <h4 class="mt-3">BAN LUAR</h4>
            </a>
        </div> --}}
    </div>
    <hr>
    <div class="row justify-content-left">
        <h4 class="mt-3">BIODATA</h4>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('karyawan.index')}}" class="text-decoration-none">
                <img src="{{asset('images/karyawan.svg')}}" alt="" width="80">
                <h4 class="mt-3">STAFF</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('direksi.index')}}" class="text-decoration-none">
                <img src="{{asset('images/direksi.svg')}}" alt="" width="80">
                <h4 class="mt-3">DIREKSI</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('pemegang-saham.index')}}" class="text-decoration-none">
                <img src="{{asset('images/saham.svg')}}" alt="" width="80">
                <h4 class="mt-3">PEMEGANG SAHAM</h4>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h4 class="mt-3">OTHERS</h4>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('rekening.index')}}" class="text-decoration-none">
                <img src="{{asset('images/akun-bank.svg')}}" alt="" width="80">
                <h4 class="mt-3">NOMOR REKENING TRANSAKSI</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('kategori-barang.index')}}" class="text-decoration-none">
                <img src="{{asset('images/stock.svg')}}" alt="" width="80">
                <h4 class="mt-3">KATEGORI BARANG<br>UMUM</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.barang-maintenance')}}" class="text-decoration-none">
                <img src="{{asset('images/barang-maintenance.svg')}}" alt="" width="80">
                <h4 class="mt-3">KATEGORI BARANG MAINTENANCE</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/barang-maintenance.svg')}}" alt="" width="80">
                <h4 class="mt-3">KATEGORI COST OPERATIONAL</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="80">
                <h4 class="mt-3">DASHBOARD</h4>
            </a>
        </div>
    </div>
</div>
@endsection
