@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>DASHBOARD</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-5">
        @if (auth()->user()->role === 'admin')
        
        @endif
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('database')}}" class="text-decoration-none">
                <img src="{{asset('images/database.svg')}}" alt="" width="100">
                <h2>Database</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('billing.index')}}" class="text-decoration-none">
                <img src="{{asset('images/billing.svg')}}" alt="" width="100">
                <h2>Billing</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('rekap.index')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap.svg')}}" alt="" width="100">
                <h2>Rekap</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('dokumen')}}" class="text-decoration-none">
                <img src="{{asset('images/document.svg')}}" alt="" width="100">
                <h2>Dokumen</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('pengaturan')}}" class="text-decoration-none">
                <img src="{{asset('images/pengaturan.svg')}}" alt="" width="100">
                <h2>Pengaturan</h2>
            </a>
        </div>
    </div>
</div>
@endsection
