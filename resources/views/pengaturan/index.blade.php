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
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('pengguna.index')}}" class="text-decoration-none">
                <img src="{{asset('images/worker.svg')}}" alt="" width="100">
                <h2>Pengguna</h2>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5">
            <a href="{{route('pengaturan.wa')}}" class="text-decoration-none">
                <img src="{{asset('images/wa.svg')}}" alt="" width="100">
                <h2>Whatsapp</h2>
            </a>
        </div>
        @endif
        <div class="col-md-4 text-center">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="100">
                <h2>Dashboard</h2>
            </a>
        </div>
    </div>
</div>
@endsection
