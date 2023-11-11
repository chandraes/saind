@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>STATISTIK</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-left mt-5">
        @if (auth()->user()->role == 'admin')
        <h1>PROFIT</h1>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('statisik.profit-bulanan')}}" class="text-decoration-none">
                <img src="{{asset('images/profit.svg')}}" alt="" width="100">
                <h2>BULANAN</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('statistik.profit-tahunan')}}" class="text-decoration-none">
                <img src="{{asset('images/profit-tahunan.svg')}}" alt="" width="100">
                <h2>TAHUNAN</h2>
            </a>
        </div>
        @endif
    </div>
    <hr>
    <div class="row justify-content-left mt-5">
        <h1>PERFORM UNIT</h1>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('statistik.perform-unit')}}" class="text-decoration-none">
                <img src="{{asset('images/perform-unit.svg')}}" alt="" width="100">
                <h2>BULANAN</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('statistik.perform-unit-tahunan')}}" class="text-decoration-none">
                <img src="{{asset('images/perform-unit-tahunan.svg')}}" alt="" width="100">
                <h2>TAHUNAN</h2>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left mt-5">
        <h1>OTHERS</h1>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('statistik.perform-vendor')}}" class="text-decoration-none">
                <img src="{{asset('images/statistik-vendor.svg')}}" alt="" width="100">
                <h2>STATISTIK VENDOR</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('statistik.customer')}}" class="text-decoration-none">
                <img src="{{asset('images/statistik-customer.svg')}}" alt="" width="100">
                <h2>STATISTIK CUSTOMER</h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('rekap.index')}}" class="text-decoration-none">
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
