@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>DATABASE</u></h1>
        </div>
    </div>
    <div class="row justify-content-left">
        <h2>Data Lama</h2>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('pemegang-saham.index')}}" class="text-decoration-none">
                <img src="{{asset('images/saham.svg')}}" alt="" width="70">
                <h4 class="mt-3">PEMEGANG SAHAM</h4>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h2>Data Internal</h2>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">PERSENTASE DIVIDEN<br>PENGELOLA & INVESTOR</h4>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">PERSENTASE DIVIDEN<br>PENGELOLA</h4>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">PERSENTASE DIVIDEN<br>INVESTOR</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('direksi.index')}}" class="text-decoration-none">
                <img src="{{asset('images/direksi.svg')}}" alt="" width="70">
                <h4 class="mt-3">BIODATA & GAJI<br>DIREKSI</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('karyawan.index')}}" class="text-decoration-none">
                <img src="{{asset('images/karyawan.svg')}}" alt="" width="70">
                <h4 class="mt-3">BIODATA & GAJI<br>STAFF</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('sponsor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/sponsor.svg')}}" alt="" width="70">
                <h4 class="mt-3">BONUS STAFF</h4>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h2>Data Eksternal</h2>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('vendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/vendor.svg')}}" alt="" width="70">
                <h4 class="mt-3">BIODATA<br>VENDOR</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('customer.index')}}" class="text-decoration-none">
                <img src="{{asset('images/company.svg')}}" alt="" width="70">
                <h4 class="mt-3">BIODATA TAMBANG</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('rekening.index')}}" class="text-decoration-none">
                <img src="{{asset('images/akun-bank.svg')}}" alt="" width="70">
                <h4 class="mt-3">REKENING TRANSAKSI</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">PERSENTASE PAJAK</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.kreditor')}}" class="text-decoration-none">
                <img src="{{asset('images/kreditor.svg')}}" alt="" width="70">
                <h4 class="mt-3">BIODATA KREDITUR</h4>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h2>Data Kategori</h2>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.cost-operational')}}" class="text-decoration-none">
                <img src="{{asset('images/cost-operational.svg')}}" alt="" width="70">
                <h4 class="mt-3">KATEGORI COST OPERATIONAL</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">KATEGORI INVENTARIS</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('kategori-barang.index')}}" class="text-decoration-none">
                <img src="{{asset('images/stock.svg')}}" alt="" width="70">
                <h4 class="mt-3">KATEGORI BARANG<br>UMUM</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.barang-maintenance')}}" class="text-decoration-none">
                <img src="{{asset('images/barang-maintenance.svg')}}" alt="" width="70">
                <h4 class="mt-3">KATEGORI BARANG MAINTENANCE</h4>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left">
        <h2>Data Transaksi</h2>
        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'su')
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('rute.index')}}" class="text-decoration-none">
                <img src="{{asset('images/rute.svg')}}" alt="" width="70">
                <h4 class="mt-3">RUTE</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('bbm-storing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/bbm.svg')}}" alt="" width="70">
                <h4 class="mt-3">BBM STORING</h4>
            </a>
        </div>

        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('vehicle.index')}}" class="text-decoration-none">
                <img src="{{asset('images/dumptruckempty.svg')}}" alt="" width="70">
                <h4 class="mt-3">VEHICLE</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.upah-gendong')}}" class="text-decoration-none">
                <img src="{{asset('images/upah-gendong.svg')}}" alt="" width="70">
                <h4 class="mt-3">UPAH GENDONG</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('database.aktivasi-maintenance')}}" class="text-decoration-none">
                <img src="{{asset('images/aktivasi-maintenance.svg')}}" alt="" width="70">
                <h4 class="mt-3">AKTIVASI MAINTENANCE VEHICLE</h4>
            </a>
        </div>
        @endif
        <div class="col-md-2 text-center mt-3 mb-3">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-3">DASHBOARD</h4>
            </a>
        </div>
    </div>

</div>
@endsection
