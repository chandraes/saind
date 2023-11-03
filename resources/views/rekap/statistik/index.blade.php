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
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('statisik.profit-bulanan')}}" class="text-decoration-none">
                <img src="{{asset('images/profit.svg')}}" alt="" width="100">
                <h2>PROFIT BULANAN </span></h2>
            </a>
        </div>
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('statistik.profit-tahunan')}}" class="text-decoration-none">
                <img src="{{asset('images/profit-tahunan.svg')}}" alt="" width="100">
                <h2>PROFIT TAHUNAN </span></h2>
            </a>
        </div>
        @endif
        <div class="col-md-4 text-center mt-5">
            <a href="{{route('statistik.perform-unit')}}" class="text-decoration-none">
                <img src="{{asset('images/perform-unit.svg')}}" alt="" width="100">
                <h2>PERFORM UNIT</h2>
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
