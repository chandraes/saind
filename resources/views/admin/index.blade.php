@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
         <h1><u>DASHBOARD</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-5">
        @if (auth()->user()->role === 'admin' && auth()->user()->id === 1)
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('bypass-kas-besar.index')}}" class="text-decoration-none">
                <img src="{{asset('images/admin.svg')}}" alt="" width="100">
                <h2>By Pass Kas Besar</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('bypass-kas-vendor.index')}}" class="text-decoration-none">
                <img src="{{asset('images/admin.svg')}}" alt="" width="100">
                <h2>By Pass Kas Vendor</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('bypass-kas-direksi.index')}}" class="text-decoration-none">
                <img src="{{asset('images/admin.svg')}}" alt="" width="100">
                <h2>By Pass Kasbon Direksi</h2>
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
