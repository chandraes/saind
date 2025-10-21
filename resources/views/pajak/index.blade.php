@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1><u>PAJAK</u></h1>
</div>
<div class="container mt-3">
    <div class="row justify-content-left">
        <h4 class="mt-3">BILLING</h4>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pajak.ppn-masukan')}}" class="text-decoration-none">
                <img src="{{asset('images/ppn-masukan.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPN MASUKAN</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pajak.ppn-keluaran')}}" class="text-decoration-none">
                <img src="{{asset('images/ppn-keluaran.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPN KELUARAN</h4>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('pajak.pph-vendor')}}" class="text-decoration-none">
                <img src="{{asset('images/pph-vendor.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPH VENDOR</h4>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/pph-masa.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPH MASA</h4>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/pph-badan.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPH BADAN</h4>
            </a>
        </div>

    </div>
    <hr>
    <div class="row justify-content-left">
        <h4 class="mt-3">REKAP</h4>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pajak.rekap-ppn')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-ppn.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPN</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pajak.ppn-expired')}}" class="text-decoration-none">
                <img src="{{asset('images/ppn-expired.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPN EXPIRED</h4>
            </a>
        </div>
         <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pajak.rekap-pph-vendor')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-pph-vendor.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPH VENDOR</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mt-3">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-3">DASHBOARD</h4>
            </a>
        </div>
    </div>
</div>
@endsection
